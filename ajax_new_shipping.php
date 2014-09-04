<?php

$action = $_GET["action"];
if ($action == "list_order")
{
	require("conn.php");

	date_default_timezone_set('Asia/Shanghai');
	$mysqli = new mysqli($mysql_server_name, $mysql_username, $mysql_password, $mysql_database);

	if (mysqli_connect_errno()) {
		printf("Database Connect failed: %s\n", mysqli_connect_error());
		exit();
	}

	$mysqli->query("SET NAMES UTF8;");
	$mysqli->query("SET time_zone = '+08:00';");


	$LegoID = $_GET["legoid"];
	$OrderID = $_GET["orderid"];
	if (isset($LegoID))
	{
		$query = "SELECT * FROM PSS_Item WHERE POrderID IN (SELECT POrderID FROM PSS_Item WHERE Status = 'Buy' AND LegoID = '".$mysqli->real_escape_string($LegoID)."');";
	}
	elseif (isset($OrderID))
	{
		$query = "SELECT ItemID,LegoID,PSS_Item.POrderID AS POrderID FROM PSS_Item INNER JOIN PSS_POrder ON PSS_Item.POrderID = PSS_POrder.POrderID WHERE OrderNumber = '".$mysqli->real_escape_string($OrderID)."';";
	}
	else
	{
		$query = "SELECT * FROM PSS_Item WHERE Status = 'Buy';";
		//$query = "SELECT * FROM PSS_Item WHERE Status = 'InStock' AND Location='DRCT-CA01';";
	}
	$result = $mysqli->query($query);

	$Items = array();
	$OrderItems = array();
	while ($row = $result->fetch_array(MYSQLI_ASSOC))
	{
		$ItemID = $row["ItemID"];
		$LegoID = $row["LegoID"];
		$OrderID = $row["POrderID"];
		$Items["$ItemID"] = $LegoID;
		if (array_key_exists($OrderID, $OrderItems))
		{
			$OrderItems["$OrderID"] = $OrderItems["$OrderID"].",$ItemID";
		}
		else
		{
			$OrderItems["$OrderID"] = "$ItemID";
		}
	}
	$strOrderIDs = "";
	foreach(array_keys($OrderItems) as $POID)
	{
		$strOrderIDs = $strOrderIDs.$POID.",";
	}
	$strOrderIDs = trim($strOrderIDs, ",");

	$query = "SELECT * FROM PSS_POrder WHERE POrderID IN (".$strOrderIDs.") ORDER BY OrderTime DESC;";
	$result = $mysqli->query($query);
	while ($row = $result->fetch_array(MYSQLI_ASSOC))
	{
		$OrderID = $row["POrderID"];
		echo "<a href=\"javascript:void(0);\" class=\"expend_icon\" id=\"exp_".$OrderID."\" onclick=\"show_order(".$OrderID.");\">+</a><input name=\"Order_".$OrderID."\" type=\"checkbox\" value=\"".$OrderItems["$OrderID"]."\" onchange=\"multi_checkbox(".$OrderID.");\"><span onclick=\"show_order(".$OrderID.");\"><span id=\"Seller_".$OrderID."\">".$row["Seller"]."</span>:<span id=\"OrderID_".$OrderID."\">".$row["OrderNumber"]."</span></span><br/>";
		echo "<div id=\"div_order_".$OrderID."\" class=\"order_div\"><ul>";
		foreach (explode(",", $OrderItems["$OrderID"]) as $ItemID)
		{
			echo "<li><input name=\"Item_".$ItemID."\" type=\"checkbox\" value=\"".$ItemID."\" onchange=\"all_selected();\">".$Items["$ItemID"]."</li>";
		}
		echo "</ul></div>";
	}


}
elseif ($action == "query_items")
{
	$itemsID = $_POST["ids"];

	if (isset($itemsID) && $itemsID <> "")
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
		$query = "SELECT PSS_Item.ItemID,LegoID,Amount,Rate,CNYAmount FROM PSS_Price INNER JOIN PSS_Item ON PSS_Price.ItemID = PSS_Item.ItemID WHERE Type='Purchase' AND PSS_Item.ItemID IN (".$mysqli->real_escape_string($itemsID).") ORDER BY ItemID;";
		$result = $mysqli->query($query);
	
		while ($row = $result->fetch_array(MYSQLI_ASSOC))
		{
			echo "<tr><td id=\"item_".$row["ItemID"]."\">".$row["LegoID"]."</td><td><input type=\"text\" size=\"7\" name=\"price_".$row["ItemID"]."\" value=\"".$row["Amount"]."\" disabled/></td><td><input type=\"text\" size=\"7\" name=\"shippingfee_".$row["ItemID"]."\" /></td></tr>";

		}
	
		$mysqli->close();
	}
}
elseif ($action == "new_shipping")
{
	$itemsStr = $_POST["itemids"];
	$itemIDs = explode(",", $itemsStr);
	$orderNumber = $_POST["ShippingOrderNumber"];
	$shippingFrom = $_POST["ShippingFrom"];
	$shippingTo = $_POST["ShippingTo"];
	$vendor = $_POST["Vendor"];
	$rate = floatval($_POST["Rate"]);
	$shippingTime = $_POST["ShippingTime"];
	$deliveryTime = $_POST["DeliveryTime"];
	$refID = $_POST["RefID"];
	require("conn.php");
	date_default_timezone_set('Asia/Shanghai');
	$mysqli = new mysqli($mysql_server_name, $mysql_username, $mysql_password, $mysql_database);

	if (mysqli_connect_errno()) {
		printf("Database Connect failed: %s\n", mysqli_connect_error());
		exit();
	}

	$mysqli->query("SET NAMES UTF8;");
	$mysqli->query("SET time_zone = '+08:00';");
	$query = "INSERT INTO PSS_Delivery(Vendor, OrderNumber, ShippingTime, DeliveryTime, ShippingFrom, ShippingTo, Ref, RefID) VALUES ('".$mysqli->real_escape_string($vendor)."','".$mysqli->real_escape_string($orderNumber)."','".$mysqli->real_escape_string($shippingTime)."','".$mysqli->real_escape_string($deliveryTime)."','".$mysqli->real_escape_string($shippingFrom)."','".$mysqli->real_escape_string($shippingTo)."','".$mysqli->real_escape_string($shippingFrom)."','".$mysqli->real_escape_string($refID)."');";
	//echo $query;
	$result = $mysqli->query($query);
	$DeliveryID = $mysqli->insert_id;
	//echo $DeliveryID;
	foreach($itemIDs as $itemID) 
	{
		$item_fee = $_POST["shippingfee_".$itemID];
		$cnyprice = round($item_fee*$rate, 2);
		$query = "INSERT INTO PSS_Price(ItemID, Type, LinkID, Amount, Rate, CNYAmount) VALUES ('".$mysqli->real_escape_string($itemID)."','Delivery','".$mysqli->real_escape_string($DeliveryID)."','".$mysqli->real_escape_string($item_fee)."','".$mysqli->real_escape_string($rate)."','".$mysqli->real_escape_string($cnyprice)."');";
		//echo $query;
		$result = $mysqli->query($query);
		$query = "UPDATE PSS_Item SET Status='InTransit', Location='".$mysqli->real_escape_string($shippingTo)."', Expense=(SELECT ROUND(SUM(CNYAmount),2) FROM PSS_Price WHERE CNYAmount<0 AND ItemID='".$mysqli->real_escape_string($itemID)."') WHERE ItemID='".$mysqli->real_escape_string($itemID)."' LIMIT 1;";
		//echo $query;
		$result = $mysqli->query($query);
	}
	echo $vendor.":".$orderNumber." 已发货!";
}
elseif ($action == "query_order")
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
		$query = "SELECT DeliveryID FROM PSS_Delivery WHERE OrderNumber='".$mysqli->real_escape_string($orderid)."';";
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
		echo "OrderNumber为空！";
	}
}
?>