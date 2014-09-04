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
    	$taobaoinfo->{'Vol'} = $row['TotalVolume'];
    	$taobaoinfo->{'Sellers'} = $row['Sellers'];
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
	
	$query = "SELECT * FROM Import;";
	$result = $mysqli->query($query);
	
	echo "<table class=\"sortable\"><tr><th>ID</th><th>MSRP</th><th>Onsale</th><th>Discount</th><th>Shipping></th><th>Total</th><th class=\"sorttable_nosort\">TB Price</th><th>Vol</th><th>Seller</th><th>Rev</th><th>Rev Rate</th></tr>";
	while ($row = $result->fetch_array(MYSQLI_ASSOC))
	{
		$LegoID = $row["LegoID"];
		$Price = round($row["Price"] * 0.58, 2);

		if (isset($Sets["$LegoID"]))
		{
			$MSRP = $Sets["$LegoID"]->{'CNPrice'};
			if (!$MSRP)
			{
				echo "No MSRP for $LegoID.\r\n";
			}
			elseif ($Price >= $MSRP)
			{
				$Discount = round($Price/$MSRP*100,2);
			}
			else
			{
				$Discount = -1*round(100-$Price/$MSRP*100,2);
				$Shipping = round($Sets["$LegoID"]->{'Weight'}*6/1000, 2);
				if ($Shipping == 0)
				{
					$ShippingHTML = "<td id=\"weight_".$LegoID."\"><input type=\"button\" value=\"".$LegoID."\" onclick=\"query_weight($LegoID);\"/></td>";
				}
				else
				{
					$ShippingHTML = "<td>¥".$Shipping."</td>";

				}
				$TotalPrice = round($Price+$Shipping, 2);
				$taobaolink = "tb_price.php?legoid=".$LegoID;

				if (isset($TaobaoPrices[$LegoID]))
				{
					$TBPrice = $TaobaoPrices[$LegoID]->{'Price'};
					$Vol = $TaobaoPrices[$LegoID]->{'Vol'};
					$Sellers = $TaobaoPrices[$LegoID]->{'Sellers'};
					$TBPriceHTML = "<a target=\"_blank\" href=\"$taobaolink\">¥".$TBPrice."</a>";
				}
				else
				{
					$TBPrice = 0;
					$TBPriceHTML = "<a target=\"_blank\" href=\"$taobaolink&updatedb\">N/A</a>";
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
				if ($Rev >= 0 )//&& $Vol >= 25)
				{
					echo "<tr><td><img src=\"pic.php?thumb150=".$LegoID."\"><br/>".$LegoID."</td><td>".$MSRP."</td><td>".$Price."</td><td>".$Discount."%</td>".$ShippingHTML."<td>¥".$TotalPrice."</td><td>".$TBPriceHTML."</td><td>".$Vol."</td><td>".$Sellers."</td><td sorttable_customkey=\"".$Rev."\">¥".$Rev."</td><td>".$RevRate."%</td></tr>";
				}
			}
		}
		else
		{
			echo "$LegoID is not in DB.\r\n";
		}

	}

?>
</table>
</body>
</html>
