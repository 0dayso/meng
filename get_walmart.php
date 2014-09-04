<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <link rel="stylesheet" href="table.css">
    <title>Walmart.com内容抓取</title>
</head>
<body>
<div id="info"></div>
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

if ($_GET["action"]=="update")
{
	foreach($_POST as $k=>$v)
	{
		if (preg_match("/^rad_(\w+)/",$k,$match))
		{
			$WalmartID = $match[1];
			if ($v == "import")
			{
				$LegoID = $_POST["$WalmartID"];
				$query = "INSERT INTO Walmart_Item (LegoID, WalmartID) VALUES ('".$mysqli->real_escape_string($LegoID)."', '".$mysqli->real_escape_string($WalmartID)."');";
				$result = $mysqli->query($query);
				echo $query." ".$result."<br/>";
			}
			elseif ($v == "ignore")
			{
				echo "Ignore<br/>";
			}
			elseif ($v == "never")
			{
				echo "Never<br/>";
			}
		}
	}
	$mysqli->close();
	exit;
}
else
{
?>
	<form id="form" action="get_walmart.php?action=update" method="post">
	<table>
	<tr><th>Order</th><th>Walmart ID</th><th>Title</th><th>LEGOID</th><th>Operation</th></tr>
<?php
	$query = "SELECT LegoID,WalmartID FROM Walmart_Item;";
	$result = $mysqli->query($query);

	$WalmartIDs = array();
	while ($row = $result->fetch_array(MYSQLI_ASSOC))
	{
		$WalmartID = $row["WalmartID"];
		$WalmartIDs["$WalmartID"] = $row["LegoID"];
	}


	require_once("simple_html_dom.php");
	mb_internal_encoding('utf-8');
	if ($_GET["page"])
	{
		$page = $_GET["page"];
	}
	else
	{
		$page = 6;
	}
	$show = 0;
	for ($i = 1; $i <= $page; $i++)
	{
		$startfrom = ($i-1)*60;
		$url = "http://www.walmart.com/browse/building-blocks-sets/building-sets/lego/4171_4186_1044000/YnJhbmQ6TEVHTwieie?ic=60_".$startfrom;
		$ch = curl_init(); 
		$timeout = 5; 
		curl_setopt($ch, CURLOPT_URL, $url); 
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout); 
		$contents = curl_exec($ch); 
		curl_close($ch);
		$html = str_get_html($contents);
		/*
		if ($html->find('span[class="pagnDisabled"]',0))
		{
			$page = trim($html->find('span[class="pagnDisabled"]',0)->innertext);
		}
		*/
		if ($page > 1 && $show == 1)
		{
			echo "共".$page."页，正在读取：";
			$show = 0;
		}
		echo $i."..";
		foreach($html->find('//div[@class="prodInfoBox"]') as $item)
		{
			$title = str_replace(" Building Set", "", str_replace(" Play Set", "", $item->find('./a[@class=\'prodLink ListItemLink\']',0)->plaintext));
			$walmartID = substr($item->find('./a[@class=\'prodLink ListItemLink\']',0)->href, -8);
			$priceint = str_replace("$", "", str_replace(".", "", $item->find('./div/div/div/div[@class=\'camelPrice\']/span[@class=\'bigPriceText2\']', 0)->plaintext));
			$pricedec = $item->find('./div/div/div/div[@class=\'camelPrice\']/span[@class=\'smallPriceText2\']', 0)->plaintext;
			$price = $priceint + 0.01 * $pricedec;

			if (!isset($WalmartIDs["$walmartID"]))
			#不在数据库内的新套装：
			{
				$pic = str_replace("180X180", "500X500", $item->parent()->parent()->find("./div/a/img",0)->getAttribute('src'));
				preg_match_all("/\d{4,8}/u", html_entity_decode($title, ENT_NOQUOTES, 'UTF-8'), $matches);
				$legoid = array_pop(array_pop($matches));
				if (isset($legoid))
				{
					echo "<tr><td><img src=\"".$pic."\"></td><td><a href=\"http://www.walmart.com/ip/".$walmartID."\" target=\"_blank\">".$walmartID."</a></td><td>".$title."</td><td><input type=\"text\" name=\"".$walmartID."\" size=\"6\" value=\"".$legoid."\" /></td><td><input type=\"radio\" checked=\"checked\" name=\"rad_$walmartID\" value=\"import\" />导入<br /><input type=\"radio\" name=\"rad_$walmartID\" value=\"ignore\" />暂不<br /><input type=\"radio\" name=\"rad_$walmartID\" value=\"never\" />永不</td></tr>";
				}
				else
				{
					echo "<tr><td><div style=\"background-image:url($pic); background-position:left;height:150; width:150;\"></div></td><td><a href=\"http://www.walmart.com/ip/".$walmartID."\" target=\"_blank\">".$walmartID."</a></td><td>".$title."</td><td><input type=\"text\" name=\"".$walmartID."\" size=\"6\" value=\"".$legoid."\" /></td><td><input type=\"radio\" name=\"rad_$walmartID\" value=\"import\" />导入<br /><input type=\"radio\" name=\"rad_$walmartID\" value=\"ignore\" checked=\"checked\" />暂不<br /><input type=\"radio\" name=\"rad_$walmartID\" value=\"never\" />永不</td></tr>";
				}
			}
		}
	}
}
?>
</table>
<input type="submit" value="批量导入" />
</body>
</html>
