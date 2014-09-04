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
#order_list table tr td a {text-decoration:none; position:relative; display:block;}
#order_list table tr td a div{display:none;}
#order_list table tr td a:hover{ visibility:visible;}
#order_list table tr td a:hover div{position:absolute; left:280px; top:-40px; background-color:#FFF; display:block; width:300px; height:100px; color:#000; overflow:hidden;}

</style>

	<script type="text/javascript" src="http://ossweb-img.qq.com/images/js/jquery/jquery-1.7.1.min.js"></script>
	<script language="JavaScript">
	function search()
	{
		var search_type = $("select[name='search_type']").val();

		$.get("ajax_new_delivery.php?action=list_order", { type: search_type} , function(data) {search_callback(data)} );

		$("#order_list").html('<img src="/images/loading.gif">');

	}
	function search_callback(data)
	{
		$("#order_list").html(data);
	}
	function unlock_update_time(orderid)
	{
		$("#btn_delivery_"+orderid).removeAttr("disabled");
	}
	function query_time(orderid)
	{
		$("#btn_delivery_"+orderid).attr("disabled","desabled");

		var vendor = $("#vendor_"+orderid).html();
		var trackID = $("#ordernumber_"+orderid).html();
		switch (vendor)
		{
			case "EMS.TX": case "EMS":
				$.get("query_ems.php", {r: "dtime", emsid: trackID}, function(data) { query_time_callback(orderid, data) } );
				break;
			case "UPS":
				$.get("query_ups.php", {r: "time", upsid: trackID}, function(data) { query_time_callback(orderid, data) } );
				break;
			case "USPS":
				$.get("query_usps.php", {r: "time", uspsid: trackID}, function(data) { query_time_callback(orderid, data) } );
				break;
			case "FEDEX":
				$.get("query_fedex.php", {r: "time", fedexid: trackID}, function(data) { query_time_callback(orderid, data) } );
				break;
			case "SUNING":
				$("#btn_delivery_"+orderid).removeAttr("disabled");
				break;
		}
	}
	function query_time_callback(orderid, data)
	{
		obj = jQuery.parseJSON(data);
		if (obj)
		{
			if (obj.ShippingTime)
			{
				$("#shipping_"+orderid).html(obj.ShippingTime.date);
			}
			if (obj.DeliveryTime)
			{
				$("#delivery_"+orderid).html(obj.DeliveryTime.date);
				$("#btn_delivery_"+orderid).removeAttr("disabled");
			}
		}
	}
	function update_time(orderid, itemids)
	{
		$("#btn_delivery_"+orderid).attr("disabled","disabled");
		var vendor = $("#vendor_"+orderid).html();
		if (vendor == "EMS")
		{
			var delivery_time = $("#delivery_"+orderid).html();
			$.post("ajax_new_delivery.php?action=update_time", {oid: orderid, deliverytime: delivery_time, items: itemids, vendor: vendor}, function(data) { if (data=="OK") $("#row_"+orderid).hide(700); } );
		}
		else
		{
			var shipping_time = $("#shipping_"+orderid).html();
			var delivery_time = $("#delivery_"+orderid).html();
			$.post("ajax_new_delivery.php?action=update_time", {oid: orderid, shippingtime: shipping_time, deliverytime: delivery_time, items: itemids, vendor: vendor}, function(data) { if (data=="OK") $("#row_"+orderid).hide(700); } );
		}
	}
	function show_tracking(vendor, trackID)
	{
		switch (vendor)
		{
			case "UPS":
				//$.get("query_ups.php", {upsid: trackID}, function(data) { alert(data) } );
				break;
			case "USPS":
				//$.get("query_usps.php", {uspsid: trackID}, function(data) { alert(data) } );
				break;
			case "FEDEX":
				//$.get("query_fedex.php", {fedexid: trackID}, function(data) { alert(data) } );
				break;
		}
	}
	function clickon_ordernum(orderid, ordernum)
	{
		$("#ordernumber_"+orderid).html("<input type=\"text\" id=\"txtOrder_"+orderid+"\" name=\"txtOrder_"+orderid+"\" onkeydown=\"javascript: if (event.which==13 || event.keyCode==13) {update_ordernum('"+orderid+"', this.value)};\" onblur=\"restore_ordernum('"+orderid+"', '"+ordernum+"');\">");
		$("#txtOrder_"+orderid).val(ordernum);
		$("#txtOrder_"+orderid).focus();
		$("#txtOrder_"+orderid).select();
	}
	function update_ordernum(orderid, ordernum)
	{
		$.post("ajax_new_delivery.php?action=update_ordernum", {oid: orderid, onum: ordernum}, function(data) { if (data=="OK") $("#ordernumber_"+orderid).html(ordernum); } );
	}
	function restore_ordernum(orderid, ordernum)
	{
		$("#ordernumber_"+orderid).html(ordernum);
	}
	</script>
    <title>到货单</title>
</head>
<body onload="search();">
<div style="float:left">
	<div id="search">
	<select name="search_type" onchange="search();">
	  <option value="Transit" selected>转运</option>
	  <option value="Delivery">到货</option>
	</select>
	</div>
	<div id="order_list">
	</div>
</div>

<div id="sound"></div>
</body>
</html>


