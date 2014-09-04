  function omm(oEle)
  {
    $(oEle).children().addClass('alt');
  }
  function omo(oEle)
  {
    $(oEle).children().removeClass('alt');
  }
  function input_legoid()
  {
    if (event.keyCode==13)
    {
      get_legoid();
    }
  }
  function get_legoid()
  {
    var legoid = document.getElementById("LegoID").value;
    $.get("get_legoid.php", { legoid: legoid}, function(data) { $("#SetPreview").html(data);} );
  }
  function pageup()
  {
    if (pagenum > 1)
    {
      pagenum--;
      refresh_setlist();
    }
    
  }
  function pagedown()
  {
    pagenum++;
    refresh_setlist();
  }
  function page(num)
  {
    pagenum = num;
    refresh_setlist();
  }
  function default_type()
  {
    $('#select_type option[value=' + type + ']').attr('selected', true);
  }
  function search()
  {
    type = $('#select_type').val();
    keyword = $('#select_keyword').val();
    var divname = "#SetList";
    $(divname).html('<img src="/images/loading.gif">');
    $("#InvOperation").html('');
    $("#InvDetail").html('');
    if (keyword != '')
    {
      pagenum = 1;
      $.get("get_setlist.php", { keyword: keyword, type: type, page: pagenum} , function(data) { $(divname).html(data); default_type(); } );
    }
    else
    {
      pagenum = 1;
      $.get("get_setlist.php", { type: type, page: pagenum} , function(data) { $(divname).html(data); default_type();} );
    }
    default_type();
  }
  function refresh_setlist()
  {
    var divname = "#SetList";
    $(divname).html('<img src="/images/loading.gif">');
    $.get("get_setlist.php", { type: type, page: pagenum, keyword: keyword} , function(data) { $(divname).html(data); default_type();} );
    $("#InvOperation").html('');
    $("#InvDetail").html('');
    
  }
  function cacl_cny()
  {
    var amount = document.getElementById("ExpenseAmount").value;
    var rate = document.getElementById("ExpenseRate").value;
    var total = amount * rate;

    if(!Number.prototype.toFixed)
    {
      Number.prototype.toFixed = function(num) { with(Math) return round(this.valueof()*pow(10,num))/pow(10,num);}
    }
    total = total.toFixed(2);
    document.getElementById("ExpenseCNY").value = total;
  }
  function toCurrency(oText)
  {
    var amount = 1 * oText.value;
    if(!Number.prototype.toFixed)
    {
      Number.prototype.toFixed = function(num) { with(Math) return round(this.valueof()*pow(10,num))/pow(10,num);}
    }
    amount = amount.toFixed(2);
    oText.value = amount;

  }
  function default_rate()
  {
    var currency = document.getElementById("Payee").value;
    switch (currency)
    {
      case "amazon.com": case "toysrus.com": case "shop.lego.com": case "bn.com":
      {
        select_currency("USD");
        break;
      }
      case "amazon.de": case "amazon.fr":
      {
        select_currency("EUR");
        break;
      }
      case "amazon.cn": case "kidsland":
      {
        select_currency("CNY");
        break;
      }

    }
  }
  function select_currency(currency)
  {
    for (var i=0; i <document.getElementById("RateSelector").length; i++)
    {
      with(document.getElementById("RateSelector").options[i])
      {
        if (value == currency)
        {
          selected = true;
        }
      }
    }
    switch (currency)
    {
      case "CNY":
      {
        document.getElementById("ExpenseRate").value = "1.00";
        break;
      }
      case "USD":
      {
        document.getElementById("ExpenseRate").value = "6.40";
        break;
      }
      case "EUR":
      {
        document.getElementById("ExpenseRate").value = "8.35";
        break;
      }
    }
    cacl_cny();
  }
  function nextfocus()  
  {
    var e = window.event || ev;
    var keyCode = -1;
    if (e.which == null)
    keyCode= e.keyCode;    // IE
    else if (e.which > 0)
    keyCode=e.which;    // All others
    if(keyCode==13)
    {
      e.keyCode = 9; //only for IE
      e.which = 9;
      //$(this).next('input').focus();  

    }
  }
  function highlight(oTr)
  {
    $(oTr).parent().children().children().removeClass('hl');
    $(oTr).children().addClass('hl');
  }
  function show_expense(oEle, InvID)
  {
    highlight($(oEle).parent().parent());
    get_expense(InvID);
    show_add_expense(InvID);
  }
  function show_add_expense(InvID)
  {
    var divname = "#InvOperation";
    $(divname).html('<img src="/images/loading.gif">');
    $.get("add_exp.php", { invid: InvID}, function(data) { $(divname).html(data);} );
  }
  function get_expense(InvID)
  {
    var divname = "#InvDetail";
    $(divname).html('<img src="/images/loading.gif">');
    $.get("get_exp.php", { invid: InvID}, function(data) { $(divname).html(data);} );
  }
  function del_expense(InvID, ExpID)
  {
    $.post("del_exp.php", {expid: ExpID, invid: InvID}, function(exp) { update_expense_byID(InvID, exp); get_expense(InvID); } );
  }
  function add_expense(InvID)
  {
    $.post("add_exp_submit.php", $("#add_exp").serialize(), function(exp) { update_expense_byID(InvID, exp); get_expense(InvID); show_add_expense(InvID); } );
  }
  function show_revenue(oEle, InvID)
  {
    highlight($(oEle).parent().parent());
    get_revenue(InvID);
    show_add_revenue(InvID);
  }
  function get_revenue(InvID)
  {
    var divname = "#InvDetail";
    $(divname).html('<img src="/images/loading.gif">');
    $.get("get_rev.php", { invid: InvID}, function(data) { $(divname).html(data);} );
  }
  function del_revenue(InvID, RevID)
  {
    $.post("del_rev.php", {revid: RevID, invid: InvID}, function(rev) { update_revenue_byID(InvID, rev); get_expense(InvID); } );
  }
  function add_revenue(InvID)
  {
    $.post("add_rev_submit.php", $("#add_rev").serialize(), function(rev) { update_revenue_byID(InvID, rev); get_revenue(InvID); show_add_revenue(InvID); } );
  }
  function get_list(ItemID)
  {
    var divname = "#InvOperation";
    $(divname).html('<img src="/images/loading.gif">');
    $.get("ajax_new_price.php", { itemid: ItemID}, function(data) { $(divname).html(data);} );
  }
  function show_add_revenue(InvID)
  {
    var divname = "#InvOperation";
    $(divname).html('<img src="/images/loading.gif">');
    $.get("add_rev.php", { invid: InvID}, function(data) { $(divname).html(data);} );
  }
  function show_list(oEle, InvID)
  {
    highlight($(oEle).parent().parent());
	get_list(InvID);
    $("#InvOperation").html('');
  }
  function update_expense_byID(InvID, exp)
  {
  	$('#exp_'+InvID).html(exp);
  }
  function update_revenue_byID(InvID, rev)
  {
  	$('#rev_'+InvID).html(rev);
  }
  
  function edit_status(InvID, data_status)
  {
    var divname = "#status_"+InvID;
    var options = ["Buy", "InTransit", "Delivered", "InStock", "Sold", "Opened"];
    var optionhtml = "";
    options.forEach( function (option) {
      if (option == data_status)
      {
      	optionhtml = optionhtml + '<option value="' + option + '" selected="selected">' + option + '</option>';
      }
      else
      {
      	optionhtml = optionhtml + '<option value="' + option + '">' + option + '</option>';
      }
    } );
    var edithtml = '<select id="select_' + InvID + '" onchange="update_status(' + InvID + ')" onblur="restore_status(' + InvID + ',\'' + data_status + '\')">'+ optionhtml;
  	$(divname).html(edithtml);
    var selectname = "#select_"+InvID;
    $(selectname).focus();
  }
  function update_status(InvID)
  {
    var divname = "#status_"+InvID;
    var selectname = "#select_"+InvID;
    var data_status = $(selectname + ' option:selected').val();
  	$.get("inv_status.php", { invid: InvID, status: data_status}, function(data) { $(divname).html(data);} );
  }
  function restore_status(InvID, data_status)
  {
    var divname = "#status_"+InvID;
    var edithtml = '<a href="javascript:void(0)" onclick="edit_status(' + InvID + ',\'' + data_status + '\')">' + data_status + '</a>';
    $(divname).html(edithtml);
    $(divname).parent().parent().children().removeClass('alt');

  }
  function update_expensetype(type)
  {
    switch (type)
    {
      case "Tax":
      {
        $('#th_RefID').remove();
        $('#td_RefID').remove();
        break;
      }
      case "Postage":
      {
      	$('#Payee').val("tiantian8.us");
      	$('#RateSelector').val("CNY");
      	$('#ExpenseRate').val("1.00");
      	$('#Courier').val("EMS");
        break;
      }
      case "Express":
      {
        break;
      }
    }
  }
  function edit_exp(field, expid)
  {
    var divname = field + "_" + expid;
    restorehtml = escape($("#" + divname).html());
    edithtml = "<input type=\"text\" id=\""+field+"\" name=\""+field+"\" onkeydown=\"javascript: if (event.which==13 || event.keyCode==13) {update_exp('"+field+"', "+expid+", this.value)};\" onblur=\"restore_exp('"+field+"', "+expid+", '"+ restorehtml +"');\"/>";
    $("#" + divname).html(edithtml);
    $("#" + field).focus();
  }
  function update_exp(field, expid, value)
  {
    $.get("edit_exp.php", { expid: expid, field: field, value: value}, function(data) { $("#"+field+"_"+expid).html(data); } );
  }
  function restore_exp(field, expid, html)
  { 
  	var divname = field + "_" + expid;
  	$("#" + divname).html(decodeURIComponent(html));
  }
  function query_express_one(courier, trackID)
  {
    var tdname = "#td_" + trackID;
    $(tdname).html('<img src="/images/loading.gif">');
  	switch (courier)
  	{
  		case "EMS.TX": case "EMS":
  			$.get("query_ems.php", {r: "oneline", emsid: trackID}, function(data) { $(tdname).html(data); } );
  			break;
  		case "UPS":
  			$.get("query_ups.php", {r: "oneline", upsid: trackID}, function(data) { $(tdname).html(data); } );
  			break;
  		case "FEDEX":
  			$.get("query_fedex.php", {r: "oneline", fedexid: trackID}, function(data) { $(tdname).html(data); } );
  			break;
  	    case "YTO":
  			$.get("query_yto.php", {r: "oneline", ytoid: trackID}, function(data) { $(tdname).html(data); } );
  			break;
  	    case "STO":
  			$.get("query_sto.php", {r: "oneline", stoid: trackID}, function(data) { $(tdname).html(data); } );
  			break;
  		default:
  		    $(tdname).html('');
  	}
  }
function get_datetime()
{
	var myDate = new Date();
	myDate.getYear();       //获取当前年份(2位)
	myDate.getFullYear();   //获取完整的年份(4位,1970-????)
	myDate.getMonth();      //获取当前月份(0-11,0代表1月)
	myDate.getDate();       //获取当前日(1-31)
	myDate.getDay();        //获取当前星期X(0-6,0代表星期天)
	myDate.getTime();       //获取当前时间(从1970.1.1开始的毫秒数)
	myDate.getHours();      //获取当前小时数(0-23)
	myDate.getMinutes();    //获取当前分钟数(0-59)
	myDate.getSeconds();    //获取当前秒数(0-59)
	myDate.getMilliseconds();   //获取当前毫秒数(0-999)
	var mytime = myDate.toLocaleString();
	return mytime;
}
