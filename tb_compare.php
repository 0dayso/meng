<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <link rel="stylesheet" href="table.css">
	<script type="text/javascript" src="script/jquery-1.11.1.min.js"></script>
	<script type="text/javascript" src="script/jquery.tablesorter.min.js"></script>
	<script language="JavaScript">
	function del()
	{
		$("#btn_del").attr("disabled","disabled");
		$("#list").hide();
		var deleteid = parseInt($("#del").val());
		if(deleteid)
		{
			$("#main").html('<img src="/images/loading.gif">');
			$.get("ajax_tb_snapshot.php", { op: "del", id: deleteid}, function(data) {del_callback(deleteid, data);} );
		}
		else
		{
			$("#btn_del").removeAttr("disabled");
		}
	}
	function del_callback(deleteid, data)
	{
		var obj = jQuery.parseJSON(data);
		if (obj)
		{
			if (obj.ReturnCode == "0")
			{
				$("#main").html(obj.ReturnHTML);
				$("#del").find('[value="'+deleteid+'"]').remove();
				$("#comp1").find('[value="'+deleteid+'"]').remove();
				$("#comp2").find('[value="'+deleteid+'"]').remove();
				$("#btn_del").removeAttr("disabled");
			}
			else
			{
				$("#main").html(obj.ReturnHTML);
				$("#btn_del").removeAttr("disabled");
			}
		}
	}
	function comp()
	{
		$("#btn_comp").attr("disabled","disabled");
		$("#list").hide();
		var comp1 = parseInt($("#comp1").val());
		var comp2 = parseInt($("#comp2").val());
		if (comp1 < comp2)
		{
			$("#main").html('<img src="/images/loading.gif">');
			$.get("ajax_tb_snapshot.php", { op: "comp", id1: comp1, id2: comp2}, function(data) {comp_callback(data);} );
		}
		else if (comp1 > comp2)
		{
			$("#main").html('<img src="/images/loading.gif">');
			$.get("ajax_tb_snapshot.php", { op: "comp", id1: comp2, id2: comp1}, function(data) {comp_callback(data);} );
		}
		else
		{
			$("#main").html("<p>原快照与目标快照一致!</p>");
			$("#btn_comp").removeAttr("disabled");
		}
	}
	function comp_callback(data)
	{
		var obj = jQuery.parseJSON(data);
		if (obj)
		{
			if (obj.NochangeCount > "0")
			{
				var output = "<p>"+obj.NochangeCount+"条数据未变化。</p>";
				for (var i = 0; i < Object.keys(obj.PriceRise).length; i++)
				{
					var legoid = Object.keys(obj.PriceRise)[i];
					var oldprice = obj.PriceRise[legoid].Old;
					var newprice = obj.PriceRise[legoid].New;
					var delta = obj.PriceRise[legoid].Delta;
					var rate = obj.PriceRise[legoid].Rate+"%";
					
					var row = "\t\t<tr><td align=\"center\"><a target=\"_blank\" href=\"tb_price.php?legoid="+legoid+"\"><img height=\"75\" src=\"pic.php?thumb150="+legoid+"\"><br/>"+legoid+"</td><td>"+oldprice+"</td><td>"+newprice+"</td><td>"+delta+"</td><td>"+rate+"</td></tr>\r\n";
					$("#tb_pricerise tbody").append(row);
				}
				for (var i = 0; i < Object.keys(obj.PriceDrop).length; i++)
				{
					var legoid = Object.keys(obj.PriceDrop)[i];
					var oldprice = obj.PriceDrop[legoid].Old;
					var newprice = obj.PriceDrop[legoid].New;
					var delta = obj.PriceDrop[legoid].Delta;
					var rate = obj.PriceDrop[legoid].Rate+"%";
					var row = "\t\t<tr><td align=\"center\"><a target=\"_blank\" href=\"tb_price.php?legoid="+legoid+"\"><img height=\"75\" src=\"pic.php?thumb150="+legoid+"\"><br/>"+legoid+"</td><td>"+oldprice+"</td><td>"+newprice+"</td><td>"+delta+"</td><td>"+rate+"</td></tr>\r\n";
					$("#tb_pricedrop tbody").append(row);
				}
				for (var i = 0; i < Object.keys(obj.VolumeIncrease).length; i++)
				{
					var legoid = Object.keys(obj.VolumeIncrease)[i];
					var oldvol = obj.VolumeIncrease[legoid].Old;
					var newvol = obj.VolumeIncrease[legoid].New;
					var delta = obj.VolumeIncrease[legoid].Delta;
					var rate = obj.VolumeIncrease[legoid].Rate;
					if (rate != "N/A")
					{
						rate = rate+"%";
					}
					var row = "\t\t<tr><td align=\"center\"><a target=\"_blank\" href=\"tb_price.php?legoid="+legoid+"\"><img height=\"75\" src=\"pic.php?thumb150="+legoid+"\"><br/>"+legoid+"</td><td>"+oldvol+"</td><td>"+newvol+"</td><td>"+delta+"</td><td>"+rate+"</td></tr>\r\n";
					$("#tb_volumeincrease tbody").append(row);
				}
				$("#main").html(output);
				
				$("#list").show();
   				$("#tb_pricerise").tablesorter();
   				$("#tb_pricedrop").tablesorter();
   				$("#tb_volumeincrease").tablesorter();
			}
		}
		else
		{
			$("#main").html("<p>返回错误："+data+"</p>");
		}
		$("#btn_comp").removeAttr("disabled");

	}
	</script>
    <title>Taobao数据对比</title>
</head>
<body>
<?php
require("conn.php");

date_default_timezone_set('Asia/Shanghai');
$mysqli = new mysqli($mysql_server_name, $mysql_username, $mysql_password, $mysql_database);

if (mysqli_connect_errno()) {
	printf("Database Connect failed: %s\n", mysqli_connect_error());
	exit();
}

$mysqli->query("SET NAMES UTF8;");
$mysqli->query("SET time_zone = '+08:00';");

$query = "SELECT * FROM TB_Snapshot_List ORDER BY SSID DESC;";
$result = $mysqli->query($query);

$Snapshots = array();
while ($row = $result->fetch_array(MYSQLI_ASSOC))
{
	$SSID = $row['SSID'];
	$Snapshots[$SSID] = $row['CreateTime'];
}
$mysqli->close();

$HTMLOptions = "";
foreach($Snapshots as $key=>$value)
{
	$HTMLOptions .= "\t<option value=\"".$key."\">".$value."</option>\r\n";
}
$HTMLOptions .= "</select>\r\n";

echo "<select id=\"del\">\r\n\t<option value=\"0\"></option>\r\n".$HTMLOptions;
echo "<input id=\"btn_del\" type=\"button\" onclick=\"del();\" value=\"删除\" /><br/>\r\n";

echo "<select id=\"comp1\">\r\n".$HTMLOptions."<select id=\"comp2\">\r\n".$HTMLOptions;
echo "<input id=\"btn_comp\" type=\"button\" onclick=\"comp();\" value=\"对比\" />\r\n";

?>
<div id="main">
</div>
<div id="list" style="display:none;">
	<div style="float:left;">
		<table id="tb_pricerise">
			<thead>
				<tr><th>LEGOID</th><th>OLD</th><th>NEW</th><th>Delta</th><th>Rate</th></tr>
			</thead>
			<tbody>
			</tbody>
		</table>
	</div>
	<div style="float:left;">
		<table id="tb_pricedrop">
			<thead>
				<tr><th>LEGOID</th><th>OLD</th><th>NEW</th><th>Delta</th><th>Rate</th></tr>
			</thead>
			<tbody>
			</tbody>			
		</table>
	</div>
	<div style="float:left;">
		<table id="tb_volumeincrease">
			<thead>
				<tr><th>LEGOID</th><th>OLD</th><th>NEW</th><th>Delta</th><th>Rate</th></tr>
			</thead>
			<tbody>
			</tbody>
		</table>
	</div>
</div>
</body>
</html>
