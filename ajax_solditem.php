<?php
require("conn.php");
include "appconf.php";
include "TopSdk.php";

$OrderID = $_POST["orderid"];
$Vendor = $_POST["vendor"];
$DeliverID = $_POST["deliverid"];
$ItemNum = $_POST["itemnum"];

if (isset($OrderID))
{
	date_default_timezone_set('Asia/Shanghai');
	$mysqli = new mysqli($mysql_server_name, $mysql_username, $mysql_password, $mysql_database);

	if (mysqli_connect_errno()) {
		printf("Database Connect failed: %s\n", mysqli_connect_error());
		exit();
	}

	$mysqli->query("SET NAMES UTF8;");
	$mysqli->query("SET time_zone = '+08:00';");
	
	$query = "SELECT SOrderID FROM PSS_SOrder WHERE OrderNumber = '".$OrderID."';";
	$result = $mysqli->query($query);
	if ($result->num_rows > 0)
	{
		$row = $result->fetch_row();
		$SOrderID = intval($row[0]);	
		$result->close();
	}
	else
	{
		$c = new TopClient;
		$c->appkey = $client_id;
		$c->secretKey = $client_secret;
		$sessionKey = $access_token;
		$reqTrades = new TradeFullinfoGetRequest;
		$reqTrades->setFields("buyer_nick,tid,sid,status,pay_time,consign_time,credit_card_fee,buyer_nick,total_fee,payment,post_fee,orders,receiver_state,receiver_city,receiver_district,receiver_address,receiver_name,receiver_mobile,receiver_phone");
		$reqTrades->setTid($OrderID);
		$respTrades = $c->execute($reqTrades, $sessionKey);
		$trade = $respTrades->trade;
		$OrderTime = $trade->pay_time;
		$ShippingTime = $trade->consign_time;
		if ($trade->receiver_state == "北京" || $trade->receiver_state == "天津" || $trade->receiver_state == "上海" || $trade->receiver_state == "重庆")
		{
			$strState = "";
			$ShippingTo = $trade->receiver_city.$trade->receiver_district;
		}
		else
		{
			$strState = $trade->receiver_state;
			$ShippingTo = $trade->receiver_state.$trade->receiver_city;
		}
		$strAddr = $trade->receiver_address;
		$strAddr = str_replace($trade->receiver_city, "", $strAddr);
		$strAddr = str_replace($trade->receiver_state, "", $strAddr);
		$strAddr = str_replace($trade->receiver_district, "", $strAddr);
		$BuyerInfo = trim($strState.$trade->receiver_city.$trade->receiver_district.$strAddr.",".$trade->receiver_name.",".$trade->receiver_mobile.",".$trade->receiver_phone,",");
		$Buyer = $trade->buyer_nick;

		//$trade->status;
		//$trade->credit_card_fee;
		if (isset($OrderTime))
		{
			$query = "INSERT INTO PSS_SOrder (OrderNumber, OrderTime, Buyer, BuyerInfo) VALUES ('".$mysqli->real_escape_string($OrderID)."', '".$mysqli->real_escape_string($OrderTime)."', '".$mysqli->real_escape_string($Buyer)."', '".$mysqli->real_escape_string($BuyerInfo)."');";
			$mysqli->query($query);
			$SOrderID = $mysqli->insert_id;
			$query = "UPDATE TMP_DeliverID_OrderID SET Imported=1 WHERE DeliverID='".$mysqli->real_escape_string($DeliverID)."' AND OrderID='".$mysqli->real_escape_string($OrderID)."' LIMIT 1;";
			$mysqli->query($query);
		}
	}

	$query = "SELECT DeliveryID FROM PSS_Delivery WHERE OrderNumber = '".$DeliverID."' LIMIT 1;";
	$result = $mysqli->query($query);
	if ($result->num_rows > 0)
	{
		$row = $result->fetch_row();
		$DeliveryID = intval($row[0]);	
		$result->close();
	}
	else
	{
		if (isset($DeliverID) && isset($Vendor))
		{
			if (!isset($ShippingTime) || !isset($ShippingTo))
			{
				$c = new TopClient;
				$c->appkey = $client_id;
				$c->secretKey = $client_secret;
				$sessionKey = $access_token;
				$reqTrades = new TradeFullinfoGetRequest;
				$reqTrades->setFields("consign_time,receiver_state,receiver_city,receiver_district");
				$reqTrades->setTid($OrderID);
				$respTrades = $c->execute($reqTrades, $sessionKey);
				$trade = $respTrades->trade;
				$OrderTime = $trade->pay_time;
				$ShippingTime = $trade->consign_time;
				if ($trade->receiver_state == "北京" || $trade->receiver_state == "天津" || $trade->receiver_state == "上海" || $trade->receiver_state == "重庆")
				{
					$strState = "";
					$ShippingTo = $trade->receiver_city.$trade->receiver_district;
				}
				else
				{
					$strState = $trade->receiver_state;
					$ShippingTo = $trade->receiver_state.$trade->receiver_city;
				}
			}
			$query = "INSERT INTO PSS_Delivery (Vendor, OrderNumber, ShippingTime, ShippingFrom, ShippingTo, Ref, RefID) VALUES ('".$mysqli->real_escape_string($Vendor)."', '".$mysqli->real_escape_string($DeliverID)."', '".$mysqli->real_escape_string($ShippingTime)."', 'LEMO_BJ01', '".$mysqli->real_escape_string($ShippingTo)."', 'TAOBAO', '".$mysqli->real_escape_string($OrderID)."');";
			$mysqli->query($query);
			$DeliveryID = $mysqli->insert_id;
		}
	}

	$query = "SELECT LegoID FROM PSS_Price INNER JOIN PSS_Item ON PSS_Price.ItemID = PSS_Item.ItemID WHERE PSS_Price.Type='Delivery' AND PSS_Price.LinkID = '".$mysqli->real_escape_string($DeliveryID)."';";
	$result = $mysqli->query($query);
	if ($result->num_rows > 0)
	{
		die("该运单已有".$result->num_rows."条对应纪录！");
	}	
	
	for ($i = 1; $i <= $ItemNum; $i++)
	{
    	$LegoID = $_POST["legoid_".$i];
    	$Payment = floatval($_POST["payment_".$i]);
    	$Fee = floatval($_POST["fee_".$i]);
    	$Postfee = floatval($_POST["postfee_".$i]);
    	
    	$query = "SELECT ItemID FROM PSS_Item WHERE Status='InStock' AND LegoID='".$mysqli->real_escape_string($LegoID)."' ORDER BY Expense	DESC LIMIT 1;";
		$result = $mysqli->query($query);
		if ($result->num_rows > 0)
		{
			$row = $result->fetch_row();
			$ItemID = intval($row[0]);	
			$result->close();
			
			$query = "INSERT INTO PSS_Price (ItemID, Type, LinkID, Amount, Rate, CNYAmount) VALUES ('".$mysqli->real_escape_string($ItemID)."', 'Sold', '".$mysqli->real_escape_string($SOrderID)."', '".$mysqli->real_escape_string($Payment)."', '1', '".$mysqli->real_escape_string($Payment)."'); ";
    		$query = $query . "INSERT INTO PSS_Price (ItemID, Type, LinkID, Amount, Rate, CNYAmount) VALUES ('".$mysqli->real_escape_string($ItemID)."', 'Delivery', '".$mysqli->real_escape_string($DeliveryID)."', '".$mysqli->real_escape_string($Postfee)."', '1', '".$mysqli->real_escape_string($Postfee)."'); ";
    		
    		if ($Fee > 0)
    		{
    			$query = $query . "INSERT INTO PSS_Price (ItemID, Type, LinkID, Amount, Rate, CNYAmount) VALUES ('".$mysqli->real_escape_string($ItemID)."', 'Commission', '".$mysqli->real_escape_string($SOrderID)."', '".$mysqli->real_escape_string($Fee)."', '1', '".$mysqli->real_escape_string($Fee)."'); ";
    		}
    		$query = $query . "UPDATE PSS_Item SET Status='Sold' WHERE ItemID = '".$mysqli->real_escape_string($ItemID)."' LIMIT 1; ";
    		$mysqli->multi_query($query);
    		echo "成功售出".$LegoID;
		}
		else
		{
			echo $LegoID."无剩余库存！";
		}
	}
}
?>
