<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <link rel="stylesheet" href="table.css">
	<script type="text/javascript" src="script/jquery-1.11.1.min.js"></script>
	<script language="JavaScript">
	function query_barcode(itemid)  
	{
		var e = window.event || ev;
		var keyCode = -1;
		if (e.which == null)
		keyCode= e.keyCode;    // IE
		else if (e.which > 0)
		keyCode=e.which;    // All others
		if(keyCode==13)
		{
			var barcode = $("#"+itemid).val(); 
			var retid = "match_"+itemid.substring(itemid.indexOf("_")+1, itemid.length);
			$.get("ajax_bc.php", { a: "query", bc: barcode}, function(data) {legoid_callback(retid, data);} );
		}
	}
	function legoid_callback(retid, legoid)
	{
		if (legoid == "Unknown barcode")
		{
			$("#sound").html("<embed src=\"/images/failed.wav\" autostart=\"true\" hidden=\"true\" loop=\"false\">");
			$("#"+retid).html("<span>XXX</span>");

		}
		else
		{
			$("#sound").html("<embed src=\"/images/done.wav\" autostart=\"true\" hidden=\"true\" loop=\"false\">");
			$("#"+retid).html(legoid);
		}
	}
	function show_form(ordername)
	{
		var orderid = ordername.substring(ordername.indexOf("_")+1, ordername.length);
		var formname = "form_"+orderid;
		if ($("#"+formname).css("display") == "none")
		{
			$("#"+formname).fadeToggle("fast");
			$("#item_"+orderid+"_1").select();
			$("#item_"+orderid+"_1").focus();

		}
		else
		{
			$("#"+formname).fadeToggle("fast");
		}

	}
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
	</script>
    <title>出库单</title>
</head>
<body>

<div name="orderlist">

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

$reqTrades = new TradesSoldGetRequest;
$reqTrades->setFields("tid,sid,status,seller_flag,has_buyer_message,pay_time,buyer_nick,total_fee,payment,post_fee,orders,receiver_state,receiver_city,receiver_district,receiver_address,receiver_name,receiver_mobile,receiver_phone,pic_path,shipping_type");
//$reqTrades->setStatus("WAIT_BUYER_CONFIRM_GOODS");
$reqTrades->setStatus("WAIT_SELLER_SEND_GOODS");
$reqTrades->setPageNo(1);
$reqTrades->setPageSize(40);
$reqTrades->setUseHasNext("true");
//$reqTrades->setIsAcookie("false");
$respTrades = $c->execute($reqTrades, $sessionKey);

if (count($respTrades->trades->trade) == 0)
{
	echo "暂无订单";
}
else
{
foreach ($respTrades->trades->trade as $trade)
{
?>
	<div id="order_<?php echo $trade->sid; ?>" onclick="show_form(this.id)">
	<table>
	<tr><td width="220">买家：<b><?php echo $trade->buyer_nick; ?></b></td><td>订单号：<?php echo $trade->sid; ?></td></tr>
	<tr><td>付款金额：¥<?php echo $trade->payment." =".$trade->total_fee."+".$trade->post_fee; ?></td><td width="220">时间：<?php echo $trade->pay_time; ?></td></tr>
	</table>
	</div>
	<div id="form_<?php echo $trade->sid; ?>" class="formdiv">
	<table>
	
<?php
	$sum = 0;
	foreach ($trade->orders->order as $order)
	{
	  $num_iid = $order->num_iid;
	  $title = $order->title;
	  $num = $order->num;
	  $sum = $sum + $num;
	  $legoid = $TaobaoItems["$num_iid"];
?>
	<tr><td><img width="150px" height="150px" src="<?php echo $order->pic_path; ?>" /></td><td colspan="2"><span class="highlight"><?php echo $num; ?></span>件 * <span class="highlight"><?php echo $legoid; ?></span><br/>产品名称：<?php echo $title; ?><br/>颜色分类：<br/>
<?php
	for($i=1;$i<=$num;$i++)
	{
		$itemid = $trade->sid."_".$i;
?>
		<input type="text" id="item_<?php echo $itemid; ?>" size="18" value="请输入商品条码..." onkeydown="query_barcode(this.id);" onkeydown="enterEventHandler();" onclick="select_focus(this.id);"/><div id="match_<?php echo $itemid; ?>"></div>
<?php
	}
?>
	</td></tr>
<?php
	}
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

?>
	<tr><td colspan="3">总计件数：<span class="highlight"><?php echo $sum; ?></span>件</td></tr>
	<tr><td colspan="3">收货信息：<b><?php echo $strFullAddr; ?></b></td></tr>
<?php
	if ($trade->has_buyer_message || seller_flag > 0)
	{
		$reqMemo = new TradeGetRequest;
		$reqMemo->setFields("buyer_memo,seller_memo,buyer_message");
		$reqMemo->setTid($trade->sid);
		$respMemo = $c->execute($reqMemo, $sessionKey);
		$memo = $respMemo->trade;
	}
?>
	<tr><td colspan="3">备注：<span class="highlight_blue"><?php echo $memo->buyer_message; ?></span></br><span class="highlight"><?php echo $memo->seller_memo; ?></span></td></tr>
	<tr><td width="120"">物流名称：
<?php 
	if ($trade->shipping_type == "ems")
	{
?>
	<select><option value="ZTO">中通</option><option value="SFE" selected>顺丰</option><option value="NA">自提</option><option value="Other">其他</option></select></td>
<?php
	}
	else
	{
?>
	<select><option value="ZTO">中通</option><option value="SFE">顺丰</option><option value="NA">自提</option><option value="Other">其他</option></select></td>
<?php
	}
?>
	<td>运单号:<input type="text" id="track_<?php echo $trade->sid; ?>" size="18" value="请输入快递单号......" onkeydown="enterEventHandler();" onclick="select_focus(this.id);">&nbsp;&nbsp;&nbsp;重量:<input type="text" id="weight_<?php echo $trade->sid; ?>" size="4" onkeydown="enterEventHandler();" onclick="select_focus(this.id);">kg</td></tr>
	<tr><td><input type="button" id="submit_<?php echo $trade->sid; ?>" value="提交" onclick="form_submit(<?php echo $trade->sid; ?>);"></td></tr>
	</table>
	</div>
<?php
}
}
?>	


<div id="sound">
</div>
</body>
</html>