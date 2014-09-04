<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
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

$query = "SELECT ItemID FROM `PSS_Item`;";
$result = $mysqli->query($query);


while ($row = $result->fetch_array(MYSQLI_ASSOC))
{
	$ItemID = $row["ItemID"];
	
	$querySold = "SELECT LinkID FROM PSS_Price WHERE Type = 'Sold' AND ItemID='".$ItemID."' LIMIT 1;";
	$resultSold = $mysqli->query($querySold);
	if ($row = $resultSold->fetch_array(MYSQLI_ASSOC))
	{
		$queryLocation = "SELECT ShippingFrom FROM PSS_SOrder INNER JOIN PSS_Delivery ON PSS_SOrder.OrderNumber=PSS_Delivery.RefID WHERE SOrderID='".$row["LinkID"]."';";
		$resultLocation = $mysqli->query($queryLocation);
		if ($row = $resultLocation->fetch_array(MYSQLI_ASSOC))
		{
			$Location = $row['ShippingFrom'];
		}
		else
		{
			$Location = "LEMO-BJ03";
		}
		$queryUpdate = "UPDATE PSS_Item SET Status='Sold', Location='".$Location."', Expense=(SELECT ROUND(SUM(CNYAmount),2) FROM PSS_Price WHERE CNYAmount<0 AND ItemID='".$ItemID."'), Revenue=(SELECT ROUND(SUM(CNYAmount),2) FROM PSS_Price WHERE CNYAmount>0 AND ItemID='".$ItemID."') WHERE ItemID=".$ItemID." LIMIT 1;";
		$mysqli->query($queryUpdate);

	}
	else
	{
		$queryDelivery = "SELECT Vendor,OrderNumber,DeliveryTime,ShippingTo FROM PSS_Price INNER JOIN PSS_Delivery ON LinkID=DeliveryID WHERE Type = 'Delivery' AND PSS_Price.ItemID='".$ItemID."' ORDER BY PriceID DESC LIMIT 1;";
		$resultDelivery = $mysqli->query($queryDelivery);
		if ($row = $resultDelivery->fetch_array(MYSQLI_ASSOC))
		{
			if ($row["DeliveryTime"] != "0000-00-00 00:00:00")
			{
				$Status = 'InStock';
				$Location = $row["ShippingTo"];
				$Vendor = $row["Vendor"];
				if ($Location == "")
				{
					if ($Vendor == "SFE" || $Vendor == "DANGDANG" || $Vendor == "dangdang" || $Vendor == "Z.CN" || $Vendor == "NA" || $Vendor == "STO" || $Vendor == "360BUY" || $Vendor == "DHL.DE" || $Vendor == "YUNDA" || $Vendor == "HTKY" || $Vendor == "ZJS" || $Vendor == "ZTO" )
					{
						$Location = "LEMO-BJ03";
					}
					elseif ($Vendor == "USPS" && substr($row["OrderNumber"], 0, 8) == "00417090")
					{
						$Location = "LEMO-BJ03";
					}
				}
			}
			else
			{
				$Status = 'InTransit';
				$Location = $row["ShippingTo"];
			}
			$queryUpdate = "UPDATE PSS_Item SET `Status`='".$Status."', Location = '".$Location."', Expense=(SELECT ROUND(SUM(CNYAmount),2) FROM PSS_Price WHERE CNYAmount<0 AND ItemID='".$ItemID."'), Revenue=(SELECT ROUND(SUM(CNYAmount),2) FROM PSS_Price WHERE CNYAmount>0 AND ItemID='".$ItemID."') WHERE ItemID=".$ItemID." LIMIT 1;";
			echo $queryUpdate;
			$mysqli->query($queryUpdate);
		}
		else
		{
			$queryPurchase = "SELECT OrderTime,ShippingTo FROM PSS_Price INNER JOIN PSS_POrder ON LinkID=POrderID WHERE Type = 'Purchase' AND PSS_Price.ItemID='".$ItemID."' ORDER BY PriceID DESC LIMIT 1;";
			$resultPurchase = $mysqli->query($queryPurchase);
			if ($row = $resultPurchase->fetch_array(MYSQLI_ASSOC))
			{
				$Status = 'Buy';
				$Location = $row["ShippingTo"];
				$queryUpdate = "UPDATE PSS_Item SET `Status`='".$Status."', Location = '".$Location."', Expense=(SELECT ROUND(SUM(CNYAmount),2) FROM PSS_Price WHERE CNYAmount<0 AND ItemID='".$ItemID."'), Revenue=(SELECT ROUND(SUM(CNYAmount),2) FROM PSS_Price WHERE CNYAmount>0 AND ItemID='".$ItemID."') WHERE ItemID=".$ItemID." LIMIT 1;";
				echo $queryUpdate;
				$mysqli->query($queryUpdate);
			}
	
		}
		$resultDelivery->close();
	}

}
$result->close();
$mysqli->close();

?>