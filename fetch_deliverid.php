<?php
if (isset($_GET["did"]))
{
	$deliverid = $_GET["did"];
	require("conn.php");
	include "appconf.php";
	include "TopSdk.php";

	date_default_timezone_set('Asia/Shanghai');
	$mysqli = new mysqli($mysql_server_name, $mysql_username, $mysql_password, $mysql_database);

	if (mysqli_connect_errno()) {
		printf("Database Connect failed: %s\n", mysqli_connect_error());
		exit();
	}

	$mysqli->query("SET NAMES UTF8;");
	$mysqli->query("SET time_zone = '+08:00';");

	$query = "SELECT * FROM TB_Item;";
	$result = $mysqli->query($query);

	$TaobaoItems = array();
	while ($row = $result->fetch_array(MYSQLI_ASSOC))
	{
		$TaobaoID = $row["ItemID"];
		$LegoID = $row["LegoID"];
		$TaobaoItems["$TaobaoID"] = $LegoID;
	}
	
	$query = "SELECT * FROM TMP_DeliverID_OrderID WHERE DeliverID = '".$deliverid."';";
	$result = $mysqli->query($query);
	if (mysqli_num_rows($result))
	{

		//实例化TopClient类
		$c = new TopClient;
		$c->appkey = $client_id;
		$c->secretKey = $client_secret;
		$sessionKey = $access_token;
		$reqTrades = new TradeFullinfoGetRequest;
		$reqTrades->setFields("buyer_nick,tid,sid,status,pay_time,credit_card_fee,buyer_nick,total_fee,payment,post_fee,orders,receiver_state,receiver_city,receiver_district,receiver_address,receiver_name,receiver_mobile,receiver_phone");

		$itemid = 1;
		while ($row = $result->fetch_array(MYSQLI_ASSOC))
		{
			$OrderID = $row["OrderID"];
			$reqTrades->setTid($OrderID);
			$respTrades = $c->execute($reqTrades, $sessionKey);
			$trade = $respTrades->trade;
			echo "<input type=\"hidden\" id=\"vendor\" value=\"".$row["Vendor"]."\">";
			echo "订单：<span id=\"orderid\">".$trade->sid."</span><br>";
			echo "时间：".$trade->pay_time."   状态:".$trade->status."<br>";
			if ($trade->receiver_state == "北京" || $trade->receiver_state == "天津" || $trade->receiver_state == "上海" || $trade->receiver_state == "重庆")
			{
				$strState = "";
			}
			else
			{
				$strState = $trade->receiver_state;
			}
			$strAddr = $trade->receiver_address;
			$strAddr = str_replace($trade->receiver_city, "", $strAddr);
			$strAddr = str_replace($trade->receiver_state, "", $strAddr);
			$strAddr = str_replace($trade->receiver_district, "", $strAddr);
			$strFullAddr = trim($strState.$trade->receiver_city.$trade->receiver_district.$strAddr."，".$trade->receiver_name."，".$trade->receiver_mobile."，".$trade->receiver_phone,"，");
			echo "买家：".$trade->buyer_nick."<br>";
			echo "地址：".$strFullAddr."<br>";
			
			$total_payment = floatval($trade->payment);
			$total_price = floatval($trade->total_fee);
			$total_post_fee = floatval($trade->post_fee);
			echo "总金额：¥<span id=\"total_payment\">".$total_payment."</span> = ¥<span id=\"total_price\">".$total_price."</span> + ¥<span id=\"total_post_fee\">".$total_post_fee."</span><br>";
			if ($trade->credit_card_fee > 0)
			{
				$total_credit_fee = round(floatval($trade->credit_card_fee)/100, 2);
			}
			else
			{
				$total_credit_fee = sprintf("%.2f", 0);
			}
			echo "手续费：¥<span id=\"total_credit_fee\">".$total_credit_fee."</span><br>";

			echo "<table><tr><th>ID</th><th>LEGOID</th><th>标价</th><th>付款</th><th>手续费</th><th>邮费</th><th>售价</th><th>标题</th></tr>";
			foreach ($trade->orders->order as $order)
			{
				$num_iid = $order->num_iid;
				$title = $order->title;
				$price = sprintf("%.2f", floatval($order->price));
				$payment = sprintf("%.2f", round(floatval($price/$total_price*$total_payment), 2));
				$credit_fee = sprintf("%.2f", round(floatval($payment/$total_payment*$total_credit_fee), 2));
				if (isset($TaobaoItems["$num_iid"]))
				{
					$LegoID = $TaobaoItems["$num_iid"];
					$HTMLTitle = "";
				}
				else
				{
					$LegoID = "";
					if (preg_match('/(\d{3,})/', $title, $matches))
					{
						$LegoID = $matches[1];
					}
					$HTMLTitle = "<span>".$title."</span>";
				}
				$num = intval($order->num);
				while($num>0)
				{
					echo "<tr><td><span id=\"item_id_".$itemid."\">".$itemid."</span></td><td><input type=\"text\" id=\"item_legoid_".$itemid."\" size=\"6\" value=\"".$LegoID."\" disabled></td><td><input type=\"text\" id=\"item_price_".$itemid."\" size=\"5\" value=\"".$price."\" disabled></td><td><input type=\"text\" id=\"item_payment_".$itemid."\" size=\"5\" value=\"".$payment."\" disabled></td><td><input type=\"text\" id=\"item_fee_".$itemid."\" size=\"3\" value=\"".$credit_fee."\" disabled></td><td><input type=\"text\" id=\"item_postfee_".$itemid."\" size=\"3\" disabled></td><td><input type=\"text\" id=\"item_rev_".$itemid++."\" size=\"6\" disabled></td><td>".$HTMLTitle."</td></tr>";
					$num--;
				}
			}
			echo "</table>";
		}
		echo "<input type=\"hidden\" id=\"itemnum\" value=\"".($itemid-1)."\">";

	}
	else
	{
		echo "无对应订单！";
	}

}
?>