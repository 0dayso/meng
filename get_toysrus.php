<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <link rel="stylesheet" href="table.css">
    <title>Toysrus.com内容抓取</title>
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
			$ToysrusID = $match[1];
			if ($v == "import")
			{
				$LegoID = $_POST["$ToysrusID"];
				$query = "INSERT INTO Toysrus_Item (LegoID, ToysrusID) VALUES ('".$mysqli->real_escape_string($LegoID)."', '".$mysqli->real_escape_string($ToysrusID)."');";
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
	<form id="form" action="get_toysrus.php?action=update" method="post">
	<table>
	<tr><th>Order</th><th>ToysRus ID</th><th>Title</th><th>LEGOID</th><th>Operation</th></tr>
<?php
	$query = "SELECT LegoID,ToysrusID FROM Toysrus_Item;";
	$result = $mysqli->query($query);

	$ToysrusIDs = array();
	while ($row = $result->fetch_array(MYSQLI_ASSOC))
	{
		$ToysrusID = $row["ToysrusID"];
		$ToysrusIDs["$ToysrusID"] = $row["LegoID"];
	}


	require_once("simple_html_dom.php");
	mb_internal_encoding('utf-8');
	if ($_GET["page"])
	{
		$page = $_GET["page"];
	}
	else
	{
		$page = 4;
	}
	$show = 1;
	for ($i = 1; $i <= $page; $i++)
	{
		$url = 'http://www.toysrus.com/family/index.jsp?categoryId=31820206&view=all&page='.$i;
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
		$items = $html->find('//*[@id="familyProducts"]/div/div/div[class="prodloop_cont"]');
		foreach ($items as $item)
		{
			$itemhtml = str_get_html($item);
			$toysrusID = $itemhtml->find('/div[class="varHeightTop"]/div[class="prodloop-thumbnail"]/div[class="expressShopButtonGlobal button"]',0)->getAttribute('data-productid');
			//var_dump($toysrusID);

			if (!isset($ToysrusIDs["$toysrusID"]))
			#不在数据库内的新套装：
			{
				$order = intval($itemhtml->find('/div[class="varHeightTop"]/div[class="prodloop-thumbnail"]/div[class="expressShopButtonGlobal button"]',0)->getAttribute('data-index'));
				$order = ( $i - 1 ) * 200 + $order;
				$title = trim($itemhtml->find('/div[class="varHeightTop"]/a[class="prodtitle"]',0)->innertext);
				
				/*
				if ($itemhtml->find('div ul li[class="newp"] a del',0))
				{
					$price = str_replace("-", " ", trim($itemhtml->find('div ul li[class="newp"] a del',0)->innertext));
				}
				elseif ($itemhtml->find('div ul li[class="newp"] a span',0))
				{
					$price = str_replace("-", " ", trim($itemhtml->find('div ul li[class="newp"] a span',0)->innertext));
				}
				*/
				$pic = $itemhtml->find('/div[class="varHeightTop"]/div[class="prodloop-thumbnail"]/a/img',0)->class;
				$pic = "http://www.toysrus.com".$pic;
				preg_match_all("/\d{4,8}/u", html_entity_decode($title, ENT_NOQUOTES, 'UTF-8'), $matches);
				$legoid = array_pop(array_pop($matches));
				if (isset($legoid))
				{
					echo "<tr><td>".$order."</td><td><a href=\"http://www.toysrus.com/product/index.jsp?productId=".$toysrusID."\" target=\"_blank\">".$toysrusID."</a></td><td>".$title."</td><td><input type=\"text\" name=\"".$toysrusID."\" size=\"6\" value=\"".$legoid."\" /></td><td><input type=\"radio\" checked=\"checked\" name=\"rad_$toysrusID\" value=\"import\" />导入<br /><input type=\"radio\" name=\"rad_$toysrusID\" value=\"ignore\" />暂不<br /><input type=\"radio\" name=\"rad_$toysrusID\" value=\"never\" />永不</td></tr>";
				}
				else
				{
					echo "<tr><td>".$order."</td><td><a href=\"http://www.toysrus.com/product/index.jsp?productId=".$toysrusID."\" target=\"_blank\">".$toysrusID."</a></td><td>".$title."</td><td><input type=\"text\" name=\"".$toysrusID."\" size=\"6\" value=\"".$legoid."\" /></td><td><input type=\"radio\" name=\"rad_$toysrusID\" value=\"import\" />导入<br /><input type=\"radio\" name=\"rad_$toysrusID\" value=\"ignore\" checked=\"checked\" />暂不<br /><input type=\"radio\" name=\"rad_$toysrusID\" value=\"never\" />永不</td></tr>";
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
