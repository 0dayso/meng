<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<style type="text/css">
html,body,div,span,applet,object,iframe,table,caption,tbody,tfoot,thead,tr,th,td,del,dfn,em,font,img,ins,kbd,q,s,samp,small,strike,tt,var, h1, h2, h3, h4, h5, h6, p, blockquote, pre, a, abbr, acronym, address, big, cite, code, dl, dt, dd, ol, ul, li, fieldset, form, label, legend {
    outline:0;
    padding:0;
    margin:0;
    border:0;
    text-align:left;
    font-style:normal;
}:focus{
    outline:0;
}
html,
body {
    text-align:center;
}
body {
    font-family:verdana,SimSun,Helvetica,sans-serif;
    color:#2a5c84;
    font-size:12px;
}
ol,
ul {
    list-style:none;
}
table {
    border-collapse:collapse;
    border-spacing:0;
}
caption,
th,
td {
    font-weight:normal;
    text-align:left;
    vertical-align:top;
}
blockquote:before,
blockquote:after,
q:before,
q:after {
    content:"";
}
blockquote,
q {
    quotes:"""";
}
strong,
b,
em,
i,
address,
cite {
    font-style:normal;
}
img {
    vertical-align:top;
}
a:link,
a:visited {
    color:#c9570c;
    text-decoration:none;-webkit-transition:color.3s linear;-moz-transition:color.3s linear;-o-transition:color.3s linear;
    transition:color.3s linear;
}
a:hover {
    color:#d95854;
    text-decoration:none;
}
span .hightlight{color: #e61717}

.content{width:800px;margin:0 auto}
.line{width:800px;height: 2px;background: #b6d5ef;overflow: hidden;z-index: 1;margin-bottom: 20px}
.tab li{float: left;width: 131px;height:36px;text-align: center;line-height: 36px;cursor: pointer;z-index: 1;margin-bottom: -2px;background: #daeaf7;margin-right: 3px;font-size: 14px;border-bottom: 3px solid #fff}
.tab li.cur{border-top:2px solid #b6d5ef;border-right: 2px solid #b6d5ef;border-left: 2px solid #b6d5ef;background: #fff;border-bottom: 4px solid #fff}
.orders{width:798px;border:1px solid #b6d5ef;}
.order_header{height: 33px;line-height: 33px;padding:0 15px;background: #e2eef9}
.order_header span{float: right;cursor: pointer;}
.order_summary{margin: 10px 0}
.nameprice td{padding-left: 15px;line-height: 24px;height: 24px;font-size: 14px}

.barcode{text-align: center;width: 399px}
.order_detail{border-bottom:1px solid #b6d5ef;width: 100%}

.item_detail{width: 100%}
.item_detail tr{border-top: 1px solid #b6d5ef;border-bottom: 1px solid #b6d5ef;}
.item_detail td{margin-left: 20px; text-align: center}
.item_detail td p{padding-left: 20px;line-height: 25px}
.item_detail span{color: #e61717;font-weight: bold;}

.item_addr td{padding-left: 15px;line-height: 24px;height: 24px;font-size: 14px}
.item_addr span{color: #e61717;font-weight: bold;}

.item_title{white-space: nowrap;text-overflow: ellipsis;overflow: hidden;}
.tracking_form{padding-left: 15px; font-size: 14px}
.tracking_num{height: 18px;border:1px solid #b6d5ef;margin-right: 5px;vertical-align: middle;}
.tracking_weight{height: 18px;border:1px solid #b6d5ef;margin-right: 5px;vertical-align: middle;}
.tracking_button{height: 24px;width: 85px;text-align: center;color: #fff;border:0;background: #2a5c84;vertical-align: middle;margin-left: 40px;cursor: pointer;}
input[type="button"]:disabled{background-color: #ddd; color:#ACA899;}
</style>
	<script type="text/javascript" src="script/jquery-1.11.1.min.js"></script>
	<script language="JavaScript">
	jQuery(document).ready(function() {
		$('.tab li').click(function(){
			if(!$(this).hasClass('cur')){
				$(this).addClass('cur');
				$(this).siblings().removeClass('cur')
			}
		})
		$('.order_header, .order_summary').click(function(){
			if($(this).closest('.orders').find('.order_detail').css('display') == 'none'){
				$(this).closest('.orders').find('.order_detail').show('fast')
				$(this).closest('.orders').find('.tracking_num').select()
				$(this).closest('.orders').find('.tracking_num').focuse()
				$(this).closest('.orders').find('.order_header .order_expend').html('收起︽')
			}else{
				$(this).closest('.orders').find('.order_detail').hide('fast')
				$(this).closest('.orders').find('.order_header .order_expend').html('展开︾')
			}
		})
	})
	
	
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
			$("#track_"+orderid).select();
			$("#track_"+orderid).focus();

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
	function form_submit(orderid)
	{
		var vendor = $("#vendor_"+orderid).val();
		var tracknum = $("#track_"+orderid).val();
		var weight = $("#weight_"+orderid).val();
		
		$("#submit_"+orderid).attr("disabled","disabled");
		
		$.post("ajax_taobao_sold_track.php?action=shipping", { orderid: orderid, vendor: vendor, tracknum: tracknum, weight: weight }, function(data) {submit_sold_track_callback(data)} );

	}
	function submit_sold_track_callback(data)
	{
		obj = jQuery.parseJSON(data);
		if (obj)
		{
			if (obj.Status == "0")
			{
				alert(obj.Message);
				$("#order_"+obj.OrderID).hide("fast");
				var count_sent = $("#count_sent").html();
				$("#count_sent").html(count_sent-1);
			}
			else
			{
				alert(obj.Message);
				$("#submit_"+obj.OrderID).removeAttr("disabled");
			}
		}
	}
	</script>
    <title>出库单</title>
</head>
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
$reqTrades->setPageSize(30);
$reqTrades->setUseHasNext("true");
$respTrades = $c->execute($reqTrades, $sessionKey);

$ordercount = count($respTrades->trades->trade);
?>
<body>
	<div class="content">
		<ul class="tab">
            <li class="cur">未发货(<span id="count_sent"><?php echo($ordercount); ?></span>)</li>
            <li>已发货()</li>
            <li>全部订单()</li>     
        </ul>
        <div class="line"></div>
<?php
if ($ordercount)
{
	foreach ($respTrades->trades->trade as $trade)
	{
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

        <div class="orders" id="order_<?php echo $trade->sid; ?>">
            <div class="order_header">
                <?php echo $trade->pay_time; ?><span class="order_expend">展开︾</span>
            </div>
            <div class="order_summary">
                <table class="nameprice"> 
                    <tr>
                        <td width="399">买家：<?php echo $trade->buyer_nick; ?></td>
                        <td rowspan="2" class="barcode">订单号：<?php echo $trade->sid; ?></td>
					</tr>
                    <tr>
                    	<td width="399">金额：&yen;<?php echo $trade->payment; ?>(&yen;<?php echo $trade->total_fee; ?>+&yen;<?php echo $trade->post_fee; ?>)</td>
                    </tr>
                </table>
            </div>
            <div class="order_detail" style="display:none">
                <table class="item_detail">
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
                    <tr>
                        <td><img height="120px" width="120px" src="<?php echo $order->pic_path; ?>"></td>
                        <td>            
							<p class="item_title"><?php echo $title; ?></p>
							<p>编号：<span><?php echo $legoid; ?></span></p>
							<p>颜色：<span>默认</span></p>
							<p>数量：<span><?php echo $num; ?></span></p>
                        </td>
                    </tr>
<?php
	}
?>
                </table>
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
				<table class="item_addr">
					<tr><td>总计件数：<span><?php echo $sum; ?></span>件</td></tr>
					<tr><td>收货信息：<span><?php echo $strFullAddr; ?></span></td></tr>
					<tr><td>买家留言：<?php echo $memo->buyer_message; ?></td></tr>
					<tr><td>卖家备注：<?php echo $memo->seller_memo; ?></td></tr>
				</table>
				<div class="tracking_form">
                        物流:
<?php
	if ($trade->shipping_type == "ems")
	{
?>
					<select id="vendor_<?php echo $trade->sid; ?>">
						<option value="ZTO">中通</option>
						<option value="SFE" selected>顺丰</option>
						<option value="NA">自提</option>
						<option value="Other">其他</option>
					</select>
<?php
	}
	else
	{
?>
					<select id="vendor_<?php echo $trade->sid; ?>">
						<option value="ZTO" selected>中通</option>
						<option value="SFE">顺丰</option>
						<option value="NA">自提</option>
						<option value="Other">其他</option>
					</select>
<?php
	}
?>
					运单号:
					<input type="text" id="track_<?php echo $trade->sid; ?>" class="tracking_num" name="track_<?php echo $trade->sid; ?>" size="18" value="请输入快递单号......" onkeydown="enterEventHandler();" onclick="select_focus(this.id);"> 
					<input type="text" id="weight_<?php echo $trade->sid; ?>" class="tracking_weight" name="weight_<?php echo $trade->sid; ?>" size="4" value="重量..." onkeydown="enterEventHandler();" onclick="select_focus(this.id);">kg
					<input type="button" class="tracking_button" id="submit_<?php echo $trade->sid; ?>" value="提交" onclick="form_submit(<?php echo $trade->sid; ?>);">    
                </div>
            </div>
        </div>
<?php
	}
?>
	</div>
<?php
}
?>	

	<div id="sound"></div>
</body>
</html>
