<?php
header("Content-type: text/html; charset=utf-8");

include "appconf.php";
include "TopSdk.php";
require("conn.php");

$OrderInfo = new stdClass();
$OrderInfo->{'Status'} = 1;
$OrderInfo->{'Message'} = "";

if (isset($_GET['tid']))
{
	$tid = intval($_GET['tid']);

	//实例化TopClient类
	$c = new TopClient;
	$c->appkey = $client_id;
	$c->secretKey = $client_secret;
	$sessionKey = $access_token;

	$reqTrade = new TradeFullinfoGetRequest;
	$reqTrade->setFields("sid,pay_time,consign_time,received_payment,receiver_state,orders");
	$reqTrade->setTid($tid);
	$respTrade = $c->execute($reqTrade, $sessionKey);

	$trade = $respTrade->trade;



	$OrderInfo->{'OrderTime'} = (string)$trade->pay_time;
	$OrderInfo->{'OrderNumber'} = (string)$trade->sid;
	$OrderInfo->{'ShippingTime'} = (string)$trade->consign_time;
	$OrderInfo->{'OrderTotalPayment'} = floatval($trade->received_payment);
	$OrderInfo->{'ShippingTo'} = (string)$trade->receiver_state;
	
	$arrItems = array();
	foreach ($trade->orders->order as $order)
	{
		if ($order->outer_iid <> "")
		{
			$legoid = (string)$order->outer_iid;
		}
		else
		{
			$num_iid = $order->num_iid;
			if (isset($TaobaoItems["$num_iid"]))
			{
				$legoid = $TaobaoItems["$num_iid"];
			}
			else
			{
				$legoid = "UNKNOWN";
			}
		}

		$iteminfo = new stdClass();
		$iteminfo->{'Number'} = intval($order->num);
		$iteminfo->{'Legoid'} = $legoid;
        $iteminfo->{'Price'} = floatval($order->price);
		array_push($arrItems, $iteminfo);
	}
	$OrderInfo->{'Items'} = $arrItems;

	$reqLogistics = new LogisticsTraceSearchRequest;
	$reqLogistics->setTid($trade->sid);
	$reqLogistics->setSellerNick("懒懒淑女");
	$respLogistics = $c->execute($reqLogistics);

	if($respLogistics->company_name == "顺丰速运")
	{
		$OrderInfo->{'Vendor'} = "SFE";
	}
	elseif ($respLogistics->company_name == "中通快递")
	{
		$OrderInfo->{'Vendor'} = "STO";
	}
	else
	{
		$OrderInfo->{'Vendor'} = "SELF";
	}

	if (isset($respLogistics->out_sid))
	{
		$OrderInfo->{'TrackNum'} = (string)$respLogistics->out_sid;
	}

	$mysqli = new mysqli($mysql_server_name, $mysql_username, $mysql_password, $mysql_database);

	if (mysqli_connect_errno())
	{
		$OrderInfo->{'Status'} = 1;
		$OrderInfo->{'Message'} = "Database Connect failed:".mysqli_connect_error();
		echo json_encode($OrderInfo);
		exit();
	}

	$mysqli->query("SET NAMES UTF8;");

	$query = "SELECT Weight FROM TMP_Track_Weight WHERE TrackNum = '".$mysqli->real_escape_string($OrderInfo->{'TrackNum'})."';";
	$result = $mysqli->query($query);
	if ($row = $result->fetch_array(MYSQLI_ASSOC))
	{
		$OrderInfo->{'Weight'} = $row["Weight"];
	}
	$OrderInfo->{'Status'} = 0;	
}
else
{
	$OrderInfo->{'Status'} = 1;
	$OrderInfo->{'Message'} = "No Tid!";
}
echo json_encode($OrderInfo);
?>