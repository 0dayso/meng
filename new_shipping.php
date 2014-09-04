<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <link rel="stylesheet" href="table.css">
    <style type="text/css">
a.expend_icon { 
	text-decoration:none;
	width: 12px;
	height: 20px;
	cursor: pointer;
	text-align: center;
	line-height: 20px;
	font-weight: bold;
	font-size: 14px;
	color: #575757;
}
.order_div {
	display: none;
	padding: 0; 

}
.order_div ul { 
    list-style: none; 
    margin: 0; 
    padding: 2px;
    margin-left: 20px;
}
.order_div ul li { 
    margin: 0; 
} 
.order_div ul li a { 
    display: block; 
    padding: 2px 2px 2px 0.5em; 
    border-left: 10px solid #369; 
    border-right: 1px solid #69c; 
    border-bottom: 1px solid #369; 
    background-color: #036; 
    color: #fff; 
    text-decoration: none; 
    width: 100%; 
} 
</style>

	<script type="text/javascript" src="http://ossweb-img.qq.com/images/js/jquery/jquery-1.7.1.min.js"></script>
	<script language="JavaScript">
	function multi_checkbox(orderid)
	{
		if ($("input[name='Order_"+orderid+"']").prop("checked"))
		{
			$("#div_order_"+orderid+" ul li input[type=checkbox]").prop("checked", "checked");
		}
		else
		{
			$("#div_order_"+orderid+" ul li input[type=checkbox]").removeAttr('checked');
		}
		var seller = $("#Seller_"+orderid).html();
		var refid = $("#OrderID_"+orderid).html();

		$("select[name='ShippingFrom']").val(seller);
		$("#RefID").val(refid);

		all_selected();
	}
	function show_order(orderid)
	{
		if ($("#exp_"+orderid).html() == "+")
		{
			$("#exp_"+orderid).html("-");
			$("#div_order_"+orderid).toggle();
		}
		else
		{
			$("#exp_"+orderid).html("+");
			$("#div_order_"+orderid).toggle();
		}
	}
    function calc_shipping_total()
    {
		var total = parseFloat($("#total_amount").val());
		if (isNaN(total) == true)
    	{
    		total = parseFloat(0);
    	}
    	var totalcny = parseFloat(total) * parseFloat($("#Rate").val());
    	if(!Number.prototype.toFixed)
		{
			Number.prototype.toFixed = function(num) { with(Math) return round(this.valueof()*pow(10,num))/pow(10,num);}
		}

    	$("#total_amount").val(total.toFixed(2));
    	$("#total_cnyamount").val(totalcny.toFixed(2));
    	var sum = parseFloat(0);
		$("input[name^='price_']").each( function()
		{ 
			var price = parseFloat($(this).val());
			if (isNaN(price) == true)
			{
				sum = sum + parseFloat(0);
			}
			else
			{
				sum = sum + price;
			}
		});
		$("input[name^='shippingfee_']").each( function() 
		{
			var itemstr = $(this).attr("name").split("_");
			var item_id = itemstr[1];
			var item_price = $("input[name='price_"+item_id+"']").val();
			var item_fee = item_price / sum * total;
			$("input[name='shippingfee_"+item_id+"']").val(item_fee.toFixed(2));
		});

    }
	function all_selected()
	{
		var id_array=new Array();
		$("input:checked[name^='Item_']").each(function()
		{
			id_array.push($(this).val());
		});
		var idstr=id_array.join(',');
		$("input[name='itemids']").val(idstr);
    	$.post("ajax_new_shipping.php?action=query_items", { ids: idstr}, function(data) {all_selected_callback(data)} );
	}
	function all_selected_callback(data)
	{
		$("#item_list").html(data);
		calc_shipping_total();
	}
	function search()
	{
		var search_text = $("input[name='search_text']").val();
		var search_type = $("select[name='search_type']").val();
		if (search_text == "")
		{
			$.get("ajax_new_shipping.php?action=list_order", null, function(data) {search_callback(data)} );
		}
		else if (search_type == "legoid")
		{
			$.get("ajax_new_shipping.php?action=list_order", { legoid: search_text} , function(data) {search_callback(data)} );
		}
		else if (search_type == "orderid")
		{
			$.get("ajax_new_shipping.php?action=list_order", { orderid: search_text} , function(data) {search_callback(data)} );
		}
		$("#order_list").html('<img src="/images/loading.gif">');

	}
	function search_callback(data)
	{
		$("#order_list").html(data);
	}
	function ajax_form_submit()
	{
		$("#ajax_submit").attr("disabled","disabled");

		var postdata = $("#shipping_fee_form").serialize();
    	$.post("ajax_new_shipping.php?action=new_shipping", postdata, function(data) {ajax_form_submit_callback(data)} );
	}
	function ajax_form_submit_callback(data)
	{
		alert(data);
		check_orderid();
		search();
	}
	function check_shipping_orderid()
    {
    	var orderid = $("input[name='ShippingOrderNumber']").val();
    	$.get("ajax_new_shipping.php?action=query_order", { oid: orderid}, function(data) {check_shipping_orderid_callback(data);} );
    	if ($("#Vendor").val() == "UPS")
    	{
    		$.get("query_ups.php?r=time", { upsid: orderid}, function(data) {fill_in_time(data);} );
    	}
    	else if ($("#Vendor").val() == "FEDEX")
    	{
    		$.get("query_fedex.php?r=time", { fedexid: orderid}, function(data) {fill_in_time(data);} );
    	}
    	else if ($("#Vendor").val() == "USPS")
    	{
    		$.get("query_usps.php?r=time", { uspsid: orderid}, function(data) {fill_in_time(data);} );
    	}    	
    }
    function fill_in_time(data)
    {
    	var obj = jQuery.parseJSON(data);
		$("#ShippingTime").val(obj.ShippingTime.date);
    	$("#DeliveryTime").val(obj.DeliveryTime.date);
    }
    function check_shipping_orderid_callback(data)
    {
		if (data != "OK")
		{
			$("#ajax_submit").attr("disabled","disabled");
			$("#shipping_order_text").html(data);
		}
		else
		{
			$("#shipping_order_text").html("<img src=\"/images/checked.png\">");
			$("#ajax_submit").removeAttr("disabled");
		}
    }
	</script>
    <title>发货单</title>
</head>
<body onload="search();">
<div style="width:400px; float:left">
	<div id="search">
	<select name="search_type" >
	  <option value="legoid">LegoID</option>
	  <option value="orderid">OrderID</option>
	</select>
	<input type="text" name="search_text" size="14" />
	<input type="button" value="搜" onclick="search();"/>
	</div>
	<div id="order_list">
	</div>
</div>
<div id="transit_form" style="margin-left:410px">
<form id="shipping_fee_form">
<span>原地址:
<select name="ShippingFrom" />
  <option value="amazon.com">amazon.com</option>
  <option value="amazon.de">amazon.de</option>
  <option value="amazon.fr">amazon.fr</option>
  <option value="amazon.cn">amazon.cn</option>
  <option value="toysrus.com">ToysRus</option>
  <option value="target.com">Target</option>
  <option value="shop.lego.com">Lego S&amp;H</option>
  <option value="walmart.com">Walmart.com</option>
  <option value="bn.com">B&amp;N</option>
  <option value="yoyo.com">YoYo</option>
  <option value="kidsland">KidsLand</option>
  <option value="360buy.com">360Buy</option>
  <option value="taobao.com">taobao.com</option>
  <option value="dangdang.com">dangdang.com</option>
  <option value="suning.com">suning.com</option>
</select>
</span>
<span>发送到:
<select name="ShippingTo" />
  <option value="TIAN-DE01">tiantian DE合箱(145)</option>
  <option value="TIAN-DE02">tiantian DE原箱(177)</option>
  <option value="4PX-OR01">4方OR</option>
  <option value="QQEX-DE01">QQ快递DE</option>
  <option value="LEMO-BJ01">LEMO Y!公司</option>
  <option value="LEMO-BJ02">LEMO JYHF-10-2604</option>
  <option value="LEMO-BJ03">LEMO JYHF-10-0103</option>
  <option value="LEMO-BJ04">LEMO JYHF-2-1-2102</option>
</select>
</span>
<br />
<span>承运方:
<select id="Vendor" name="Vendor">
  <option value="UPS">UPS</option>
  <option value="USPS">USPS</option>
  <option value="FEDEX">FedEx</option>
  <option value="ONTRAC">OnTrac</option>
  <option value="DHL">DHL</option>
  <option value="DHL.DE">DHL.DE</option>
  <option value="EMS">EMS</option>
  <option value="ZTO">ZTO中通</option>
  <option value="SFE">SFE顺丰</option>
  <option value="ZCN">amazon.cn</option>
  <option value="DANGDANG">dangdang.com</option>
  <option value="SUNING">suning.com</option>
  <option value="360BUY">jd.com</option>
  <option value="SELF">SELF</option>
</select></span>
<span>订单号:<input type="text" size="20" name="ShippingOrderNumber" onblur="check_shipping_orderid();"/></span><span id="shipping_order_text"></span>
<br />
<span>发货时间:<input type="text" size="23" id="ShippingTime" name="ShippingTime" value="<?php date_default_timezone_set('Asia/Shanghai'); echo $showtime=date('Y-m-d H:i:s'); ?>"/></span>
<span>
<select id="ShippingTimezone" name="ShippingTimezone" >
  <option value="+8">CST</option>
  <option value="-8">PST</option>
  <option value="-12">EST</option>
</select>
</span>
<br />
<span>到货时间:<input type="text" size="23" id="DeliveryTime" name="DeliveryTime" /></span>
<span>
<select id="DeliveryTimezone" name="DeliveryTimezone" >
  <option value="+8">CST</option>
  <option value="-8">PST</option>
  <option value="-12">EST</option>
</select>
</span>
<br />
<span>关联单号:<input type="text" size="27" id="RefID" name="RefID" /></span>
<br />
<span>总金额:<input type="text" size="7" id="total_amount" onblur="calc_shipping_total();"/>
<select id="Rate" name="Rate" onchange="calc_shipping_total();">
  <option value="1">CNY</option>
  <option value="6.2">USD</option>
  <option value="8.30">EUR</option>
</select></span>
<span>=<input type="text" size="7" id="total_cnyamount" disabled/></span>

<table>
<thead><tr><th>LEGO编号</th><th>单价</th><th>均分运费</th></tr></thead>
<tbody id="item_list"></tbody>
</table>
<input type="hidden" name="itemids" value="" />
<input type="button" id="ajax_submit" value="提交" onclick="ajax_form_submit();" disabled/>
</form>
</div>
<div id="sound"></div>
</body>
</html>


