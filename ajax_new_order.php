<?php

function tz_convert($timestr, $timezone)
{
	switch($timezone)
	{
	case "-8":
		$datetime = new DateTime($timestr, new DateTimeZone('America/Los_Angeles') );
		break;
	case "-12":
		$datetime = new DateTime($timestr, new DateTimeZone('America/New_York') );
		break;
	default:
		$datetime = new DateTime($timestr, new DateTimeZone('Asia/Shanghai') );
		break;
	}
	$datetime->setTimezone(new DateTimeZone('Asia/Shanghai'));
	
	return date_format($datetime, 'Y-m-d H:i:s');
}

$action = $_GET["action"];
if ($action == "query_order")
{
	$orderid = $_GET["oid"];
	if (isset($orderid) && $orderid<>"")
	{
		require("conn.php");
		date_default_timezone_set('Asia/Shanghai');
		$mysqli = new mysqli($mysql_server_name, $mysql_username, $mysql_password, $mysql_database);

		if (mysqli_connect_errno()) {
			echo "无法连接到数据库服务器！";
			exit();
		}

		$mysqli->query("SET NAMES UTF8;");
		$mysqli->query("SET time_zone = '+08:00';");
		$query = "SELECT POrderID FROM PSS_POrder WHERE OrderNumber='".$mysqli->real_escape_string($orderid)."';";
		$result = $mysqli->query($query);
		if ($result->num_rows > 0)
		{
			$result->close();
			echo "系统已经存在该订单号！";
		}
		else
		{
			echo "OK";
		}
		$mysqli->close();
	}
	else
	{
		echo "OrderID为空！";
	}
}
elseif ($action == "new_order")
{
	$item_num = $_POST["item_num"];
	$Seller = $_POST["Seller"];
	$Buyer = $_POST["Buyer"];
	$Payby = $_POST["Payby"];
	$OrderTime = $_POST["OrderTime"];
	$Timezone = $_POST["Timezone"];
	$OrderNumber = $_POST["OrderNumber"];
	$ShippingTo = $_POST["ShippingTo"];
	$Memo = $_POST["Memo"];
	$Rate = floatval($_POST["Rate"]);
	$Vendor = $_POST["Vendor"];
	$ShippingOrderNumber = $_POST["ShippingOrderNumber"];
	$ShippingTime = $_POST["ShippingTime"];
	$ShippingTimezone = $_POST["ShippingTimezone"];
	$DeliveryTime = $_POST["DeliveryTime"];
	$DeliveryTimezone = $_POST["DeliveryTimezone"];

	require("conn.php");
	date_default_timezone_set('Asia/Shanghai');
	$mysqli = new mysqli($mysql_server_name, $mysql_username, $mysql_password, $mysql_database);

	if (mysqli_connect_errno()) {
		printf("Database Connect failed: %s\n", mysqli_connect_error());
		exit();
	}

	$mysqli->query("SET NAMES UTF8;");
	$mysqli->query("SET time_zone = '+08:00';");
	
	$OrderTime = tz_convert($OrderTime, $Timezone);
	$ShippingTime = tz_convert($ShippingTime, $ShippingTimezone);
	$DeliveryTime = tz_convert($DeliveryTime, $DeliveryTimezone);

	
	$query = "INSERT INTO PSS_POrder(Seller, Buyer, Payby, OrderNumber, OrderTime, ShippingTo, Memo) VALUES ('".$mysqli->real_escape_string($Seller)."','".$mysqli->real_escape_string($Buyer)."','".$mysqli->real_escape_string($Payby)."','".$mysqli->real_escape_string($OrderNumber)."','".$mysqli->real_escape_string($OrderTime)."','".$mysqli->real_escape_string($ShippingTo)."','".$mysqli->real_escape_string($Memo)."');";
	$result = $mysqli->query($query);
	$POrderID = $mysqli->insert_id;
	if ($_POST["shipping_method"] == "shippingnow")
	{
		$query = "INSERT INTO PSS_Delivery(Vendor, OrderNumber, ShippingTime, DeliveryTime, ShippingFrom, ShippingTo, Ref, RefID) VALUES ('".$mysqli->real_escape_string($Vendor)."','".$mysqli->real_escape_string($ShippingOrderNumber)."','".$mysqli->real_escape_string($ShippingTime)."','".$mysqli->real_escape_string($DeliveryTime)."','".$mysqli->real_escape_string($Seller)."','".$mysqli->real_escape_string($ShippingTo)."','".$mysqli->real_escape_string($Seller)."','".$mysqli->real_escape_string($OrderNumber)."');";
		$result = $mysqli->query($query);
		$DeliveryID = $mysqli->insert_id;
	}
	$total_num = 0;
	for ($i = 1; $i <= $item_num; $i++)
	{
		$qty = $_POST["item_".$i."_qty"];
		$legoid = $_POST["item_".$i."_legoid"];
		$price = floatval($_POST["item_".$i."_price"]);
		$cnyprice = round($price * $Rate, 2);
		for ($j = 1; $j <= $qty; $j++)
		{
			$query = "INSERT INTO PSS_Item(LegoID, POrderID, Status, Location, Expense) VALUES ('".$mysqli->real_escape_string($legoid)."','".$mysqli->real_escape_string($POrderID)."','Buy','".$mysqli->real_escape_string($Seller)."','".$mysqli->real_escape_string(-$cnyprice)."');";
			$result = $mysqli->query($query);
			$ItemID = $mysqli->insert_id;
			$query = "INSERT INTO PSS_Price(ItemID, Type, LinkID, Amount, Rate, CNYAmount) VALUES ('".$mysqli->real_escape_string($ItemID)."','Purchase','".$mysqli->real_escape_string($POrderID)."','".$mysqli->real_escape_string(-$price)."','".$mysqli->real_escape_string($Rate)."','".$mysqli->real_escape_string(-$cnyprice)."');";
			$result = $mysqli->query($query);
			if ($_POST["shipping_method"] == "shippingnow")
			{
				$query = "INSERT INTO PSS_Price(ItemID, Type, LinkID, Amount, Rate, CNYAmount) VALUES ('".$mysqli->real_escape_string($ItemID)."','Delivery','".$mysqli->real_escape_string($DeliveryID)."','0','1','0');";
				//echo $query;
				$result = $mysqli->query($query);
				$query = "UPDATE PSS_Item SET Status='InTransit', Location='".$mysqli->real_escape_string($ShippingTo)."', Expense=(SELECT ROUND(SUM(CNYAmount),2) FROM PSS_Price WHERE CNYAmount<0 AND ItemID='".$mysqli->real_escape_string($ItemID)."') WHERE ItemID='".$mysqli->real_escape_string($ItemID)."' LIMIT 1;";
				//echo $query;
				$result = $mysqli->query($query);
			}
			$total_num++;
		}
	}
	$mysqli->close();
	echo "订单：".$OrderNumber."(".$POrderID.")已经更新".$total_num."件物品。";
}

?>