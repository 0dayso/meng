<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <link rel="stylesheet" href="table.css">
	<script type="text/javascript" src="script/jquery-1.11.1.min.js"></script>
	<script type="text/javascript" src="script/jquery.tablesorter.min.js"></script>
	<script language="JavaScript">
	function update_main_pic(legoid)
	{
		$("#btn_"+legoid).attr("disabled","desabled");
    	$("#tb_main_"+legoid).html('<img src="/images/loading.gif">');

		$.get("taobao_mainpic_update.php", { id: legoid} , function(data) {update_callback(data)} );
	}
	function update_callback(data)
	{
		var obj = jQuery.parseJSON(data);
	
		if (obj.status == "OK")
		{
			$("#tb_main_"+obj.id).html("<img height=120px width=120px src=\""+obj.imgurl+"\">");
		}
		else
		{
			alert(obj.error);
		}
		$("#btn_"+obj.id).removeAttr("disabled");

	}
	</script>
    <title>更新商品条码</title>
</head>
<body>
<form id="form" action="taobao_item.php?action=updatebarcode" method="post">
<?php

include "appconf.php";
include "TopSdk.php";	

require("conn.php");

date_default_timezone_set('Asia/Shanghai');
$mysqli = new mysqli($mysql_server_name, $mysql_username, $mysql_password, $mysql_database);

if (mysqli_connect_errno()) {
	printf("Database Connect failed: %s\n", mysqli_connect_error());
	exit();
}

$mysqli->query("SET NAMES UTF8;");
$mysqli->query("SET time_zone = '+08:00';");

$query = "SELECT ItemID,LegoID FROM TB_Item;";
$result = $mysqli->query($query);

$TBItems = array();
while ($row = $result->fetch_array(MYSQLI_ASSOC))
{
	$ItemID = $row["ItemID"];
	$LegoID = $row["LegoID"];
	$TBItems["$ItemID"] = $LegoID;
}

$query = "SELECT LegoID,EAN,UPC FROM DB_Set;";
$result = $mysqli->query($query);

$Barcodes = array();
while ($row = $result->fetch_array(MYSQLI_ASSOC))
{
	$LegoID = $row["LegoID"];
	if ($row["EAN"] <> "")
	{
		$Barcodes["$LegoID"] = $row["EAN"];
	}
	elseif ($row["UPC"] <> "")
	{
		$Barcodes["$LegoID"] = $row["UPC"];
	}
}
$result->free();
$mysqli->close();

echo "<table class=\"sortable\"><tr><th>ItemID</th><th>Title</th><th>当前头图</th><th>生成头图</th><th>更新头图</th><th>当前条码</th><th><th>更新条码</th><th>Operations</th></tr>";


//实例化TopClient类
$c = new TopClient;
$c->appkey = $client_id;
$c->secretKey = $client_secret;
$sessionKey = $access_token;

$reqItems = new ItemsOnsaleGetRequest;
//$reqItems = new ItemsInventoryGetRequest;
//$reqItems->setBanner("for_shelved");
//$reqItems->setBanner("sold_out");

$reqItems->setFields("num_iid,title,outer_id,pic_url,barcode");
$reqItems->setOrderBy("delist_time:asc");
$page = 1;
$pages = 1;
$pagesize = 40;

while ($page <= $pages)
{
	$reqItems->setPageNo($page);
	$reqItems->setPageSize($pagesize);
	$respItems = $c->execute($reqItems, $sessionKey);
	$total = $respItems->total_results;
	$pages = ceil($total/$pagesize);

	foreach ($respItems->items->item as $item)
	{
		$ItemID = $item->num_iid;
		$LegoID = $TBItems["$ItemID"];
		$Barcode = $Barcodes["$LegoID"];
		//$reqCode = new ItemGetRequest;
		//$reqCode->setFields("barcode");
		//$reqCode->setNumIid(intval($item->num_iid));
		//$respCode = $c->execute($reqCode, $sessionKey);
		//$code = $respCode->item_get_response->item->barcode;
		//var_dump($respCode);
		if (isset($LegoID))
		{
			$file = "setimg/tb_main/".$LegoID."_800.jpg";
			$button = "<input id=\"btn_".$LegoID."\" type=\"button\" onclick=\"update_main_pic('".$LegoID."')\" value=\"更新\" />";
			if (!file_exists($file))
			{
				$file = "setimg/notfound.jpg";
				$button = "";
			}
			
			echo "<tr><td><a href=\"http://item.taobao.com/item.htm?id=".$item->num_iid."\">".$item->num_iid."</a></td><td>".$item->title."</td><td><div id=\"tb_main_".$LegoID."\"><img height=120px width=120px src=\"".$item->pic_url."\"></div></td><td><img height=120px width=120px src=\"".$file."\"></td><td>".$button."</td><td><input type=\"text\" name=\"".$item->num_iid."\" size=\"16\"value=\"".$Barcode."\" /></td>";
			if (isset($Barcode) && $code <> $Barcode)
			{
				echo "<td><input type=\"radio\" checked=\"checked\" name=\"rad_".$item->num_iid."\" value=\"import\" />更新<input type=\"radio\" name=\"rad_".$item->num_iid."\" value=\"ignore\" />暂不</td>";
			}
			else
			{
				echo "<td><input type=\"radio\" name=\"rad_".$item->num_iid."\" value=\"import\" />更新<input type=\"radio\" checked=\"checked\" name=\"rad_".$item->num_iid."\" value=\"ignore\" />暂不</td>";
			}
			echo "</tr>";
		}
	}
	$page++;
}

echo "</table>";
?>
<input type="submit" value="批量更新" />
</form>
</body>
</html>
