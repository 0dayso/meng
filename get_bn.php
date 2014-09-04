<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <link rel="stylesheet" href="table.css">
    <title>bn.com内容抓取</title>
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
			$EAN = $match[1];
			if ($v == "import")
			{
				$LegoID = $_POST["$EAN"];
				$query = "INSERT INTO BarnesNoble_Item (LegoID, EAN) VALUES ('".$mysqli->real_escape_string($LegoID)."', '".$mysqli->real_escape_string($EAN)."');";
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
	<form id="form" action="get_bn.php?action=update" method="post">
	<table>
	<tr><th>Order</th><th>BN ID</th><th>Title</th><th>LEGOID</th><th>Operation</th></tr>
<?php
	$query = "SELECT LegoID,EAN FROM BarnesNoble_Item;";
	$result = $mysqli->query($query);

	$EANs = array();
	while ($row = $result->fetch_array(MYSQLI_ASSOC))
	{
		$EAN = $row["EAN"];
		$EANs["$EAN"] = $row["LegoID"];
	}

	$mysqli->close();

	require_once("simple_html_dom.php");
	mb_internal_encoding('utf-8');
	if ($_GET["page"])
	{
		$page = $_GET["page"];
	}
	else
	{
		$page = 8;
	}
	$show = 0;
	for ($i = 1; $i <= $page; $i++)
	{
		$startfrom = ($i-1)*90+1;
		$url = "http://www.barnesandnoble.com/s?CAT=1024014&view=grid&store=toy&sort=SA&size=90&startat=".$startfrom;
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
		foreach($html->find('./ol[@class="result-set box"]/li') as $item)
		{
			$href = $item->find('./div[@class="price-format"]/a', 0)->href;
			preg_match_all("/\/p\/(.*)\/(\d+)\?ean=(\d+)&/u", html_entity_decode($href, ENT_NOQUOTES, 'UTF-8'), $matches);
			$title = str_replace("toys games lego ", "", str_replace("-", " ", $matches[1][0]));
			//$BNID = $matches[2][0];
			$EAN = $matches[3][0];
			$from = $item->find('./div[@class="price-format"]/a/span[@class="format"]', 0)->plaintext;
			//$price = str_replace("$", "", trim($item->find('./div[@class="price-format"]/a/span[@class="price"]', 0)->plaintext));

			//var_dump($title, $EAN, $EAN, $from);

			if (!isset($EANs[$EAN]) && $from == "BN.com")
			#不在数据库内的新套装：
			{
				$pic = "http://img1.imagesbn.com/p/".$EAN."_p0_v2_s600.jpg";
				preg_match_all("/\d{4,8}/u", html_entity_decode($title, ENT_NOQUOTES, 'UTF-8'), $matches);
				$legoid = array_pop(array_pop($matches));
				if (isset($legoid))
				{
					echo "<tr><td></td><td><a href=\"http://www.barnesandnoble.com/p/?ean=".$EAN."\" target=\"_blank\">".$EAN."</a></td><td>".$title."</td><td><input type=\"text\" name=\"".$EAN."\" size=\"6\" value=\"".$legoid."\" /></td><td><input type=\"radio\" checked=\"checked\" name=\"rad_$EAN\" value=\"import\" />导入<br /><input type=\"radio\" name=\"rad_$EAN\" value=\"ignore\" />暂不<br /><input type=\"radio\" name=\"rad_$EAN\" value=\"never\" />永不</td></tr>";
				}
				else
				{
					echo "<tr><td><div style=\"background-image:url($pic); background-position:left;height:150; width:150;\"></div></td><td><a href=\"http://www.barnesandnoble.com/p/?ean=".$EAN."\" target=\"_blank\">".$EAN."</a></td><td>".$title."</td><td><input type=\"text\" name=\"".$EAN."\" size=\"6\" value=\"".$legoid."\" /></td><td><input type=\"radio\" name=\"rad_$EAN\" value=\"import\" />导入<br /><input type=\"radio\" name=\"rad_$EAN\" value=\"ignore\" checked=\"checked\" />暂不<br /><input type=\"radio\" name=\"rad_$EAN\" value=\"never\" />永不</td></tr>";
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
