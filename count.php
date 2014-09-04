<html>
<head>
  <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
  <link rel="stylesheet" href="table.css">
  <script type="text/javascript" src="http://ossweb-img.qq.com/images/js/jquery/jquery-1.7.1.min.js"></script>


	<script language="JavaScript">
	var i=0;//行的id 
	//var num;
	//var tdname;
	//var unit; 
	//var list= new Array(); 
	//var numList=new Array();
	//var flag=true;
	
	function query_barcode()  
	{
		var barcode = document.getElementById("barcode").value; 

		var e = window.event || ev;
		var keyCode = -1;
		if (e.which == null)
		keyCode= e.keyCode;    // IE
		else if (e.which > 0)
		keyCode=e.which;    // All others
		if(keyCode==13)
		{
			add_row(barcode);
		}
	}
	function add_row(barcode)
	{
		var tr=document.createElement("tr");
		var linenum = ++i; 
		tr.id="count_"+linenum;
		
		var td0=document.createElement("td");
		td0.innerHTML=linenum;
		tr.appendChild(td0); 
		
		var td1=document.createElement("td");
		td1.id="legoid_"+linenum;
		$.get("ajax_bc.php", { a: "query", bc: barcode}, function(data) {legoid_callback(td1.id, data);} );
		tr.appendChild(td1);
		
		var td2=document.createElement("td");
		td2.innerHTML=barcode;
		tr.appendChild(td2);

		var td3=document.createElement("td");
		td3.innerHTML="<input type=\"button\" value=\"del\" onclick=\"del_row(this.parentElement.parentElement,this.parentElement.parentElement.id)\"/>";
		tr.appendChild(td3);
		
		document.getElementById("countlog").innerHTML = tr.outerHTML + document.getElementById("countlog").innerHTML;

	}
	function del_row(src,index)
	{ 
		var tr = document.getElementById(index); 
		tr.parentNode.removeChild(tr);
		var legoid = tr.cells[1].innerText;
		del_inventory(legoid);
		init_barcode();
	} 
	function legoid_callback(tdid, legoid)
	{
		if (legoid == "Unknown barcode")
		{
			$("#sound").html("<embed src=\"/images/failed.wav\" autostart=\"true\" hidden=\"true\" loop=\"false\">");
		}
		else
		{
			$("#sound").html("<embed src=\"/images/done.wav\" autostart=\"true\" hidden=\"true\" loop=\"false\">");
			var tdname = "#"+tdid;
			$(tdname).html(legoid);
			add_inventory(legoid);
		}
		init_barcode();
	}
	function init_barcode()
	{
		var txtBarcode = document.getElementById("barcode");
		txtBarcode.select();
		txtBarcode.focus();
	}
	function add_inventory(legoid)
	{
		var tdname = "#invcount_"+legoid;
		if ($(tdname).html())
		{
			var s = parseInt($("#stock_"+legoid).html());
			var n = parseInt($(tdname).html());
			var count = n + 1;
			$(tdname).html(count);
			if (count == s)
			{
				$(tdname).css("color","blue");
			}
			else
			{
				$(tdname).css("color","red");
			}
		}
		else
		{
			var invHtml = $("#countresult").html();
			invHtml = "<tr><td>"+legoid+"</td><td id=\"stock_"+legoid+"\"></td><td id=\"invcount_"+legoid+"\">1</td></tr>" + invHtml;
			$("#countresult").html(invHtml);
			$(tdname).css("color","red");
			$.get("ajax_stock.php", { legoid: legoid}, function(count) {$("#stock_"+legoid).html(count); if (count == 1) $(tdname).css("color","blue");} );
		}
	}
	function del_inventory(legoid)
	{
		var tdname = "#invcount_" + legoid;
		var s = parseInt($("#stock_"+legoid).html());
		var n = parseInt($(tdname).html());
		var count = n - 1;
		$(tdname).html(count);
		if (count == s)
		{
			$(tdname).css("color","blue");
		}
		else
		{
			$(tdname).css("color","red");
		}
		if (count == 0)
		{
			var tr = document.getElementById("invcount_"+legoid).parentNode;
			tr.parentNode.removeChild(tr);
		}
		
	}
	</script>
</head>
<body onload="init_barcode();">

<div style="width:98%; margin:0 auto; overflow:auto; _display:inline-block;"> 
    <div style="width:300px; float:left">
		请扫描条码：<input type="text" id="barcode" name="barcode" onkeydown="query_barcode();" /><br />
		<div>
			<p>统计：</p>
			<table>
				<tr><th>LEGO ID</th><th>库存数量</th><th>已点数</th></tr>
				<tbody id="countresult"></tbody>
			</table>
		</div>
    </div> 
    <div style="margin-left:310px">
    	<div>
			<p>输入纪录：</p>
			<table>
				<tr><th>ID</th><th>LEGO ID</th><th>Barcode</th><th>操作</th></tr>
				<tbody id="countlog"></tbody>
			</table>
		</div>

    </div> 
</div> 
<div id="sound">
</div>
</body>
</html>
