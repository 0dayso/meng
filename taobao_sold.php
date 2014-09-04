<?php
header("Content-type: text/html; charset=utf-8");

?>
<html>
<head>
  <title>乐乐萌10号</title>
  <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
  <link rel="stylesheet" href="table.css">
</head>
<body style="margin-left:auto;margin-right:auto">


</body>

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

$query = "SELECT * FROM TB_Item;";
$result = $mysqli->query($query);

$TaobaoItems = array();
while ($row = $result->fetch_array(MYSQLI_ASSOC))
{
	$TaobaoID = $row["ItemID"];
	$LegoID = $row["LegoID"];
	$TaobaoItems["$TaobaoID"] = $LegoID;
}
	
include "appconf.php";
include "TopSdk.php";

//实例化TopClient类
$c = new TopClient;
$c->appkey = $client_id;
$c->secretKey = $client_secret;
$sessionKey = $access_token;
echo $client_id."</br>";
echo $client_secret."</br>";
echo $sessionKey."</br>";

$reqTrades = new TradesSoldGetRequest;
$reqTrades->setFields("buyer_nick,tid,sid,status,pay_time,credit_card_fee,buyer_nick,total_fee,payment,post_fee,orders,receiver_state,receiver_city,receiver_district,receiver_address,receiver_name,receiver_mobile,receiver_phone");
$reqTrades->setStatus("WAIT_BUYER_CONFIRM_GOODS");
//$reqTrades->setStatus("WAIT_SELLER_SEND_GOODS");
$reqTrades->setPageSize(40);
$reqTrades->setUseHasNext("true");
$reqTrades->setIsAcookie("false");
$pagenum = 1;

while ($pagenum > 0)
{
	$reqTrades->setPageNo($pagenum);
	$respTrades = $c->execute($reqTrades, $sessionKey);

	if ($respTrades->has_next == "true")
	{
		$pagenum++;
	}
	else
	{
		$pagenum = 0;
	}

	foreach ($respTrades->trades->trade as $trade)
	{
	?>
	<table>
	  <tr>
		<td>订单：</td><td><img src="img_bc.php?bc=<?php echo $trade->sid; ?>"></td><td>状态：</td><td><?php echo $trade->status; ?></td><td>付款时间：</td><td><?php echo $trade->pay_time; ?></td>
	  </tr>
	</table>
	<?php
		echo "订单：<a href=\"http://trade.taobao.com/trade/detail/trade_item_detail.htm?bizOrderId=".$trade->sid."\">".$trade->sid."</a><br>";
		echo "总金额：¥".$trade->payment." = ¥".$trade->total_fee." + ¥".$trade->post_fee."<br>";
		if ($trade->credit_card_fee > 0)
		{
			$credit_fee = round(floatval($trade->credit_card_fee)/100, 2);
		}
		else
		{
			$credit_fee = sprintf("%.2f", 0);
		}
		echo "手续费：¥".$credit_fee."<br>";
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

		foreach ($trade->orders->order as $order)
		{
			$num_iid = $order->num_iid;
			$title = $order->title;
			$num = $order->num;
			$price = $order->price;
			if (isset($TaobaoItems["$num_iid"]))
			{
				echo $num." * ".$TaobaoItems["$num_iid"]." = ¥".$price."<br>";
			}
			else
			{
				echo $num." * ".$title."<input type=text /> = ¥".$price."<br>";
			}
		}
	
		$reqLogistics = new LogisticsTraceSearchRequest;
		$reqLogistics->setTid($trade->tid);
		$reqLogistics->setSellerNick("懒懒淑女");
		$respLogistics = $c->execute($reqLogistics);
		if($respLogistics->company_name == "顺丰速运")
		{
			$vendor = "SFE";
		}
		elseif ($respLogistics->company_name == "中通速递")
		{
			$vendor = "STO";
		}
		else
		{
			$vendor = "Other";
		}
		
		if (isset($respLogistics->out_sid))
		{
			echo "物流信息：".$vendor." ".$respLogistics->out_sid."<br>";
			$query = "INSERT IGNORE INTO TMP_DeliverID_OrderID(`Vendor`, `DeliverID`, `OrderID`, `OrderTime`) VALUES ('".$vendor."','".$respLogistics->out_sid."','".$trade->sid."','".$trade->pay_time."')";
			$result = $mysqli->query($query);
		}
		echo "物流状态：".$respLogistics->status."<br>";
		foreach ($respLogistics->trace_list->transit_step_info as $step)
		{
			echo "  ".$step->status_time." ".$step->status_desc."<br>";
		}
	
		echo "<br>";
	}
}
?>
</html>
