<?php
header("Content-type: text/html; charset=utf-8");

require("conn.php");
include "appconf.php";
include "TopSdk.php";

$OrderID = $_POST["orderid"];
$Vendor = strtoupper($_POST["vendor"]);
$Tracknum = $_POST["tracknum"];
$Weight = $_POST["weight"];
$Action = $_GET["action"];


$ret = new stdClass();
$ret->{'OrderID'} = $OrderID;
$ret->{'Status'} = 1;
$ret->{'Message'} = "";

if (isset($OrderID) && isset($Action) && isset($Vendor) && isset($Tracknum))
{
	date_default_timezone_set('Asia/Shanghai');
	$mysqli = new mysqli($mysql_server_name, $mysql_username, $mysql_password, $mysql_database);

	if (mysqli_connect_errno()) {
		printf("Database Connect failed: %s\n", mysqli_connect_error());
		exit();
	}

	$mysqli->query("SET NAMES UTF8;");
	$mysqli->query("SET time_zone = '+08:00';");
	
	if ($Action == "remove")
	{
		$querycleanup = "";

		$query = "SELECT DeliveryID, PriceID FROM `PSS_Delivery` INNER JOIN PSS_Price ON DeliveryID = LinkID WHERE `OrderNumber` = '".$mysqli->real_escape_string($Tracknum)."' AND TYPE = 'Delivery';";
		$result = $mysqli->query($query);
		while ($row = $result->fetch_array(MYSQLI_ASSOC))
		{
			$querycleanup .= "DELETE FROM PSS_Delivery WHERE DeliveryID=".$mysqli->real_escape_string($row["DeliveryID"])." LIMIT 1;";
			$querycleanup .= "DELETE FROM PSS_Price WHERE PriceID=".$mysqli->real_escape_string($row["PriceID"])." LIMIT 1;";
		}
		
		$query = "SELECT SOrderID, PriceID, PSS_Item.ItemID AS ItemID, LegoID FROM  `PSS_SOrder` INNER JOIN PSS_Price ON SOrderID = LinkID INNER JOIN PSS_Item ON PSS_Price.ItemID = PSS_Item.ItemID WHERE `OrderNumber` = '".$mysqli->real_escape_string($OrderID)."' AND TYPE = 'Sold';";
		$result = $mysqli->query($query);
		while ($row = $result->fetch_array(MYSQLI_ASSOC))
		{
			$querycleanup .= "DELETE FROM PSS_SOrder WHERE SOrderID=".$mysqli->real_escape_string($row["SOrderID"])." LIMIT 1;";
			$querycleanup .= "DELETE FROM PSS_Price WHERE PriceID=".$mysqli->real_escape_string($row["PriceID"])." LIMIT 1;";
			$querycleanup .= "UPDATE PSS_Item SET Status='InStock' WHERE ItemID = '".$mysqli->real_escape_string($row["ItemID"])."' LIMIT 1; CALL Update_Item_Exp_Rev_Price(".$mysqli->real_escape_string($row["ItemID"]).");";
		}

		$mysqli->multi_query($querycleanup);
		while ($mysqli->next_result()) {;}
		$ret->{'Debug'} .= $querycleanup;
		$ret->{'Message'} = "删除: SOrder: $SOrderID->$PriceID, Delivery: $DeliveryID->$ShippingID, Item: $ItemID";
		$ret->{'Status'} = 0;

		echo json_encode($ret);
		exit;

	}
	elseif (isset($Weight))
	{
		$query = "SELECT * FROM TB_Item;";
		$result = $mysqli->query($query);

		$TaobaoItems = array();
		while ($row = $result->fetch_array(MYSQLI_ASSOC))
		{
			$TaobaoID = $row["ItemID"];
			$LegoID = $row["LegoID"];
			$TaobaoItems["$TaobaoID"] = $LegoID;
		}
	
		$query = "SELECT SOrderID FROM PSS_SOrder WHERE OrderNumber = '".$mysqli->real_escape_string($OrderID)."';";
		$result = $mysqli->query($query);
		if ($result->num_rows > 0)
		{
			$row = $result->fetch_row();
			$SOrderID = intval($row[0]);	
			$result->close();
			$ret->{'Message'} = "订单已经录入: $SOrderID";
			echo json_encode($ret);
			exit;
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
		
			if ($Action == "shipping")
			{
				$now = new DateTime('now');
				$ShippingTime = $now->format('Y-m-d H:i:s');
			}
			elseif ($Action == "record")
			{
				$ShippingTime = $trade->consign_time;
				/*
				$reqLogistics = new LogisticsTraceSearchRequest;
				$reqLogistics->setTid($trade->tid);
				$reqLogistics->setSellerNick("懒懒淑女");
				$respLogistics = $c->execute($reqLogistics);
				if($respLogistics->company_name == "顺丰速运")
				{
					$Vendor = "SFE";
				}
				elseif ($respLogistics->company_name == "中通速递")
				{
					$Vendor = "STO";
				}
				else
				{
					$Vendor = "Other";
				}
				$Tracknum = $respLogistics->out_sid;
				*/
			}
		
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

			$query = "INSERT IGNORE INTO `TMP_Track_Weight`(`Vendor`, `TrackNum`, `Weight`, `ShippingTo`, `ShippingDate`) VALUES ('".$mysqli->real_escape_string($Vendor)."', '".$mysqli->real_escape_string($Tracknum)."','".$mysqli->real_escape_string($Weight)."','".$mysqli->real_escape_string($trade->receiver_state)."','".$mysqli->real_escape_string($ShippingTime)."');";
			$result = $mysqli->query($query);


			if ($Vendor != "OTHER")
			{
				$query = "SELECT 1st,2nd FROM TB_ShippingFee WHERE Vendor='".$mysqli->real_escape_string($Vendor)."' AND State='".$mysqli->real_escape_string((string)$trade->receiver_state)."';";
				$result = $mysqli->query($query);

				if ($result->num_rows > 0)
				{
					$row = $result->fetch_row();
					$firstfee = floatval($row[0]);
					$secondfee = floatval($row[1]);
					$result->close();

					if ($Weight <= 1)
					{
						$Postfee = $firstfee;
					}
					else
					{
						$CeilWeight = floatval(ceil($Weight));
						if ($CeilWeight-$Weight >= 0.5)
						{
							$AccountWeight = $CeilWeight - 0.5;
						}
						else
						{
							$AccountWeight = $CeilWeight;
						}
						$Postfee = $firstfee+($AccountWeight-1)*$secondfee;
					}	
				}
				else
				{
					$ret->{'Message'} = "无法正确获得运费：Vendor:".$Vendor.", State:".(string)$trade->receiver_state;
					echo json_encode($ret);
					exit;
				}
			}
			else
			{
				$Postfee = floatval(0);
			}
		
			$TotalPayment = floatval($trade->payment);
			$TotalPrice = floatval($trade->total_fee);
			$PaidPostfee = floatval($trade->post_fee);
			$Items = array();

			foreach ($trade->orders->order as $order)
			{
				if ($order->outer_iid <> "")
				{
					$LegoID = (string)$order->outer_iid;
				}
				else
				{
					$num_iid = $order->num_iid;
					if (isset($TaobaoItems["$num_iid"]))
					{
						$LegoID = $TaobaoItems["$num_iid"];
					}
					else
					{
						$LegoID = "UNKNOWN";
					}
				}
				$ItemPrice = floatval(sprintf("%.2f", floatval($order->price)));
				$AccountPrice = floatval(sprintf("%.2f", round(floatval($ItemPrice/$TotalPrice*$TotalPayment), 2)));
				$AccountPostFee = floatval(sprintf("%.2f", round(floatval($ItemPrice/$TotalPrice*$Postfee), 2)));
				$ItemNum = intval($order->num);
			
				$query = "SELECT ItemID,Location FROM PSS_Item WHERE Status='InStock' AND Location LIKE 'LEMO-%%' AND LegoID='".$mysqli->real_escape_string($LegoID)."' ORDER BY Expense,ItemID LIMIT ".$mysqli->real_escape_string($ItemNum).";";
				$result = $mysqli->query($query);
				$Location = "";
				if ($result->num_rows == $ItemNum)
				{
					while ($row = $result->fetch_array(MYSQLI_ASSOC))
					{
						$ItemInfo = new stdclass();
						$ItemInfo->{'LegoID'} = $LegoID;
						$ItemInfo->{'ItemID'} = $row['ItemID'];
						$ItemInfo->{'Price'} = $AccountPrice;
						$ItemInfo->{'Postfee'} = $AccountPostFee;
						$Items[$row['ItemID']] = $ItemInfo;
						$Location = $row['Location'];
					}
				}
				else
				{
					$ret->{'Message'} = "库存商品不足:".$LegoID;
					echo json_encode($ret);
					exit;
				}

			}
		
			if (isset($OrderTime))
			{
				//更新数据库
				$query = "INSERT INTO PSS_SOrder (OrderNumber, OrderTime, Buyer, BuyerInfo) VALUES ('".$mysqli->real_escape_string($OrderID)."', '".$mysqli->real_escape_string($OrderTime)."', '".$mysqli->real_escape_string($Buyer)."', '".$mysqli->real_escape_string($BuyerInfo)."');";
				$mysqli->query($query);
				$SOrderID = $mysqli->insert_id;
				$query = "INSERT INTO PSS_Delivery (Vendor, OrderNumber, ShippingTime, ShippingFrom, ShippingTo, Ref, RefID, Weight) VALUES ('".$mysqli->real_escape_string($Vendor)."', '".$mysqli->real_escape_string($Tracknum)."', '".$mysqli->real_escape_string($ShippingTime)."', '".$mysqli->real_escape_string($Location)."', '".$mysqli->real_escape_string($ShippingTo)."', 'TAOBAO', '".$mysqli->real_escape_string($OrderID)."', '".$mysqli->real_escape_string($Weight)."');";
				$mysqli->query($query);
				$DeliveryID = $mysqli->insert_id;
				$ret->{'Message'} .= "SOrderID:".$SOrderID.", DeliveryID:".$DeliveryID."\r\n";

				foreach ($Items as $Item)
				{
					$query = "INSERT INTO PSS_Price (ItemID, Type, LinkID, Amount, Rate, CNYAmount) VALUES (".$mysqli->real_escape_string($Item->{'ItemID'}).", 'Sold', ".$mysqli->real_escape_string($SOrderID).", ".$mysqli->real_escape_string($Item->{'Price'}).", 1, ".$mysqli->real_escape_string($Item->{'Price'})."); ";
					$query .= "INSERT INTO PSS_Price (ItemID, Type, LinkID, Amount, Rate, CNYAmount) VALUES (".$mysqli->real_escape_string($Item->{'ItemID'}).", 'Delivery', ".$mysqli->real_escape_string($DeliveryID).", -".$mysqli->real_escape_string($Item->{'Postfee'}).", 1, -".$mysqli->real_escape_string($Item->{'Postfee'})."); ";
					$query .= "UPDATE PSS_Item SET Status='Sold', Expense=(SELECT ROUND(SUM(CNYAmount),2) FROM PSS_Price WHERE CNYAmount<0 AND ItemID='".$mysqli->real_escape_string($Item->{'ItemID'})."'), Revenue=(SELECT ROUND(SUM(CNYAmount),2) FROM PSS_Price WHERE CNYAmount>0 AND ItemID='".$mysqli->real_escape_string($Item->{'ItemID'})."') WHERE ItemID = '".$mysqli->real_escape_string($Item->{'ItemID'})."' LIMIT 1; ";
					$mysqli->multi_query($query);
					while ($mysqli->next_result()) {;}
					$ret->{'Debug'} .= $query;
					$ret->{'Message'} .= $Item->{'LegoID'}."(".$Item->{'ItemID'}.") 已售出。\r\n";
					$ret->{'Status'} = 0;
				}

			}
			if ($Action == "shipping")
			{
				//淘宝发货
				if ($Vendor == "SFE")
				{
					$Vendor = "SF";
				}
				if ($Vendor == "ZTO" || $Vendor == "SF")
				{
					$reqLogistic = new LogisticsOfflineSendRequest;
					$reqLogistic->setTid(intval($trade->tid));
					$reqLogistic->setOutSid($Tracknum);
					$reqLogistic->setCompanyCode($Vendor);
					$respLogistic = $c->execute($reqLogistic, $sessionKey);
				}
				$ret->{'Message'} .= "taobao订单".$trade->tid."已发货：".$Vendor.":".$Tracknum;
				$ret->{'Status'} = 0;

			}
		}
	}
}
else
{
	$ret->{'Message'} = "参数错误."; //.$_POST["orderid"].", ",$_POST["vendor"].", ".$_POST["tracknum"].", ".$_POST["weight"].", ".$_GET["act"]);
}
echo json_encode($ret);
?>