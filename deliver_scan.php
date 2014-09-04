<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<link rel="stylesheet" href="table.css">
	<script type="text/javascript" src="http://ossweb-img.qq.com/images/js/jquery/jquery-1.7.1.min.js"></script>
	<script language="JavaScript">
	function keydown_barcode()  
	{
		var e = window.event || ev;
		var keyCode = -1;
		if (e.which == null)
		keyCode= e.keyCode;    // IE
		else if (e.which > 0)
		keyCode=e.which;    // All others
		if(keyCode==13 || keyCode==9)
		{
			fetch_deliverid();
		}
	}
	function keydown_fee()  
	{
		var e = window.event || ev;
		var keyCode = -1;
		if (e.which == null)
		keyCode= e.keyCode;    // IE
		else if (e.which > 0)
		keyCode=e.which;    // All others
		if(keyCode==13 || keyCode==9)
		{
			calc_fee();
			//$("#sub_button").select();
			
		}
	}
	function fetch_deliverid()
	{
		var barcode = $("#barcode").val(); 
		$.get("fetch_deliverid.php", { did: barcode}, function(data) {deliverid_callback(data);} );
	}
	function calc_fee()
	{
		var fee = 1 * $("#fee").val();
		if(!Number.prototype.toFixed)
		{
			Number.prototype.toFixed = function(num) { with(Math) return round(this.valueof()*pow(10,num))/pow(10,num);}
		}
		$("#fee").val(fee.toFixed(2));
		var itemnum =  $("#itemnum").val();
		var total_payment = $("#total_payment").text();
		var input_postfee = $("#fee").val();
		for (var i=1; i <=itemnum; i++)
		{
			var price = $("#item_price_"+i).val();
			var payment = $("#item_payment_"+i).val();
			var fee = $("#item_fee_"+i).val();
			var postfee = payment/total_payment*input_postfee;
			$("#item_postfee_"+i).val(postfee.toFixed(2));
			var saleprice = payment - fee - postfee;
			$("#item_rev_"+i).val(saleprice.toFixed(2));
		}
		//$("#sub_button").focus();
	}
	function submit_click()
	{
		$("#sub_button").attr("disabled","disabled");

		var mydata = {};


		var itemnum = $("#itemnum").val();
		mydata['orderid'] = $("#orderid").text();
		mydata['vendor'] = $("#vendor").val();
		mydata['deliverid'] = $("#barcode").val();
		mydata['itemnum'] = itemnum;

		for (var i=1; i <=itemnum; i++)
		{
			mydata['legoid_'+i] = $("#item_legoid_"+i).val();
			mydata['payment_'+i] = $("#item_payment_"+i).val();
			mydata['fee_'+i] = $("#item_fee_"+i).val();
			mydata['postfee_'+i] = $("#item_postfee_"+i).val();
		}
		$.post("ajax_solditem.php",mydata,function(data){alert(data);});

		$("#barcode").val("");
		$("#fee").val("");
		$("#orderlist").html("");
		$("#barcode").focus();
		
		
	}
	function deliverid_callback(data)
	{
		if (data == "无对应订单！")
		{
			$("#sound").html("<embed src=\"/images/failed.wav\" autostart=\"true\" hidden=\"true\" loop=\"false\">");
			$("#orderlist").html(data);
			$("#fee").val("");
		    $("#barcode").select();
		    $("#barcode").focus();
		    $("#sub_button").attr("disabled","disabled");

		}
		else
		{
			$("#sound").html("<embed src=\"/images/done.wav\" autostart=\"true\" hidden=\"true\" loop=\"false\">");
			$("#orderlist").html(data);
			$("#fee").val("");
			$("#fee").focus();
			$("#sub_button").removeAttr("disabled");
		}
	}
	</script>
</head>
<body>

<div style="width:98%; margin:0 auto; overflow:auto; _display:inline-block;"> 
    <div style="width:100%; float:left">
		<div>
		快递单条码：<input type="text" id="barcode" name="barcode" size="15" onkeydown="keydown_barcode();" autofocus/><br />
		运费：<input type="text" id="fee" name="fee" size="5" onkeydown="keydown_fee();" /><br />
		</div>
		<div id="orderlist">
		</div>
		<button type="button" id="sub_button" onclick="submit_click();" disabled>提交</button>
	</div>
    <div style="margin-left:110px">
    </div> 
</div> 
<div id="sound">
</div>

</body>
</html>
