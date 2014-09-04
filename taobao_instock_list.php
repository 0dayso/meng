<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <link rel="stylesheet" href="table.css">
	<script type="text/javascript" src="script/jquery-1.11.1.min.js"></script>
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
	</script>
    <title>库存列表</title>
</head>
<body>
<?php

include "appconf.php";
include "TopSdk.php";	

require("conn.php");

date_default_timezone_set('Asia/Shanghai');
$mysqli = new mysqli($mysql_server_name, $mysql_username, $mysql_password, $mysql_database);

if (mysqli_connect_errno()) {
	printf("Database Connect failed: %s\n", mysqli_connect_error());
	exit();
}

$mysqli->query("SET NAMES UTF8;");
$mysqli->query("SET time_zone = '+08:00';");

$query = "SELECT ItemID,LegoID FROM TB_Item;";
$result = $mysqli->query($query);

$TBItems = array();
while ($row = $result->fetch_array(MYSQLI_ASSOC))
{
	$ItemID = $row["ItemID"];
	$LegoID = $row["LegoID"];
	$TBItems["$ItemID"] = $LegoID;
}

$query = "SELECT LegoID,CNPrice FROM DB_Set;";
$result = $mysqli->query($query);

$CNPrices = array();
while ($row = $result->fetch_array(MYSQLI_ASSOC))
{
	$LegoID = $row["LegoID"];
	$CNPrices["$LegoID"] = $row["CNPrice"];
}

$query = "SELECT LegoID,LowPrice,TotalVolume,Sellers FROM V_TB_Price;";
$result = $mysqli->query($query);

$TaobaoPrices = array();
while ($row = $result->fetch_array(MYSQLI_ASSOC))
{
	$taobaoinfo = new stdClass();

	$taobaoinfo->{'Price'} = $row['LowPrice'];
	$vol = $row['TotalVolume'];
	$taobaoinfo->{'VolRate'} = $vol;
	$sellers = $row['Sellers'];
	$taobaoinfo->{'SellerRate'} = $sellers;
	$TaobaoPrices[$row['LegoID']] = $taobaoinfo;

}

$query = "SELECT LastValue,ASIN,LegoID FROM PW_AmazonInfo WHERE Country='CN';";
$result = $mysqli->query($query);

$CNItems = array();
while ($row = $result->fetch_array(MYSQLI_ASSOC))
{
	$LegoID = $row["LegoID"];

	$LegoInfo = new stdClass();
	
	$LegoInfo->{'Price'} = $row["LastValue"];
	$LegoInfo->{'ASIN'} = $row["ASIN"];
	$CNItems["$LegoID"] = $LegoInfo;

}

$query = "SELECT LegoID,COUNT(LegoID) AS Count, ROUND(MIN(Expense)*-1,2) AS MaxExp, ROUND(Avg(Expense)*-1,2) AS AvgExp FROM PSS_Item WHERE Status='InStock' GROUP BY LegoID;";
$result = $mysqli->query($query);

$StockPrices = array();
while ($row = $result->fetch_array(MYSQLI_ASSOC))
{
	$LegoID = $row["LegoID"];
	$stockinfo = new stdClass();
	$stockinfo->{'AvgExp'} = $row["AvgExp"];
	$stockinfo->{'Volume'} = $row["Count"];
	$StockPrices["$LegoID"] = $stockinfo;
}



$result->free();


$mysqli->close();

echo "<table class=\"sortable\"><tr><th>ItemID</th><th>LegoID</th><th>Number</th><th>Instock</th><th>Exp</th><th>List</th><th>TBPrice</th><th>ZPrice</th><th>Rev</th><th>Rev%</th><th>CNMSRP</th><th>65%</th></tr>";


//实例化TopClient类
$c = new TopClient;
$c->appkey = $client_id;
$c->secretKey = $client_secret;
$sessionKey = $access_token;

$reqItems = new ItemsOnsaleGetRequest;
//$reqItems = new ItemsInventoryGetRequest;
//$reqItems->setBanner("for_shelved");
//$reqItems->setBanner("sold_out");

$reqItems->setFields("num_iid,pic_url,title,num,outer_id,price");
$reqItems->setOrderBy("delist_time:asc");
$page = 1;
$pages = 1;
$pagesize = 40;

while ($page <= $pages)
{
	$reqItems->setPageNo($page);
	$reqItems->setPageSize($pagesize);
	$respItems = $c->execute($reqItems, $sessionKey);
	$total = $respItems->total_results;
	$pages = ceil($total/$pagesize);

	foreach ($respItems->items->item as $item)
	{
		$ItemID = $item->num_iid;
		if (!isset($TBItems["$ItemID"]))
		{		
			$LegoIDstr = $TBItems["$ItemID"];
		}
		elseif ($item->outer_id != "")
		{
			$LegoIDstr = $item->outer_id;
		}
		else
		{
			$LegoIDstr = "Unknown";
		}
		$tmp = explode("-", $LegoIDstr);
		$LegoID = $tmp[0];
		$Num = $item->num;
		$CNMSRP = $CNPrices["$LegoID"];
		$ASIN = $CNItems["$LegoID"]->{'ASIN'};

		if ($CNMSRP == 0 && $ASIN != "")
		{
			$CNMSRP = "<input type=\"button\" value=\"$ASIN\" onclick=\"query_cnprice($LegoID, '$ASIN');\"/>";
		}
		$DiscountPrice = ROUND($CNMSRP*0.65, 2);
		$TBPrice = $TaobaoPrices["$LegoID"]->{'Price'};
		$ZPrice = "<a href=\"http://www.amazon.cn/dp/$ASIN\">".$CNItems["$LegoID"]->{'Price'}."</a>";
		$ListPrice = $item->price;
		$StockPrice = $StockPrices["$LegoID"]->{'AvgExp'};
		$StockVol = $StockPrices["$LegoID"]->{'Volume'};
		if ($StockPrice > 0)
		{
			$Rev = $ListPrice - $StockPrice;
			$RevRate = ROUND($Rev/$StockPrice*100, 2);
		}
		else
		{
			$Rev = "";
			$RevRate = "";
		}
		echo "<tr><td><a href=\"http://item.taobao.com/item.htm?id=$ItemID\"><img height=120px width=120px src=\"".$item->pic_url."\"><br>$ItemID</a></td><td>$LegoIDstr</td><td>$Num</td><td>$StockVol</td><td>$StockPrice</td><td>$ListPrice</td><td>$TBPrice</td><td>$ZPrice</td><td>$Rev</td><td>$RevRate%</td><td id=\"price_$ASIN\">$CNMSRP</td><td>$DiscountPrice</td></tr>";
	}
	$page++;
}

echo "</table>";
?>
</body>
</html>
