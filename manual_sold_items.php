<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <link rel="stylesheet" href="table.css">
	<script type="text/javascript" src="script/jquery-1.11.1.min.js"></script>
	<script language="JavaScript">
	if(!Number.prototype.toFixed)
	{
		Number.prototype.toFixed = function(num) { with(Math) return round(this.valueof()*pow(10,num))/pow(10,num);}
	}
	
    function calc_price(itemid)
    {
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
    		
	function pull_taobao_order()
	{
		var orderid = $("#OrderNumber").val();
		
		
		$.get("ajax_taobao_orderinfo.php", { tid: orderid }, function(data) {pull_taobao_order_callback(data)} );

	}
	
	function pull_taobao_order_callback(data)
	{    
		obj = jQuery.parseJSON(data);
		if (obj)
		{
			if (obj.Status == "0")
			{
				$("#OrderTime").val(obj.OrderTime);
				$("#OrderTotalPayment").val(obj.OrderTotalPayment.toFixed(2));
				
				if (typeof obj.Vendor != 'undefined')
				{
					$('#Vendor option[value="'+obj.Vendor+'"').attr('selected', true);
				}
				
				if (typeof obj.TrackNum != 'undefined')
				{
					$("#TrackNum").val(obj.TrackNum);
				}
				else
				{
					var date = new Date();
					var month = date.getMonth();
					var day = date.getDate();
					if (month < 10)
					{
						month = '0'+month;
					}
					if (day < 10)
					{
						day = '0'+day;
					}
					var tn = date.getFullYear()+month+day+date.getHours()+date.getMinutes()+date.getSeconds();
					$("#TrackNum").val(tn);
				}
				
				if (typeof obj.ShippingTime != 'undefined')
				{
					$("#ShippingTime").val(obj.ShippingTime);
				}

				if (typeof obj.Weight != 'undefined')
				{
					$("#Weight").val(obj.Weight.toFixed(2));
				}
				else
				{
					$("#Weight").val("");
				}
				
				if (typeof obj.Items != 'undefined')
				{
					var linenum = $("#item_list tr").length + 1;
					for(var num in obj.Items)
					{
						add_itemline(linenum);
						$("input[name='item_"+linenum+"_qty'").val(obj.Items[num].Number);
						$("input[name='item_"+linenum+"_legoid'").val(obj.Items[num].Legoid);
						$("input[name='item_"+linenum+"_price'").val(obj.Items[num].Price.toFixed(2));
						calc_price(linenum);
						linenum++;
            		}
            	}
			}
			else
			{
				$("#OrderMessage").html(obj.Message);
			}
		}
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
	</script>
</head>
<html>
<div>
Taobao订单号：<input type="text" id="OrderNumber" name="OrderNumber" size="20" onblur="pull_taobao_order();"/><span id="OrderMessage"></span></br>
成交时间：<input type="text" id="OrderTime" name="OrderTime" size="23" /></br>
成交金额：<input type="text" id="OrderTotalPayment" name="OrderTotalPayment" size="6" /></br>
承运方：
<select id="Vendor" name="Vendor">
  <option value="ZTO">ZTO中通</option>
  <option value="SFE">SFE顺丰</option>
  <option value="SELF">自提</option>
</select></br>
运单号：<input type="text" id="TrackNum" name="TrackNum" size="18" onblur="check_shipping_orderid();"/></br>
运单重量：<input type="text" id="Weight" name="Weight" size="4" />kg</br>
发货时间：<input type="text" id="ShippingTime" name="ShippingTime" size="23" value="<?php date_default_timezone_set('Asia/Shanghai'); echo $showtime=date('Y-m-d H:i:s'); ?>"/></br>
内容列表：</br>
<table>
<thead><tr><th>数量</th><th>LEGO编号</th><th>单价</th><th>总价</th><th>操作</th></tr></thead>
<tbody id="item_list"></tbody>
</table>
</div>
</html>
<?php

?>