<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <link rel="stylesheet" href="table.css">
	<script type="text/javascript" src="script/jquery-1.11.1.min.js"></script>
	<script type="text/javascript" src="script/jquery.tablesorter.min.js"></script>
	<script type="text/javascript" src="script/jquery.scrollLoading.min.js"></script>
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
    <title>Amazon.US特价信息</title>
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

	$query = "SELECT LegoID,LowPrice,TotalVolume,Sellers FROM V_TB_Price;";
	$result = $mysqli->query($query);

	$TaobaoPrices = array();
	while ($row = $result->fetch_array(MYSQLI_ASSOC))
	{
		$taobaoinfo = new stdClass();

    	$taobaoinfo->{'Price'} = $row['LowPrice'];
    	$vol = $row['TotalVolume'];
    	$taobaoinfo->{'VolRate'} = $vol;
    	/*
    	if ($vol >= 200)
    	{
    		$taobaoinfo->{'VolRate'} = 5;
    	}
    	elseif ($vol >= 100)
    	{
    		$taobaoinfo->{'VolRate'} = 4;
    	}
    	elseif ($vol >= 50)
    	{
    		$taobaoinfo->{'VolRate'} = 3;
    	}
    	elseif ($vol >= 25)
    	{
    		$taobaoinfo->{'VolRate'} = 2;
    	}
    	elseif ($vol >= 10)
    	{
    		$taobaoinfo->{'VolRate'} = 1;
    	}
    	else
    	{
    		$taobaoinfo->{'VolRate'} = 0;
    	}
    	*/
    	$sellers = $row['Sellers'];
    	$taobaoinfo->{'SellerRate'} = $sellers;
    	/*
    	if ($sellers >= 200)
    	{
    		$taobaoinfo->{'SellerRate'} = 5;
    	}
    	elseif ($sellers >= 100)
    	{
    		$taobaoinfo->{'SellerRate'} = 4;
    	}
    	elseif ($sellers >= 50)
    	{
    		$taobaoinfo->{'SellerRate'} = 3;
    	}
    	elseif ($sellers >= 25)
    	{
    		$taobaoinfo->{'SellerRate'} = 2;
    	}
    	elseif ($sellers >= 10)
    	{
    		$taobaoinfo->{'SellerRate'} = 1;
    	}
    	else
    	{
    		$taobaoinfo->{'SellerRate'} = 0;
    	}
    	*/
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
	
	echo "<table class=\"sortable\"><tr><th>ID</th><th>US Price</th><th>Discount</th><th>Shipping Fee</th><th>CN MSRP</th><th>到手价</th><th class=\"sorttable_nosort\">TB Price</th><th>Vol</th><th>Seller</th><th>Rev</th><th>Rev Rate</th></tr>";
	$USItems = array();
	$CNASINs = array();
	while ($row = $result->fetch_array(MYSQLI_ASSOC))
	{
		$LegoID = $row["LegoID"];

		$LegoInfo = array();
		$LegoInfo["Price"] = $row["LastValue"];
		$LegoInfo["ASIN"] = $row["ASIN"];

		$LegoInfo["Mins"] = round((time()-strtotime($row["LastScanTime"]))/60);
		if ($row["Country"] == "US" && $row["LastValue"] > 0)
		{
			$USItems["$LegoID"] =$LegoInfo;
		}
		elseif ($row["Country"] == "CN")
		{
			$CNASINs["$LegoID"] = $row["ASIN"];
		}
	}
	$result->free();
	$mysqli->close();

	foreach ($USItems as $LegoID=>$LegoInfo )
	{
		if (isset($Sets["$LegoID"]) && $Sets["$LegoID"]->{'USPrice'} > 0)
		{
			$Price = $LegoInfo["Price"];
			$MSRP = $Sets["$LegoID"]->{'USPrice'};
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
				$TotalPrice = round($Price*6.2+$Shipping, 2);
				$taobaolink = "tb_price.php?legoid=".$LegoID;

				if (isset($TaobaoPrices[$LegoID]))
				{
					$TBPrice = $TaobaoPrices[$LegoID]->{'Price'};
					$VolRate = $TaobaoPrices[$LegoID]->{'VolRate'};
					$SellerRate = $TaobaoPrices[$LegoID]->{'SellerRate'};
					//$UpdateMins = $TaobaoPrices[$LegoID]->{'Mins'};
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
				if (($TBPrice > 0) && ($Shipping > 0))
				{
					$Rev = round($TBPrice-$TotalPrice, 2);
					$RevRate = round($Rev/$TotalPrice*100, 2);
				}
				else
				{
					$Rev = 0;
					$RevRate = 0;
				}
				if ($Mins <120)
				{
					echo "<tr><td><a target=\"_blank\" href=\"http://www.amazon.com/dp/".$LegoInfo["ASIN"]."/ref=as_li_ss_tl?ie=UTF8&camp=1789&creative=390957&creativeASIN=".$LegoInfo["ASIN"]."&linkCode=as2&tag=legocomus-20\"><img class=\"scrollLoading\" src=\"images/loading.gif\" data-url=\"setimg/thumb150/".$LegoID."_150.jpg\"><br/>".$LegoID."</a></td><td>$".$Price." (".$Mins."mins ago)<br/>MSRP:$".$MSRP."</td><td>".$Discount."%</td>".$ShippingHTML.$CNPriceHTML."<td>¥".$TotalPrice."</td><td>".$TBPriceHTML."</td><td>".$VolRate."</td><td>".$SellerRate."</td><td sorttable_customkey=\"".$Rev."\">¥".$Rev."</td><td>".$RevRate."%</td></tr>";
				}
			}
		}

	}

?>
</table>
<script type="text/javascript" charset="utf-8">
$(function() { $(".scrollLoading").scrollLoading(); });
</script>
</body>
</html>
