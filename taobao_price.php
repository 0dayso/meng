<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <link rel="stylesheet" href="table.css">
    <script src="sorttable.js"></script>
    <title>Taobao价格查询</title>
</head>
<body>
<table>
<?php
require_once("simple_html_dom.php");
mb_internal_encoding('utf-8');
if ($_GET["legoid"])
{
	$legoid = $_GET["legoid"];
}
else
{
	$legoid = "9493";
}

$pagecount = 1;
$prices = array();
$items = array();
$soldprices = array();
for ($i = 1; $i <= $pagecount; $i++)
{
	$url = "http://s.taobao.com/search?q=lego+".$legoid."&commend=all&ssid=s5-e&newpre=null&olu=yes&isprepay=1&filterFineness=2&atype=b&sort=price-asc&s=".(($i-1)*40);
	$ch = curl_init(); 
	$timeout = 5; 
	curl_setopt($ch, CURLOPT_URL, $url); 
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 
	curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout); 
	$contents = curl_exec($ch); 
	curl_close($ch);
	$html = str_get_html($contents);
	$pagehtml = iconv('GBK', 'UTF-8', trim($html->find('div[class="pagination"]', 0)->plaintext));
	preg_match('/(\d+)\/(\d+)/', $pagehtml, $matches);
	$pagecount = $matches[2];

	$itemlist = $html->find('div[class="tb-content"] div[class="row grid-view"] div[class="col item icon-datalink"]');
	foreach ($itemlist as $item)
	{
		$itemhtml = str_get_html($item);
	
		$iteminfo = new stdClass();
		$iteminfo->{'nid'} = trim($item->nid);
		
		$iteminfo->{'title'} = iconv('GBK', 'UTF-8', trim($itemhtml->find('div[class="item-box"]/h3[class="summary"]/a', 0)->title));
		if (preg_match("/[A-Za-z]{2}\d{3}/u", html_entity_decode($iteminfo->{'title'}, ENT_NOQUOTES, 'UTF-8')))
		{
			//echo "w2d3:".$iteminfo->{'title'}."<br/>";
		}
		elseif (preg_match("/[A-Za-z]{3}\d{3}/u", html_entity_decode($iteminfo->{'title'}, ENT_NOQUOTES, 'UTF-8')))
		{
			//echo "w3d3:".$iteminfo->{'title'}."<br/>";
		}
		elseif (preg_match("/\d{4}+/u", html_entity_decode(preg_replace("/lego/u", "", preg_replace("/201[0-4]/u", "", str_replace($legoid, "", $iteminfo->{'title'}))), ENT_NOQUOTES, 'UTF-8')))
		{
			//echo "d4:".$iteminfo->{'title'}."<br/>";
		}
		elseif (preg_match("/杀肉/u", html_entity_decode($iteminfo->{'title'}, ENT_NOQUOTES, 'UTF-8')))
		{
			//echo "杀肉:".$iteminfo->{'title'}."<br/>";
		}
		elseif (preg_match("/载具/u", html_entity_decode($iteminfo->{'title'}, ENT_NOQUOTES, 'UTF-8')))
		{
			//echo "载具:".$iteminfo->{'title'}."<br/>";
		}
		elseif (preg_match("/代购/u", html_entity_decode($iteminfo->{'title'}, ENT_NOQUOTES, 'UTF-8')))
		{
			//echo "代购:".$iteminfo->{'title'}."<br/>";
		}
		elseif (preg_match("/单出/u", html_entity_decode($iteminfo->{'title'}, ENT_NOQUOTES, 'UTF-8')))
		{
			//echo "单出:".$iteminfo->{'title'}."<br/>";
		}
		elseif (preg_match("/预定/u", html_entity_decode($iteminfo->{'title'}, ENT_NOQUOTES, 'UTF-8')))
		{
			//echo "预定:".$iteminfo->{'title'}."<br/>";
		}
		elseif (preg_match("/图纸/u", html_entity_decode($iteminfo->{'title'}, ENT_NOQUOTES, 'UTF-8')))
		{
			//echo "预定:".$iteminfo->{'title'}."<br/>";
		}
		elseif (preg_match("/配件/u", html_entity_decode($iteminfo->{'title'}, ENT_NOQUOTES, 'UTF-8')))
		{
			//echo "预定:".$iteminfo->{'title'}."<br/>";
		}
		elseif (preg_match("/零件/u", html_entity_decode($iteminfo->{'title'}, ENT_NOQUOTES, 'UTF-8')))
		{
			//echo "预定:".$iteminfo->{'title'}."<br/>";
		}
		elseif (preg_match("/国产/u", html_entity_decode($iteminfo->{'title'}, ENT_NOQUOTES, 'UTF-8')))
		{
			//echo "预定:".$iteminfo->{'title'}."<br/>";
		}
		elseif (preg_match("/乐高式/u", html_entity_decode($iteminfo->{'title'}, ENT_NOQUOTES, 'UTF-8')))
		{
			//echo "预定:".$iteminfo->{'title'}."<br/>";
		}
		elseif (preg_match("/邦宝/u", html_entity_decode($iteminfo->{'title'}, ENT_NOQUOTES, 'UTF-8')))
		{
			//echo "预定:".$iteminfo->{'title'}."<br/>";
		}
		else
		{
			$strPrice = iconv('GBK', 'UTF-8', trim($itemhtml->find('div[class="col price"]',0)->innertext));
			preg_match_all("/\d+\.\d{2}/u", html_entity_decode($strPrice, ENT_NOQUOTES, 'UTF-8'), $matchPrice);
			$iteminfo->{'price'} = array_pop(array_pop($matchPrice));

			$strSold = iconv('GBK', 'UTF-8', trim($itemhtml->find('div[class="col dealing"]',0)->innertext));
			preg_match_all("/\d+/u", html_entity_decode($strSold, ENT_NOQUOTES, 'UTF-8'), $matchSold);
			$iteminfo->{'vol'} = array_pop(array_pop($matchSold));

			$iteminfo->{'seller'} = iconv('GBK', 'UTF-8', trim($itemhtml->find('a[trace="srpwwnick"]',0)->innertext));
			$iteminfo->{'loc'} = iconv('GBK', 'UTF-8', trim($itemhtml->find('div[class="col end loc"]',0)->innertext));

			array_push($prices, $iteminfo->{'price'});
			array_push($items, $iteminfo);
			if ($iteminfo->{'vol'} > 0)
			{
				array_push($soldprices, $iteminfo->{'price'});
			}
		}

	}

}

//echo var_dump($prices);
$arrPrices = array();
foreach ($items as $item)
{
	if ($item->{'vol'} > 3)
	{
		array_push($arrPrices, $item->{'price'});
		array_push($arrPrices, $item->{'price'});
		array_push($arrPrices, $item->{'price'});
	}
	elseif ($item->{'vol'} > 0)
	{
		array_push($arrPrices, $item->{'price'});
	}
}
$arrPrices_count = count($arrPrices);
$maxidx = intval($arrPrices_count*0.90);
$minidx = intval($arrPrices_count*0.10);
$arrPrices_slice = array_slice($arrPrices,$minidx,$maxidx-$minidx);
$minvalue = $arrPrices_slice[0];
$maxvalue = $arrPrices_slice[count($arrPrices_slice)-1];

//var_dump($minvalue, $maxvalue);

//$totalnum = count($prices);
//$minnum = round($totalnum*0.25);
//$maxnum = round($totalnum*0.75);
//$minvalue = $prices[$minnum];
//$maxvalue = $prices[$maxnum];

//echo $minvalue, $maxvalue;

//echo var_dump(array_slice($prices,$minnum,$totalnum-$maxnum));



$avgprice = round(array_sum($soldprices)/count($soldprices),2);

$retitems = array();
$bjitems = array();
//asort($items);
$sum = 0;
$totalvol = 0;
$minprice = $avgprice;
$cheapcount = 0;
$normalcount = 0;
$expensivecount = 0;
foreach ($items as $item)
{
	if ($item->{'price'} >= $minvalue && $item->{'price'} <= $maxvalue)
	{
		array_push($retitems, $item);
		if ($item->{'loc'} == "北京")
		{
			array_push($bjitems, $item);
		}
		if ($item->{'vol'} > 0)
		{
			$sum = $sum + $item->{'vol'} * $item->{'price'};
			$totalvol = $totalvol + $item->{'vol'};
			if ($item->{'price'} < $minprice)
			{
				$minprice = $item->{'price'};
			}
		}
		$normalcount++;
	}
	elseif ($item->{'price'} < $avgprice*0.9)
	{
		$cheapcount++;
	}
	else
	{
		$expensivecount++;
	}
}
$avgsoldprice = round($sum/$totalvol,2);
$bjfirst = array_shift($bjitems);
if ($_GET["bjprice"] && isset($bjfirst))
{
	require("conn.php");
	require_once("simple_html_dom.php");

	date_default_timezone_set('Asia/Shanghai');
	$mysqli = new mysqli($mysql_server_name, $mysql_username, $mysql_password, $mysql_database);

	if (mysqli_connect_errno()) {
		printf("Database Connect failed: %s\n", mysqli_connect_error());
		exit();
	}

	$mysqli->query("SET NAMES UTF8;");
	$mysqli->query("SET time_zone = '+08:00';");
	
	$query = "INSERT INTO Taobao_Price (LegoID, TaobaoID, Price, Title, Seller) VALUES ('".$mysqli->real_escape_string($legoid)."','".$mysqli->real_escape_string($bjfirst->{'nid'})."','".$mysqli->real_escape_string($bjfirst->{'price'})."','".$mysqli->real_escape_string($bjfirst->{'title'})."','".$mysqli->real_escape_string($bjfirst->{'seller'})."') ON DUPLICATE KEY UPDATE TaobaoID='".$mysqli->real_escape_string($bjfirst->{'nid'})."', Title='".$mysqli->real_escape_string($bjfirst->{'title'})."', Seller='".$mysqli->real_escape_string($bjfirst->{'seller'})."', Price='".$mysqli->real_escape_string($bjfirst->{'price'})."';";
	//echo $query;
	$result = $mysqli->query($query);
	$mysqli->close();

	echo json_encode($bjfirst);
}
else
{
	echo "<table><tr><td>分布</td><td>".count($items).":".$cheapcount."/".$normalcount."(".round($normalcount/count($items)*100,2)."%)/".$expensivecount."</td><td>成交量</td><td>".$totalvol."</td><td>成交低价</td><td>".$minprice."</td><td>成交均价</td><td>".$avgsoldprice."</td><td>北京最低价</td><td>".$bjfirst->{'seller'}." - ".$bjfirst->{'price'}."</td></tr></table>";
	echo "<table class=\"sortable\"><tr><th>Link</th><th>Title</th><th>Price</th><th>Volume</th><th class=\"sorttable_nosort\">Seller</th><th>Location</th></tr>";
	foreach ($retitems as $item)
	{
		echo "<tr><td><a href='http://item.taobao.com/item.htm?id=".$item->{'nid'}."'>".$item->{'nid'}."</a></td><td>".$item->{'title'}."</td><td>".$item->{'price'}."</td><td>".$item->{'vol'}."</td><td>".$item->{'seller'}."</td><td>".$item->{'loc'}."</td></tr>";
	}
	echo "</table>";
}
?>
</table>
</body>
</html>
