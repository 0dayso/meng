<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <link rel="stylesheet" href="table.css">
	<script type="text/javascript" src="jquery.js"></script>
	<script type="text/javascript" src="scripts.js"></script>
	<script type="text/javascript" src="sorttable.js"></script>
	<script language="JavaScript">
	</script>
    <title>BN信息</title>
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
	
	$query = "SELECT * FROM BarnesNoble_Item;";
	$result = $mysqli->query($query);

	$BNItems = array();
	while ($row = $result->fetch_array(MYSQLI_ASSOC))
	{
    	$BNItems[$row['EAN']] = $row['LegoID'];
	}
	
	$query = "SELECT * FROM Taobao_Price;";
	$result = $mysqli->query($query);

	$TaobaoPrices = array();
	while ($row = $result->fetch_array(MYSQLI_ASSOC))
	{
    	$TaobaoPrices[$row['LegoID']] = $row['Price'];
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
	while ($page <= 8)
	{
		$startfrom = ($page-1)*90+1;
		$url = "http://www.barnesandnoble.com/s?CAT=1024014&view=grid&store=toy&sort=SA&size=90&startat=".$startfrom;
		$ch = curl_init();
		$timeout = 5; 
		curl_setopt($ch, CURLOPT_URL, $url); 
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
		$result = curl_exec($ch); 
		curl_close($ch);

		$html = str_get_html($result);
		$EANs = array();
		foreach($html->find('./ol[@class="result-set box"]/li') as $item)
		{

			$href = $item->find('./div[@class="price-format"]/a', 0)->href;
			preg_match_all("/\/p\/(.*)\/(\d+)\?ean=(\d+)&/u", html_entity_decode($href, ENT_NOQUOTES, 'UTF-8'), $matches);
			$title = str_replace("toys games lego ", "", str_replace("-", " ", $matches[1][0]));
			//$BNID = $matches[2][0];			
			$EAN = $matches[3][0];
			$from = $item->find('./div[@class="price-format"]/a/span[@class="format"]', 0)->plaintext;
			$price = str_replace("$", "", trim($item->find('./div[@class="price-format"]/a/span[@class="price"]', 0)->plaintext));
			if ($EAN =="673419167611")
			{
				var_dump($href, $title);
			}
			if (isset($BNItems[$EAN]) && !isset($EANs[$EAN]))
			{
				$EANs[$EAN] = 1;
				$legoid = $BNItems[$EAN];
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
				$shipping = round((12.98+4.98)/2*6.2, 2);
				$cnprice = $Sets[$legoid]->{'CNPrice'};
				$taobaolink = "taobao_price.php?legoid=".$legoid;
				if (isset($TaobaoPrices[$legoid]))
				{
					$taobaoprice = $TaobaoPrices[$legoid];
					$taobaotext = "<a href=\"$taobaolink\">¥".$taobaoprice."</a>";
				}
				elseif ($cnprice > 0)
				{
					$taobaoprice = round($cnprice*0.6, 2);
					$taobaotext = "<a href=\"$taobaolink\">~¥".$taobaoprice."</a>";
				}
				else
				{
					$taobaoprice = 0;
					$taobaotext = "<a href=\"$taobaolink\">N/A</a>";
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
					$totalprice = round($price*6.2+$shipping, 2);
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
					echo "<tr><td><a href=\"http://www.barnesandnoble.com/p/?ean=".$EAN."\"><img src=\"pic.php?thumb150=".$legoid."\"><br/>$legoid</a></td><td>$msrp</td><td>$price</td><td><a href=\"$amazonurl\">$amazonprice</a></td><td>$discount%</td><td>¥$shipping</td><td>¥$cnprice</td><td>¥$totalprice</td><td>$taobaotext</td><td>$rev</td><td>$revrate%</td></tr>";
				}
			}
			else
			{
				$count++;
			}
		}
		$page++;
	}
	echo "</table><p><a href=\"get_bn.php\">共计 $count 项商品无法匹配LegoID。</a></p>";
?>
</body>
</html>