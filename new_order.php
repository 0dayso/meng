<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <link rel="stylesheet" href="table.css">
	<script type="text/javascript" src="http://ossweb-img.qq.com/images/js/jquery/jquery-1.7.1.min.js"></script>
	<script language="JavaScript">
    function calc_price(itemid)
    {
    	if(!Number.prototype.toFixed)
		{
			Number.prototype.toFixed = function(num) { with(Math) return round(this.valueof()*pow(10,num))/pow(10,num);}
		}
    	var qty = $("input[name='item_"+itemid+"_qty']").val();
    	if (qty == "")
    	{
    		qty = parseInt(0);
    	}
    	else
    	{
    		qty = parseInt(qty);
    	}
		var price = $("input[name='item_"+itemid+"_price']").val();
		if (price == "")
    	{
    		price = parseFloat(0);
    	}
    	else
    	{
    		price = parseFloat(price);
    	}
		
		$("input[name='item_"+itemid+"_price']").val(price.toFixed(2));
		var sum =  qty * price;
		$("input[name='item_"+itemid+"_sum']").val(sum.toFixed(2));

		calc_total();
    }
    function calc_total()
    {
		var total = parseFloat(0);
		$("input[name$='_sum']").each( function() { var price = $(this).val(); if (price == "") { total = total + parseFloat(0); } else {total = total + parseFloat(price)} });
		
    	$("#total_amount").val(total.toFixed(2));
    	var totalcny = total * $("#Rate").val();
    	$("#total_cnyamount").val(totalcny.toFixed(2));
    }
    function add_itemline(itemid)
    {
    	var nextitemid = parseInt(itemid)+1;
		$("#item_list").append("<tr id=\"item_"+itemid+"_row\"><td><input type=\"text\" size=\"1\" name=\"item_"+itemid+"_qty\" onblur=\"calc_price("+itemid+");\" /></td><td><input type=\"text\" size=\"6\" name=\"item_"+itemid+"_legoid\" /></td><td><input type=\"text\" size=\"5\" name=\"item_"+itemid+"_price\" onblur=\"calc_price("+itemid+");\" /></td><td><input type=\"text\" size=\"5\" name=\"item_"+itemid+"_sum\" disabled/></td><td><div id=\"item_"+itemid+"_ops\"><input type=\"button\" size=\"1\" name=\"item_"+itemid+"_add\" value=\"+\" onclick=\"add_itemline("+nextitemid+");\"/><input type=\"button\" size=\"1\" name=\"item_"+itemid+"_del\" value=\"-\" onclick=\"del_itemline("+itemid+");\"/></div></td></tr>");
		$("div[id$='_ops']").hide();
		$("#item_"+itemid+"_ops").show();
		if (itemid == 1)
		{
			$("input[name='item_"+itemid+"_del'").hide();
		}
		else
		{
			$("input[name='item_"+itemid+"_qty'").focus();
		}
		$("#item_num").val(itemid);

    }
    function del_itemline(itemid)
    {
    	var preitemid = parseInt(itemid)-1;
    	$("#item_"+itemid+"_row").remove();
    	$("div[id$='_ops']").hide();
		$("#item_"+preitemid+"_ops").show();
		if (preitemid == 1)
		{
			$("input[name='item_"+preitemid+"_del'").hide();
		}
		$("#item_num").val(preitemid);
		$("input[name='item_"+preitemid+"_qty'").select();
		$("input[name='item_"+preitemid+"_qty'").focus();
		calc_total();
    }
    function submit_new_order()
    {
    	$("#ajax_submit").attr("disabled","disabled");
    	var postdata = $("#form_Order").serialize();
    	$.post("ajax_new_order.php?action=new_order", postdata, function(data) {submit_new_order_callback(data)} );
    }
    function submit_new_order_callback(data)
    {
    	check_orderid();
    	alert(data);
    }
    function check_orderid()
    {
    	var orderid = $("#OrderNumber").val();
    	$.get("ajax_new_order.php?action=query_order", { oid: orderid}, function(data) {check_orderid_callback(data);} );
    }
    function check_orderid_callback(data)
    {
		if (data != "OK")
		{
			$("#sound").html("<embed src=\"/images/failed.wav\" autostart=\"true\" hidden=\"true\" loop=\"false\">");
			$("#ajax_submit").attr("disabled","disabled");
			$("#order_text").html(data);
		}
		else
		{
			$("#sound").html("<embed src=\"/images/done.wav\" autostart=\"true\" hidden=\"true\" loop=\"false\">");
			$("#order_text").html("<img src=\"/images/checked.png\">");
			$("#ajax_submit").removeAttr("disabled");
		}
    }
    function shipping_method_changed()
    {
    	var shipping_method = $("input:radio[name='shipping_method']:checked").val();
    	if (shipping_method == "notnow")
    	{
    		$("#shipping_method_div").hide();
    	}
    	else
    	{
			$("#shipping_method_div").show();
			var seller = $("#Seller").val();
			var ordernum = $("#OrderNumber").val();
			var ordertime = $("#OrderTime").val();
			switch(seller)
			{
				case "360buy.com":
					$("#Vendor").val("360BUY");
					$("#ShippingOrderNumber").val(ordernum);
					$("#ShippingTime").val(ordertime);
					break;
				case "suning.com":
					$("#Vendor").val("SUNING");
					$("#ShippingOrderNumber").val(ordernum);
					$("#ShippingTime").val(ordertime);
					break;
				default:
			}
			
		}
    }
    function seller_changed()
    {
    	var seller = $("#Seller").val();

		switch(seller)
		{
			case "amazon.com":
				select_currency("USD");
				$('#ShippingTo option[value="TIAN-DE02"').attr('selected', true);
				select_shipping("notnow");
				break;
			case "amazon.de":
				select_currency("EUR");
				select_shipping("notnow");
				break;			
			case "amazon.fr":
				select_currency("EUR");
				select_shipping("notnow");
				break;
			case "amazon.cn":
				select_currency("CNY");
				$('#ShippingTo option[value="LEMO-BJ04"').attr('selected', true);
				select_shipping("now");
				break;
			case "toysrus.com":
				select_currency("USD");
				$('#ShippingTo option[value="TIAN-DE01"').attr('selected', true);
				select_shipping("notnow");
				break;
			case "shop.lego.com":
				select_currency("USD");
				$('#ShippingTo option[value="TIAN-DE01"').attr('selected', true);
				select_shipping("notnow");
				break;
			case "walmart.com":
				select_currency("USD");
				$('#ShippingTo option[value="TIAN-DE01"').attr('selected', true);
				select_shipping("notnow");
				break;
			case "bn.com":
				select_currency("USD");
				$('#ShippingTo option[value="TIAN-DE01"').attr('selected', true);
				select_shipping("notnow");
				break;
			case "yoyo.com":
				select_currency("USD");
				$('#ShippingTo option[value="TIAN-DE01"').attr('selected', true);
				select_shipping("notnow");
				break;
			case "kidsland":
				select_currency("CNY");
				$('#ShippingTo option[value="LEMO-BJ04"').attr('selected', true);
				select_shipping("notnow");
				break;
			case "360buy.com":
				select_currency("CNY");
				$('#ShippingTo option[value="LEMO-BJ04"').attr('selected', true);
				select_shipping("now");
				break;
			case "taobao.com":
				select_currency("CNY");
				$('#ShippingTo option[value="LEMO-BJ04"').attr('selected', true);
				select_shipping("notnow");
				break;
			case "dangdang.com":
				select_currency("CNY");
				$('#ShippingTo option[value="LEMO-BJ04"').attr('selected', true);
				select_shipping("now");
				break;
			case "suning.com":
				select_currency("CNY");
				$('#ShippingTo option[value="LEMO-BJ04"').attr('selected', true);
				select_shipping("now");
				break;
			default:

		}
    }
    function select_currency(currency)
    {
    	$('#Rate option').filter(function () { return $(this).html() == currency; }).attr('selected', true);
    }
    function select_shipping(shipping_method)
    {
    	if (shipping_method == "now")
    	{
    		$("#Radio_shippingnow").prop("checked", true)
			shipping_method_changed();

    	}
    	else
    	{
    	    $("#Radio_notnow").prop("checked", true)
    	    shipping_method_changed();
    	}
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
    function fill_in_time(data)
    {
    	var obj = jQuery.parseJSON(data);
		$("#ShippingTime").val(obj.ShippingTime.date);
    	$("#DeliveryTime").val(obj.DeliveryTime.date);
    }
	</script>
    <title>入库单</title>
</head>
<body onload="add_itemline(1);">
<form id="form_Order">
<span>购买渠道:
<select id="Seller" name="Seller" onchange="seller_changed();">
  <option value="amazon.com">amazon.com</option>
  <option value="amazon.de">amazon.de</option>
  <option value="amazon.fr">amazon.fr</option>
  <option value="amazon.cn">amazon.cn</option>
  <option value="toysrus.com">ToysRus</option>
  <option value="shop.lego.com">Lego S&amp;H</option>
  <option value="walmart.com">Walmart.com</option>
  <option value="target.com">Target.com</option>
  <option value="bn.com">B&amp;N</option>
  <option value="yoyo.com">YoYo</option>
  <option value="kidsland">KidsLand</option>
  <option value="360buy.com">360Buy</option>
  <option value="taobao.com">taobao.com</option>
  <option value="dangdang.com">dangdang.com</option>
  <option value="suning.com">suning.com</option>
</select></span>
<span>帐号:
<select id="Buyer" name="Buyer">
  <option value="hoker_long@sina.com">hoker_long@sina.com</option>
  <option value="hoker.long@qq.com">hoker.long@qq.com</option>
  <option value="hoker.long@gmail.com">hoker.long@gmail.com</option>
  <option value="lana@live.com">lana@live.com</option>
  <option value="lego_citi@qq.com">lego_citi@qq.com</option>
  <option value="lego_comm@qq.com">lego_comm@qq.com</option>
  <option value="lego_spdb@qq.com">lego_spdb@qq.com</option>
  <option value="lego_ihg@qq.com">lego_ihg@qq.com</option>
  <option value="NA">N/A</option>
</select></span>
<span>支付方式:
<select id="Payby" name="Payby">
  <option value="*****">N/A</option>
  <option value="*5972">CITI Master(5972)</option>
  <option value="*4073">CITI Unipay(4073)</option>
  <option value="*8904">CITIC Master(8904)</option>
  <option value="*4851">COMM Master(4851)</option>
  <option value="*2048">COMM Unipay(2048)</option>
  <option value="*6169">SPDB Visa(6169)</option>
  <option value="*9277">CMB Visa(9277)</option>
  <option value="*3069">CMB Master(3069)</option>
</select></span>
<br />
<span>发送到:
<select id="ShippingTo" name="ShippingTo" />
  <option value="TIAN-DE01" selected>tiantian DE合箱(145)</option>
  <option value="4PX-OR01">4方OR</option>
  <option value="QQEX-DE01">QQ快递DE</option>
  <option value="DRCT-CA01">CA直购</option>
  <option value="LEMO-BJ01">LEMO Y!公司</option>
  <option value="LEMO-BJ02">LEMO JYHF-10-2604</option>
  <option value="LEMO-BJ03">LEMO JYHF-10-0103</option>
  <option value="LEMO-BJ04">LEMO JYHF-2-2102</option>
</select>
</span>
<span>备注:<input type="text" size="20" name="Memo" /></span><span id="orde_text"></span>
<br />
<span>下单时间:<input type="text" size="23" id="OrderTime" name="OrderTime" value="<?php date_default_timezone_set('Asia/Shanghai'); echo $showtime=date('Y-m-d H:i:s'); ?>"/></span>
<span>
<select id="Timezone" name="Timezone" >
  <option value="+8">CST</option>
  <option value="-8">PST</option>
  <option value="-12">EST</option>
</select>
</span>
<br />
<span>订单号:<input type="text" size="20" id="OrderNumber" name="OrderNumber" onblur="check_orderid();" /></span><span id="order_text"></span>
<br />
<span>总金额:<input type="text" size="7" id="total_amount" disabled/>
<select id="Rate" name="Rate" onchange="calc_total();">
  <option value="1">CNY</option>
  <option value="6.30">USD</option>
  <option value="8.30">EUR</option>
</select></span>
<span>=<input type="text" size="7" id="total_cnyamount" disabled/></span>

<table>
<thead><tr><th>数量</th><th>LEGO编号</th><th>单价</th><th>总价</th><th>操作</th></tr></thead>
<tbody id="item_list"></tbody>
</table>
<input type="hidden" id="item_num" name="item_num" value="1" />
<input type="radio" id="Radio_notnow" name="shipping_method" value="notnow" onchange="shipping_method_changed();" checked />暂不发货<input type="radio" id="Radio_shippingnow" name="shipping_method" value="shippingnow" onchange="shipping_method_changed();"/>包邮发货
<br />
<div id="shipping_method_div" style="display: none;">
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
</select></span>
<span>订单号:<input type="text" size="20" id="ShippingOrderNumber" name="ShippingOrderNumber" onblur="check_shipping_orderid();"/></span><span id="shipping_order_text"></span>
<br />
<span>发货时间:<input type="text" size="23" id="ShippingTime" name="ShippingTime"/></span>
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
</div>
<input type="button" id="ajax_submit" value="提交" onclick="submit_new_order();" disabled/>
</form>
<div id="sound"></div>
</body>
</html>