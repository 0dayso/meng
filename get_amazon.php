<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <link rel="stylesheet" href="table.css">
    <title>Amazon.com内容抓取</title>
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
			$ASIN = $match[1];
			if ($v == "import")
			{
				$LegoID = $_POST["$ASIN"];
				$query = "INSERT INTO PW_AmazonInfo (Country, LegoID, ASIN, Scan ) VALUES ('US', '".$mysqli->real_escape_string($LegoID)."', '".$mysqli->real_escape_string($ASIN)."', 1);";
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
	<form id="form" action="get_amazon.php?action=update" method="post">
	<table>
	<tr><th>Order</th><th>ASIN</th><th>Title</th><th>Price</th><th>LEGOID</th><th>Operation</th></tr>
<?php
	$query = "SELECT LegoID,ASIN FROM PW_AmazonInfo WHERE Country='US';";
	$result = $mysqli->query($query);

	$ASINs = array();
	while ($row = $result->fetch_array(MYSQLI_ASSOC))
	{
		$asin = $row["ASIN"];
		$ASINs["$asin"] = $row["LegoID"];
	}


	require_once("simple_html_dom.php");
	mb_internal_encoding('utf-8');
	if ($_GET["page"])
	{
		$page = $_GET["page"];
	}
	else
	{
		$page = 1;
	}
	$show = 1;
	for ($i = 1; $i <= $page; $i++)
	{
		$url = 'http://www.amazon.com/s/rh=n%3A165793011%2Cp_4%3ALEGO%2Cp_6%3AATVPDKIKX0DER&ie=UTF8&page='.$i;
		$ch = curl_init(); 
		$timeout = 5; 
		curl_setopt($ch, CURLOPT_URL, $url); 
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout); 
		$contents = curl_exec($ch); 
		curl_close($ch);
		$html = str_get_html($contents);
		if ($html->find('span[class="pagnDisabled"]',0))
		{
			$page = trim($html->find('span[class="pagnDisabled"]',0)->innertext);
		}
		if ($page > 1 && $show == 1)
		{
			echo "共".$page."页，正在读取：";
			$show = 0;
		}
		echo $i."..";
		$items = $html->find('div[id="atfResults"] div[class="fstRowGrid prod celwidget"]');
		$otherline = $html->find('div[id="btfResults"] div[class="rsltGrid prod celwidget"]');
		foreach ($otherline as $line)
		{
			array_push($items, $line);
		}
		foreach ($items as $item)
		{
			$itemhtml = str_get_html($item);
			$asin = trim($itemhtml->find('div',0)->name);
	
			if (isset($ASINs["$asin"]))
			{
			}
			#不在数据库内的新套装：
			else
			{
				$order = str_replace("result_","",trim($itemhtml->find('div',0)->id));
				$title = str_replace("-", " ", trim($itemhtml->find('div h3[class="newaps"] a span',0)->innertext));
				if ($itemhtml->find('div ul li[class="newp"] a del',0))
				{
					$price = str_replace("-", " ", trim($itemhtml->find('div ul li[class="newp"] a del',0)->innertext));
				}
				elseif ($itemhtml->find('div ul li[class="newp"] a span',0))
				{
					$price = str_replace("-", " ", trim($itemhtml->find('div ul li[class="newp"] a span',0)->innertext));
				}
				preg_match_all("/\d{4,7}/u", html_entity_decode($title, ENT_NOQUOTES, 'UTF-8'), $matches);
				$legoid = array_pop(array_pop($matches));
				if (isset($legoid))
				{
					echo "<tr><td>".$order."</td><td><a href=\"http://www.amazon.com/dp/".$asin."\" target=\"_blank\">".$asin."</a></td><td>".$title."</td><td>".$price."</td><td><input type=\"text\" name=\"".$asin."\" size=\"6\" value=\"".$legoid."\" /></td><td><input type=\"radio\" checked=\"checked\" name=\"rad_$asin\" value=\"import\" />导入<br /><input type=\"radio\" name=\"rad_$asin\" value=\"ignore\" />暂不<br /><input type=\"radio\" name=\"rad_$asin\" value=\"never\" />永不</td></tr>";
				}
				else
				{
					echo "<tr><td>".$order."</td><td><a href=\"http://www.amazon.com/dp/".$asin."\" target=\"_blank\">".$asin."</a></td><td>".$title."</td><td>".$price."</td><td><input type=\"text\" name=\"".$asin."\" size=\"6\" value=\"".$legoid."\" /></td><td><input type=\"radio\" name=\"rad_$asin\" value=\"import\" />导入<br /><input type=\"radio\" name=\"rad_$asin\" value=\"ignore\" checked=\"checked\" />暂不<br /><input type=\"radio\" name=\"rad_$asin\" value=\"never\" />永不</td></tr>";
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
