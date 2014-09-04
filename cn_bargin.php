<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <link rel="stylesheet" href="table.css">
	<script type="text/javascript" src="jquery.js"></script>
	<script type="text/javascript" src="scripts.js"></script>
	<script type="text/javascript" src="sorttable.js"></script>
	<script language="JavaScript">
	function query_cnprice(legoid, asin)  
	{
		$.get("ajax_amzncn.php", { legoid: legoid, asin: asin}, function(data) {update_cnprice(asin, data);} );
	}
	function query_weight(legoid)
	{
		$.get("ajax_bl.php", { legoid: legoid, update: 1}, function(data) {update_weight(legoid, data);} );
	}
	function update_cnprice(asin, price)
	{
		if (price > 0)
		{
			$("#price_"+asin).html("¥"+price);
		}
		else
		{
			$("#price_"+asin).html(price);
		}
	}
	function update_weight(legoid, info)
	{
		var obj = jQuery.parseJSON(info);
		var shipping = obj.Weight*40/453.59237;
		shipping = shipping.toFixed(2);
		$("#weight_"+legoid).html("¥"+shipping);
		
	}
	</script>
    <title>Amazon.CN特价信息</title>
</head>
<body>
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
	
	$query = "SELECT * FROM PW_AmazonInfo;";
	$result = $mysqli->query($query);
	
	echo "<table class=\"sortable\"><tr><th>ID</th><th>US Price</th><th>Discount</th><th>CN MSRP</th><th>到手价</th><th class=\"sorttable_nosort\">Taobao Price</th><th>Rev</th><th>Rev Rate</th></tr>";
	$USItems = array();
	$CNASINs = array();
	while ($row = $result->fetch_array(MYSQLI_ASSOC))
	{
		$LegoID = $row["LegoID"];

		$LegoInfo = array();
		$LegoInfo["Price"] = $row["LastValue"];
		$LegoInfo["ASIN"] = $row["ASIN"];

		$LegoInfo["Mins"] = round((time()-strtotime($row["LastScanTime"]))/60);
		if ($row["Country"] == "CN" && $row["LastValue"] > 0)
		{
			$CNItems["$LegoID"] =$LegoInfo;
		}
		elseif ($row["Country"] == "US")
		{
			$USASINs["$LegoID"] = $row["ASIN"];
		}
	}
	$result->free();
	$mysqli->close();

	foreach ($CNItems as $LegoID=>$LegoInfo )
	{
		if (isset($Sets["$LegoID"]) && $Sets["$LegoID"]->{'CNPrice'} > 0)
		{
			$Price = $LegoInfo["Price"];

			$MSRP = $Sets["$LegoID"]->{'CNPrice'};
			if ($Price >= $MSRP)
			{
				$Discount = round($Price/$MSRP*100,2);
			}
			else
			{
				$Discount = -1*round(100-$Price/$MSRP*100,2);
				$Shipping = round($Sets["$LegoID"]->{'Weight'}*40/453.59237, 2);
				$CNPrice = $Sets["$LegoID"]->{'CNPrice'};
				if ($CNPrice == 0 && isset($CNASINs["$LegoID"]))
				{
					$ASIN = $CNASINs["$LegoID"];
					$CNPriceHTML = "<td id=\"price_".$ASIN."\"><input type=\"button\" value=\"".$ASIN."\" onclick=\"query_cnprice($LegoID, '$ASIN');\"/></td>";
				}
				else
				{
					$CNPriceHTML = "<td sorttable_customkey=\"".$CNPrice."\">¥".$CNPrice."</td>";
				}
				$Mins = $LegoInfo["Mins"];
				if ($Shipping == 0)
				{
					$ShippingHTML = "<td id=\"weight_".$LegoID."\"><input type=\"button\" value=\"".$LegoID."\" onclick=\"query_weight($LegoID);\"/></td>";
				}
				else
				{
					$ShippingHTML = "<td>¥".$Shipping."</td>";

				}

				/*满499-100
				if ($Price >= 499)
				{
					$TotalPrice = $Price - 100;
				}
				else
				{
					$TotalPrice = round($Price*0.8, 2);
				}
				*/
				$TotalPrice = round($Price, 2);
				$taobaolink = "tb_price.php?legoid=".$LegoID;

				if (isset($TaobaoPrices[$LegoID]))
				{
					$TBPrice = $TaobaoPrices[$LegoID]->{'Price'};
					$TBPriceHTML = "<a target=\"_blank\" href=\"$taobaolink\">¥".$TBPrice."</a>";
				}
				elseif ($CNPrice > 0)
				{
					$TBPrice = round($CNPrice*0.6, 2);
					$TBPriceHTML = "<a target=\"_blank\" href=\"$taobaolink\">~¥".$TBPrice."</a>";
				}
				else
				{
					$TBPrice = 0;
					$TBPriceHTML = "<a target=\"_blank\" href=\"$taobaolink\">N/A</a>";
				}
				if ($TBPrice > 0)
				{
					$Rev = round($TBPrice-$TotalPrice, 2);
					$RevRate = round($Rev/$TotalPrice*100, 2);
				}
				else
				{
					$Rev = 0;
					$RevRate = 0;
				}
				if ($Mins < 120)
				{
					echo "<tr><td><a target=\"_blank\" href=\"http://www.amazon.cn/dp/".$LegoInfo["ASIN"]."/ref=as_li_ss_tl?ie=UTF8&camp=1789&creative=390957&creativeASIN=".$LegoInfo["ASIN"]."&linkCode=as2&tag=brickcn-20\"><img src=\"pic.php?thumb150=".$LegoID."\"><br/>".$LegoID."</a></td><td>￥".$Price." (".$Mins."mins ago)</td><td>".$Discount."%</td>".$CNPriceHTML."<td>¥".$TotalPrice."</td><td>".$TBPriceHTML."</td><td sorttable_customkey=\"".$Rev."\">¥".$Rev."</td><td>".$RevRate."%</td></tr>";
				}
			}
		}

	}

?>
</table>
</body>
</html>
