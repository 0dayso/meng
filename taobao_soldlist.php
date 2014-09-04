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
<table>
  <tr>
  	<td>订单：</td><td><img src="img_bc.php?bc=359673485756857"></td><td></td><td></td><td></td>
  </tr>
</table>

</body>

<?php
include "TopSdk.php";

//实例化TopClient类
$c = new TopClient;
$c->appkey = "21532512";
$c->secretKey = "165fb84848c9eca8517531d89d408eea";
$sessionKey = "6101b26a2d16be97d19db8b43f97e2930619ae62e5dd32d12352442"; 

$reqTrades = new TradesSoldGetRequest;
$reqTrades->setFields("tid,sid,status,pay_time,buyer_nick,total_fee,payment,post_fee,receiver_state,receiver_city,receiver_district,receiver_address,receiver_name,receiver_mobile,receiver_phone");
//$reqTrades->setStatus("WAIT_BUYER_CONFIRM_GOODS");
$reqTrades->setStatus("WAIT_SELLER_SEND_GOODS");
$reqTrades->setPageNo(1);
$reqTrades->setPageSize(40);
$reqTrades->setUseHasNext("true");
$reqTrades->setIsAcookie("false");
$respTrades = $c->execute($reqTrades, $sessionKey);

echo count($respTrades->trades->trade);
foreach ($respTrades->trades->trade as $trade)
{
	echo "订单：".$trade->sid."    状态：".$trade->status."    付款时间：".$trade->pay_time."<br>";
	echo "总金额：¥".$trade->payment." = ¥".$trade->total_fee." + ¥".$trade->post_fee."<br>";
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
	echo "地址：".$strFullAddr."<br>";
	/* $reqLogistics = new LogisticsTraceSearchRequest;
    $reqLogistics->setTid($trade->tid);
    $reqLogistics->setSellerNick("懒懒淑女");
    $respLogistics = $c->execute($reqLogistics);
    echo "物流信息：".$respLogistics->company_name." ".$respLogistics->out_sid."<br>";
    //echo "物流状态：".$respLogistics->status."<br>";
    foreach ($respLogistics->trace_list->transit_step_info as $step)
    {
    	echo "  ".$step->status_time." ".$step->status_desc."<br>";
    }
    */
    echo "<br>";
}
?>
</html>
