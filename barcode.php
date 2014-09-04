<html>
<head>
  <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
  <script type="text/javascript">
  function getUrlParam(name)
  { 
	var reg = new RegExp("(^|&)" + name + "=([^&]*)(&|$)"); 
	var r = window.location.search.substr(1).match(reg); 
	if (r != null) 
		return unescape(r[2]); 
	return null; 
  }
  function init_barcode()
  {
  	var txtBarcode = document.getElementById("barcode");
  	txtBarcode.value=getUrlParam('barcode');
  	txtBarcode.select();
  	txtBarcode.focus();
  }
  function input_legoid()
  {
    if (event.keyCode==13)
    {
      get_legoid();
    }
  }
  function get_legoid()
  {
    var legoid = document.getElementById("LegoID").value;
    $.get("get_legoid.php", { legoid: legoid}, function(data) { $("#SetPreview").html(data);} );
  }
  function nextfocus()  
  {
    var e = window.event || ev;
    var keyCode = -1;
    if (e.which == null)
    keyCode= e.keyCode;    // IE
    else if (e.which > 0)
    keyCode=e.which;    // All others
    if(keyCode==13)
    {
      e.keyCode = 9; //only for IE
    }
  }
  </script>
</head>
<body onload="init_barcode();">
<form id="query_barcode" method="GET">
请扫描条码：<input type="text" id="barcode" name="barcode"/><br />
<div id="SetPreview"></div>
<?php
	require("conn.php");
	$BarCode = $_GET["barcode"];
	
	date_default_timezone_set('Asia/Shanghai');
	$mysqli = new mysqli($mysql_server_name, $mysql_username, $mysql_password, $mysql_database);

	if (mysqli_connect_errno()) {
		printf("Database Connect failed: %s\n", mysqli_connect_error());
		exit();
	}

	$mysqli->query("SET NAMES UTF8;");
	$mysqli->query("SET time_zone = '+08:00';");

	if (isset($BarCode))
	{
		if (preg_match('/\d{13}/', $BarCode))
		{
			$codetype = "EAN";
			$strCond = "EAN = '".$BarCode."'";
		}
		elseif (preg_match('/\d{12}/', $BarCode))
		{
			$codetype = "UPC";
			$strCond = "UPC = '".$BarCode."'";
		}
		else
		{
			$codetype = "3rdCode";
			$strCond = "3rdCode = '".$BarCode."'";
		}
		
		if (($_GET["action"] == "submit") && ($_GET["legoid"] != ''))
		{
			$legoid = $_GET["legoid"];
			$legosn = $_GET["legosn"];
			
			$strsql="SELECT LegoID FROM DB_Set WHERE LegoID = '".$legoid."' LIMIT 1;";
			$result = $mysqli->query($strsql);

			if ($result->num_rows)
			{
				if ($legosn == '')
				{
					$strsql="UPDATE DB_Barcode SET ".$strCond." WHERE LegoID = '".$legoid."';";
				}
				else
				{
					$strsql="UPDATE DB_Barcode SET ".$strCond.", ItemSN = '".$legosn."' WHERE LegoID = '".$legoid."';";
				}
				$result = $mysqli->query($strsql);

			}
			else
			{
				$strsql="INSERT INTO DB_Barcode (LegoID, ".$codetype.", ItemSN) VALUES ('".$legoid."', '".$BarCode."', '".$legosn."');";
				$result = $mysqli->query($strsql);

			}
		}
		else
		{
			$strsql="SELECT LegoID FROM DB_Set WHERE ".$strCond;
			$result = $mysqli->query($strsql);
			if ($row = $result->fetch_array(MYSQLI_ASSOC))
			{
				//echo "<embed src=\"/images/done.wav\" autostart=\"true\" hidden=\"true\" loop=\"false\">";
				echo $row['LegoID'];
			}
			else
			{
				?>
				<br />
				这是一个<?php echo $codetype; ?>条码，该条码不在我们系统中，请登记相关信息：<br />
				LEGOID：<input type="text" id="legoid" name="legoid" /><br />
				LEGO序列号：<input type="text" id="legosn" name="legosn" /><br />
				<input type="hidden" name="action" value="submit">
				<input type="submit">
				<?php
			}
		}
		$result->free();
	}
	else
	{
	}

	$mysqli->close();

?>
</body>
</html>
</script>