<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <link rel="stylesheet" href="table.css">
	<script type="text/javascript" src="script/jquery-1.11.1.min.js"></script>
	<script language="JavaScript">
	function select_focus(itemid)
	{
		$("#"+itemid).select();
		$("#"+itemid).focus();
	}
	function enterEventHandler(e)
	{
	
    var event = $.event.fix(e); //修正event事件
    var element = event.target; //jQuery统一修正为target
    var buttons = "button,reset,submit"; //button格式
    if (element.nodeName == "INPUT" || element.nodeName == "SELECT") {
        event.stopPropagation(); //取消冒泡
        event.preventDefault(); //取消浏览器默认行为
        var inputs = $("input[type!='hidden'][type!='checkbox'][type!='radio'],select"); //获取缓存的页面input集合
        var index = inputs.index(element); //当前input位置      
        if (buttons.indexOf(inputs[index + 1].type) >= 0) {
            inputs[index + 1].focus();
            inputs[index + 1].click();
        }
        else {
            inputs[index + 1].focus();
        }
 
    	}
	}
	function order_submit(orderid,vendor,tracknum)
	{
		var weight = $("#weight_"+orderid).val();
		
		$("#submit_"+orderid).attr("disabled","disabled");
		
		$.post("ajax_taobao_sold_track.php?action=record", { orderid: orderid, vendor: vendor, tracknum: tracknum, weight: weight }, function(data) {order_submit_callback(data)} );

	}
	function order_submit_callback(data)
	{
		obj = jQuery.parseJSON(data);
		if (obj)
		{
			if (obj.Status == "0")
			{
				// $("#message_"+obj.OrderID).html(obj.Message);
				$("#row_"+obj.OrderID).remove();
			}
			else
			{
				$("#message_"+obj.OrderID).html(obj.Message);
				$("#submit_"+obj.OrderID).removeAttr("disabled");
			}
		}
	}

	</script>
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

if (isset($_GET['month']))
{
	$month = $_GET['month'];
}
else
{
	$month = date('m');
}

$Date = new DateTime(date("Y-".$month."-01 00:00:00", time()));

$FromDate = $Date->modify('-1 day')->format('Y-m-d 00:00:00');
$ToDate =  $Date->modify('+1 month')->format('Y-m-d 00:00:00');

$query = "SELECT SOrderID,OrderNumber FROM PSS_SOrder WHERE OrderTime BETWEEN '".$mysqli->real_escape_string($FromDate)."' AND '".$mysqli->real_escape_string($ToDate)."';";
$result = $mysqli->query($query);

$TaobaoOrders = array();
while ($row = $result->fetch_array(MYSQLI_ASSOC))
{
	$SOrderID = $row["SOrderID"];
	$OrderNumber = $row["OrderNumber"];
	$TaobaoOrders["$OrderNumber"] = $SOrderID;
}

$query = "SELECT * FROM TB_Item;";
$result = $mysqli->query($query);

$TaobaoItems = array();
while ($row = $result->fetch_array(MYSQLI_ASSOC))
{
	$TaobaoID = $row["ItemID"];
	$LegoID = $row["LegoID"];
	$TaobaoItems["$TaobaoID"] = $LegoID;
}

$query = "SELECT * FROM TMP_Track_Weight;";
$result = $mysqli->query($query);

$TrackWeight = array();
while ($row = $result->fetch_array(MYSQLI_ASSOC))
{
	$TrackNum = $row["TrackNum"];
	$Weight = $row["Weight"];
	$TrackWeight["$TrackNum"] = $Weight;
}

include "appconf.php";
include "TopSdk.php";

//实例化TopClient类
$c = new TopClient;
$c->appkey = $client_id;
$c->secretKey = $client_secret;
$sessionKey = $access_token;

$reqTrades = new TradesSoldGetRequest;
$reqTrades->setFields("tid,sid,pay_time,consign_time,orders");
$reqTrades->setStatus("TRADE_FINISHED");
//$reqTrades->setStatus("WAIT_BUYER_CONFIRM_GOODS");
$reqTrades->setStartCreated($FromDate);
$reqTrades->setEndCreated($ToDate);
$reqTrades->setPageSize(40);
$reqTrades->setUseHasNext("true");

echo $FromDate."->".$ToDate."<br>";
echo "<table><tr><th>OrderTime</th><th>OrderNumber</th><th>Items</th><th>ShippingTime</th><th>TrackNum</th><th>Weight</th><th>Operation</th><th>Status</th></tr>";

$page = 1;
$nextpage = true;

while ($nextpage)
{
	$reqTrades->setPageNo($page);
	$respTrades = $c->execute($reqTrades, $sessionKey);

	if ((string)$respTrades->has_next == "true")
	{
		$nextpage = true;
		$page++;
	}
	else
	{
		$nextpage = false;
	}
	
	foreach ($respTrades->trades->trade as $trade)
	{
		$OrderTime = (string)$trade->pay_time;
		$OrderNumber = (string)$trade->sid;
		$ShippingTime = (string)$trade->consign_time;
		$arrItems = array();
		if (!isset($TaobaoOrders["$OrderNumber"]))
		{
			foreach ($trade->orders->order as $order)
			{
				//$num_iid = $order->num_iid;
				//$title = $order->title;
				$num = $order->num;
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

				if ($num > 1)
				{
					$itemstr = $legoid."*".$num;
				}
				else
				{
					$itemstr = $legoid;
				}
				array_push($arrItems, $itemstr);
			}
			$Items = join(",",$arrItems);
			
			$reqLogistics = new LogisticsTraceSearchRequest;
			$reqLogistics->setTid($trade->tid);
			$reqLogistics->setSellerNick("懒懒淑女");
			$respLogistics = $c->execute($reqLogistics);

			if($respLogistics->company_name == "顺丰速运")
			{
				$Vendor = "SFE";
			}
			elseif ($respLogistics->company_name == "中通快递")
			{
				$Vendor = "ZTO";
			}
			else
			{
				$Vendor = "Other";
			}
		
			if (isset($respLogistics->out_sid))
			{
				$TrackNum = $respLogistics->out_sid;
			}

			$submit_enable = "";
			if (isset($TrackWeight["$TrackNum"]))
			{
				$Weight = $TrackWeight["$TrackNum"];
			}
			else
			{
				$Weight = "重量...";
				//$submit_enable = " disabled";

			}
			
			echo "<tr id=\"row_".$OrderNumber."\"><td>".$OrderTime."</td><td><a href=\"http://trade.taobao.com/trade/detail/trade_item_detail.htm?bizOrderId=".$OrderNumber."\">".$OrderNumber."</a></td><td>".$Items."</td><td>".$ShippingTime."</td><td>".$Vendor.":".$TrackNum."</td><td><input type=\"text\" id=\"weight_".$OrderNumber."\" name=\"weight_".$OrderNumber."\" size=\"4\" value=\"".$Weight."\" onkeydown=\"enterEventHandler();\" onclick=\"select_focus(this.id);\">kg</td><td><input type=\"button\" id=\"submit_".$OrderNumber."\" value=\"提交\" onclick=\"order_submit('".$OrderNumber."','".$Vendor."','".$TrackNum."');\" ".$submit_enable."></td><td id=\"message_".$OrderNumber."\"></td></tr>";

		}
	}
}
echo "</table>";

?>
</body>
</html>