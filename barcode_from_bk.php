<html>
<head>
  <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
  <script type="text/javascript" src="http://ossweb-img.qq.com/images/js/jquery/jquery-1.7.1.min.js"></script>
  <title>Brickset 取barcode</title>
</head>
<body>
<?php
require("conn.php");
require_once("simple_html_dom.php");
$legoid=$_GET["legoid"];

if (isset($legoid))
{
  $url = 'www.brickset.com/detail/?Set='.$legoid.'-1';
  $ch = curl_init(); 
  $timeout = 5; 
  curl_setopt($ch, CURLOPT_URL, $url); 
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 
  curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout); 
  $contents = curl_exec($ch); 
  curl_close($ch); 
  
  $html = str_get_html($contents);
  foreach ($html->find('div[id=menuPanel]/div/ul/li') as $list)
  {
	$listhtml = str_get_html($list->innertext);
	//echo var_dump($list->innertext);
	//$domspan = $listhtml->find('span', 0)->innertext;
	//echo var_dump($domspan);
	if ($listhtml->find('span', 0)->plaintext == "Barcodes")
	{
	  $barcodes = trim(str_replace($listhtml->find('span', 0)->outertext, "", $listhtml->innertext));
	  	if (preg_match('/UPC: (\d{12})/', $barcodes,  $matches))
		{
			$UPC =  $matches[1];
		}
		else
		{
			$UPC = null;
		}
		if (preg_match('/EAN: (\d{13})/', $barcodes,  $matches))
		{
			$EAN =  $matches[1];
		}
		else
		{
			$EAN = null;
		}
	}
	elseif ($listhtml->find('span', 0)->plaintext == "LEGO item numbers")
	{
	  $legosnstr = trim(str_replace($listhtml->find('span', 0)->outertext, "", $listhtml->innertext));
		if (preg_match('/NA: (\d{7})/', $legosnstr,  $matches))
		{
			$legosn =  $matches[1];
		}
		else
		{
			$legosn = null;
		}
	}
  }
  if (!($UPC == null && $EAN == null))
  {
  	date_default_timezone_set('Asia/Shanghai');
	$conn=mysql_connect($mysql_server_name, $mysql_username, $mysql_password) or die ("数据库错误：".mysql_error());
	mysql_query("SET NAMES UTF8;", $conn);
	mysql_query("SET time_zone = '+08:00';", $conn);
	
	$strsql="SELECT * FROM DB_Barcode WHERE LegoID = '".$legoid."' LIMIT 1;";
	$result=mysql_db_query($mysql_database, $strsql, $conn);
	if (mysql_fetch_array($result))
	{
		$strsql = "UPDATE DB_Barcode SET ";
		if ($UPC != null)
		{
			$strsql = $strsql."UPC='".$UPC."', ";
		}
		if ($EAN != null)
		{
			$strsql = $strsql."EAN='".$EAN."', ";
		}
		if ($legosn != null)
		{
			$strsql = $strsql."LegoItemNumber='".$legosn."' ";
		}
		else
		{
			$strsql = trim(trim($strsql), ",");
		}
		$strsql = $strsql." WHERE LegoID = '".$legoid."';";
		
		$result=mysql_db_query($mysql_database, $strsql, $conn);
		echo $strsql;
	}
	else
	{
		$strsql="INSERT INTO DB_Barcode (LegoID, UPC, EAN, LegoItemNumber) VALUES ('".$legoid."', '".$UPC."', '".$EAN."','".$legosn."');";
		$result=mysql_db_query($mysql_database, $strsql, $conn);
		echo $strsql;
	}
	
	mysql_free_result($result);

  }
}
else
{
?>
<form action='' method='get'>
<p>请输入LegoID：</p>
<input type="text" id="legoid" name="legoid" /> 
</form>
<?php
}
?>
</body>
</html>
