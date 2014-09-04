<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <link rel="stylesheet" href="table.css">
	<script type="text/javascript" src="jquery.js"></script>
	<script type="text/javascript" src="scripts.js"></script>
	<script type="text/javascript" src="sorttable.js"></script>
	<script language="JavaScript">
	</script>
    <title>Walmart信息</title>
</head>
<body>
<?php
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

	$query = "SELECT * FROM DB_Set;";
	$result = $mysqli->query($query);

	$Sets = array();
	while ($row = $result->fetch_array(MYSQLI_ASSOC))
	{
		$setinfo = new stdClass();
		$LegoID = $row["LegoID"];
		$setinfo->{'ETitle'} = $row["ETitle"];
		$setinfo->{'CTitle'} = $row["CTitle"];
		$setinfo->{'Year'} = $row["Year"];
		$setinfo->{'Pieces'} = $row["Pieces"];
		$setinfo->{'Weight'} = $row["Weight"];
		$setinfo->{'Length'} = $row["Length"];
		$setinfo->{'Width'} = $row["Width"];
		$setinfo->{'Height'} = $row["Height"];
		$setinfo->{'ETheme'} = $row["ETheme"];
		$setinfo->{'CTheme'} = $row["CTheme"];
		$setinfo->{'USPrice'} = $row["USPrice"];
		$setinfo->{'CNPrice'} = $row["CNPrice"];
		$Sets["$LegoID"] = $setinfo;
	}
	
	$query = "SELECT * FROM Walmart_Item;";
	$result = $mysqli->query($query);

	$WalmartItems = array();
	while ($row = $result->fetch_array(MYSQLI_ASSOC))
	{
    	$WalmartItems[$row['WalmartID']] = $row['LegoID'];
	}
	
	$query = "SELECT LegoID,LowPrice FROM V_TB_Price;";
	$result = $mysqli->query($query);

	$TaobaoPrices = array();
	while ($row = $result->fetch_array(MYSQLI_ASSOC))
	{
		$taobaoinfo = new stdClass();

    	$taobaoinfo->{'Price'} = $row['LowPrice'];
    	//$taobaoinfo->{'Mins'} = round((time()-strtotime($row['UpdateTime']))/60);
    	$TaobaoPrices[$row['LegoID']] = $taobaoinfo;

	}
	
	$query = "SELECT * FROM PW_AmazonInfo WHERE Country='US';";
	$result = $mysqli->query($query);
	
	$AmazonPrices = array();
	while ($row = $result->fetch_array(MYSQLI_ASSOC))
	{
		$LegoID = $row["LegoID"];

		$LegoInfo = new stdClass();
		$LegoInfo->{"Price"} = $row["LastValue"];
		$LegoInfo->{"ASIN"} = $row["ASIN"];

		$LegoInfo->{"Mins"} = round((time()-strtotime($row["LastScanTime"]))/60, 0);
		if ($row["LastValue"] > 0 && $row['Scan'] < 180)
		{
			$AmazonPrices["$LegoID"] = $LegoInfo;
		}
		else
		{
			$LegoInfo->{"Price"} = "OOS";
			$AmazonPrices["$LegoID"] = $LegoInfo;
		}
	}
	//echo var_dump($AmazonPrices);
	$result->free();
	
	echo "<table class=\"sortable\"><tr><th>Lego ID</th><th>US Price</th><th>Walmart Price</th><th>Amazon Price</th><th>Discount</th><th>Shipping Fee</th><th>CN MSRP</th><th>到手价</th><th class=\"sorttable_nosort\">Taobao Price</th><th>Rev</th><th>Rev Rate</th></tr>";

	$page = 1;
	$count = 0;
	while ($page <= 6)
	{
		$startfrom = ($page-1)*60;
		$url = "http://www.walmart.com/browse/building-blocks-sets/building-sets/lego/4171_4186_1044000/YnJhbmQ6TEVHTwieie?ic=60_".$startfrom;
		$ch = curl_init();
		$timeout = 5; 
		curl_setopt($ch, CURLOPT_URL, $url); 
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
		$result = curl_exec($ch); 
		curl_close($ch);

		$html = str_get_html($result);
		foreach($html->find('//div[@class="prodInfoBox"]') as $item)
		{
			$title = str_replace(" Building Set", "", str_replace(" Play Set", "", $item->find('./a[@class=\'prodLink ListItemLink\']',0)->plaintext));
			$walmart_id = substr($item->find('./a[@class=\'prodLink ListItemLink\']',0)->href, -8);
			$priceint = str_replace("$", "", str_replace(".", "", $item->find('./div/div/div/div[@class=\'camelPrice\']/span[@class=\'bigPriceText2\']', 0)->plaintext));
			$pricedec = $item->find('./div/div/div/div[@class=\'camelPrice\']/span[@class=\'smallPriceText2\']', 0)->plaintext;
			$price = $priceint + 0.01 * $pricedec;
			
			if (isset($WalmartItems[$walmart_id]))
			{
				$legoid = $WalmartItems[$walmart_id];
				$msrp = $Sets[$legoid]->{'USPrice'};
				$discount = 0;
				if ($msrp > 0)
				{
					$discount = round($price/$msrp*100-100,2);
				}
				else
				{
					$disconut = 0;
				}
				$shipping = round($Sets[$legoid]->{'Weight'}*50/453.59237, 2);
				$cnprice = $Sets[$legoid]->{'CNPrice'};
				$taobaolink = "tb_price.php?legoid=".$legoid;

				if (isset($TaobaoPrices[$legoid]))
				{
					$taobaoprice = $TaobaoPrices[$legoid]->{'Price'};
					$taobaotext = "<a target=\"_blank\" href=\"$taobaolink\">¥".$taobaoprice."</a>";
				}
				elseif ($cnprice > 0)
				{
					$taobaoprice = round($CNPrice*0.6, 2);
					$taobaotext = "<a target=\"_blank\" href=\"$taobaolink\">~¥".$taobaoprice."</a>";
				}
				else
				{
					$taobaoprice = 0;
					$taobaotext = "<a target=\"_blank\" href=\"$taobaolink\&updatedb\">N/A</a>";
				}

				if (isset($AmazonPrices[$legoid]))
				{
					$amazonprice = $AmazonPrices[$legoid]->{'Price'};
					$amazonurl = "http://www.amazon.com/dp/".$AmazonPrices[$legoid]->{'ASIN'};
				}
				else
				{
					$amazonprice = "N/A";
					$amazonurl = "legodb_edit?id=".$legoid;
				}
				if (is_numeric($amazonprice))
				{
					$totalprice = round(min($price,$amazonprice)*6.2+$shipping, 2);
				}
				else
				{
					$totalprice = round($price*6.2+$shipping, 2);
				}
				if (($taobaoprice > 0) && ($shipping > 0))
				{
					$rev = round($taobaoprice-$totalprice, 2);
					$revrate = round($rev/$totalprice*100, 2);
				}
				else
				{
					$rev = 0;
					$revrate = 0;
				}
				if ($price > 0 )
				{
					echo "<tr><td><a href=\"http://www.walmart.com/ip/$walmart_id\"><img src=\"pic.php?thumb150=".$legoid."\"><br/>$legoid</a></td><td>$msrp</td><td>$price</td><td><a href=\"$amazonurl\">$amazonprice</a></td><td>$discount%</td><td>¥$shipping</td><td>¥$cnprice</td><td>¥$totalprice</td><td>$taobaotext</td><td>$rev</td><td>$revrate%</td></tr>";
				}
			}
			else
			{
				$count++;
			}
		}
		$page++;
	}
	echo "</table><p><a href=\"get_walmart.php\">共计 $count 项商品无法匹配LegoID。</a></p>";
?>
</body>
</html>