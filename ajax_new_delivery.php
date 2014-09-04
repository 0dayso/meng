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


	$Type = $_GET["type"];
	if ($Type == "Transit")
	{
		//$query = "SELECT PSS_Item.ItemID AS ItemID,LegoID,LinkID,TTID FROM PSS_Delivery INNER JOIN PSS_Price ON PSS_Delivery.DeliveryID = PSS_Price.LinkID INNER JOIN PSS_Item ON PSS_Price.ItemID=PSS_Item.ItemID LEFT JOIN Tiantian_Delivery ON PSS_Delivery.DeliveryID = Tiantian_Delivery.DeliveryID WHERE ShippingTo NOT LIKE 'LEMO-%%' AND PSS_Price.Type = 'Delivery' AND PSS_Item.Status='InTransit';";

		$query = "SELECT PSS_Item.ItemID AS ItemID,LegoID,LinkID,TTID FROM PSS_Delivery INNER JOIN PSS_Price ON PSS_Delivery.DeliveryID = PSS_Price.LinkID INNER JOIN PSS_Item ON PSS_Price.ItemID=PSS_Item.ItemID  LEFT JOIN Tiantian_Delivery ON PSS_Delivery.DeliveryID = Tiantian_Delivery.DeliveryID WHERE Location NOT LIKE 'LEMO-%%' AND PSS_Price.Type = 'Delivery' AND PSS_Item.Status='InTransit';";
	}
	elseif ($Type == "Delivery")
	{
		$query = "SELECT PSS_Item.ItemID AS ItemID,LegoID,LinkID FROM PSS_Delivery INNER JOIN PSS_Price ON PSS_Delivery.DeliveryID = PSS_Price.LinkID INNER JOIN PSS_Item ON PSS_Price.ItemID=PSS_Item.ItemID WHERE ShippingTo LIKE 'LEMO-%%' AND PSS_Price.Type = 'Delivery' AND PSS_Item.Status='InTransit';";
	}
	else
	{
		/*
		$query = "SELECT ItemID,LegoID,LinkID FROM PSS_Item INNER JOIN PSS_Delivery ON PSS_Item.LinkID = PSS_Delivery.DeliveryID WHERE Status = 'InTransit' AND Location NOT LIKE 'LEMO%%' AND OrderNumber = '".$mysqli->real_escape_string($OrderID)."';";
		$query = "SELECT ItemID,LegoID,LinkID FROM PSS_Item WHERE DeliveryID IN (SELECT POrderID FROM PSS_Item WHERE Status = 'InTransit' AND Location NOT LIKE 'LEMO%%' AND LegoID = '".$mysqli->real_escape_string($LegoID)."');";
		SELECT PSS_Item.ItemID AS ItemID,LegoID,LinkID FROM PSS_Item INNER JOIN PSS_Price ON PSS_Item.ItemID = PSS_Price.ItemID WHERE Status = 'InTransit' AND Location NOT LIKE 'LEMO%%' AND Type = 'Delivery';*/
	}
	$result = $mysqli->query($query);

	$Items = array();
	$OrderItems = array();
	$OrderTTIDs = array();
	while ($row = $result->fetch_array(MYSQLI_ASSOC))
	{
		$ItemID = $row["ItemID"];
		$LegoID = $row["LegoID"];
		$OrderID = $row["LinkID"];
		$Items["$ItemID"] = $LegoID;
		if (array_key_exists($OrderID, $OrderItems))
		{
			$OrderItems["$OrderID"] = $OrderItems["$OrderID"].",$ItemID";
		}
		else
		{
			$OrderItems["$OrderID"] = "$ItemID";
		}
		$OrderTTIDs["$OrderID"] = $row["TTID"];
	}
	$strOrderIDs = "";
	foreach(array_keys($OrderItems) as $POID)
	{
		$strOrderIDs = $strOrderIDs.$POID.",";
	}
	$strOrderIDs = trim($strOrderIDs, ",");
	$query = "SELECT * FROM PSS_Delivery WHERE DeliveryID IN (".$strOrderIDs.") ORDER BY ShippingTime DESC;";
	$result = $mysqli->query($query);
	echo "<table>\r\n\t<tr><th>快递公司</th><th>目的地</th><th>运单号</th><th width=\"400px\">内容</th><th width=\"150px\">发货时间</th><th width=\"150px\">到货时间</th><th width=\"90px\">操作</th><th>TTID</th></tr>\r\n";
	while ($row = $result->fetch_array(MYSQLI_ASSOC))
	{
		$OrderID = $row["DeliveryID"];
		$arrItems = array();
		foreach (explode(",", $OrderItems["$OrderID"]) as $ItemID)
		{
			$LegoID = $Items["$ItemID"];
			if (array_key_exists($LegoID, $arrItems))
			{
				$arrItems["$LegoID"] = intval($arrItems["$LegoID"]) + 1;
			}
			else
			{
				$arrItems["$LegoID"] = intval(1);
			}
		}
		$strItems = "";
		ksort($arrItems);
		foreach ($arrItems as $LegoID => $count)
		{
			$strItems = $strItems.$LegoID."*".$count.", ";
		}
		$strItems = trim($strItems, ", ");
		if ($row["DeliveryTime"] == "0000-00-00 00:00:00")
		{
			$btnDeliver = "disabled";
		}
		else
		{
			$btnDeliver = "";
		}
		if (isset($OrderTTIDs[$OrderID]))
		{
			$TTIDstr = $OrderTTIDs[$OrderID];
		}
		else
		{
			$TTIDstr = "<a href=\"add_tiantian.php?did=".$OrderID."\">TT预报</a>";
		}
		echo "\t<tr id=\"row_".$OrderID."\"><td><span id=\"vendor_".$OrderID."\">".$row["Vendor"]."</span></td><td><div id=\"ordernumber_".$OrderID."\" ondblclick=\"clickon_ordernum(".$OrderID.",'".$row["OrderNumber"]."');\">".$row["OrderNumber"]."</div></td><td>".$row["ShippingTo"]."</td><td><span id=\"detail_".$OrderID."\">".$strItems."</span></td><td><span id=\"shipping_".$OrderID."\">".$row["ShippingTime"]."</span></td><td><span id=\"delivery_".$OrderID."\">".$row["DeliveryTime"]."</span></td><td><input type=\"button\" id=\"btn_query_".$OrderID."\" value=\"查单\" onclick=\"query_time('".$OrderID."')\"><input type=\"button\" id=\"btn_delivery_".$OrderID."\" value=\"到货\" ondblclick=\"unlock_update_time('".$OrderID."');\" onclick=\"update_time('".$OrderID."','".$OrderItems["$OrderID"]."')\" ".$btnDeliver."/></td><td>".$TTIDstr."</td></tr>\r\n";

	}
	echo "</table>\r\n";
}
elseif ($action == "update_time")
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

	$DeliveryID = $_POST["oid"];
	$ItemIDs = $_POST["items"];
	$ShippingTime = $_POST["shippingtime"];
	$DeliveryTime = $_POST["deliverytime"];
	$Vender = $_POST["vender"];

	$queryTime = "";
	if (isset($ShippingTime))
	{
		$queryTime = $queryTime."ShippingTime='".$mysqli->real_escape_string($ShippingTime)."', ";
	}
	if (isset($DeliveryTime))
	{
		$queryTime = $queryTime."DeliveryTime='".$mysqli->real_escape_string($DeliveryTime)."' ";
	}
	
	$query = "UPDATE PSS_Delivery SET ".$queryTime." WHERE DeliveryID=".$mysqli->real_escape_string($DeliveryID).";";

	$result = $mysqli->query($query);

	$query = "UPDATE PSS_Item SET Status='InStock' WHERE ItemID IN (".$mysqli->real_escape_string($ItemIDs).");";

	$result = $mysqli->query($query);
	echo "OK";
	$mysqli->close();

}
elseif ($action == "update_ordernum")
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
	$DeliveryID = $_POST["oid"];
	$OrderNumber = $_POST["onum"];
	
	$query = "UPDATE PSS_Delivery SET OrderNumber='".$mysqli->real_escape_string($OrderNumber)."' WHERE DeliveryID='".$mysqli->real_escape_string($DeliveryID)."';";
	$result = $mysqli->query($query);
	echo "OK";
	$mysqli->close();

}
?>