<html>
<head>
  <style>
body {
	font: normal 11px auto "Trebuchet MS", Verdana, Arial, Helvetica, sans-serif;
	color: #4f6b72;
	background: #E6EAE9;
}

a {
	color: #c75f3e;
}

caption {
	padding: 0 0 5px 0;
	width: 700px;	 
	font: italic 11px "Trebuchet MS", Verdana, Arial, Helvetica, sans-serif;
	text-align: right;
}

th {
	font: bold 11px "Trebuchet MS", Verdana, Arial, Helvetica, sans-serif;
	color: #4f6b72;
	border-right: 1px solid #C1DAD7;
	border-bottom: 1px solid #C1DAD7;
	border-top: 1px solid #C1DAD7;
	letter-spacing: 2px;
	text-transform: uppercase;
	text-align: left;
	padding: 6px 6px 6px 12px;
	background: #CAE8EA no-repeat;
}

th.nobg {
	border-top: 0;
	border-left: 0;
	border-right: 1px solid #C1DAD7;
	background: none;
}

td {
	border-right: 1px solid #C1DAD7;
	border-bottom: 1px solid #C1DAD7;
	background: #fff;
	padding: 6px 6px 6px 12px;
	color: #4f6b72;
}


td.alt {
	background: #F5FAFA;
	color: #797268;
}

}
  </style>
  <title>新的采购</title>
  <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
  <script type="text/javascript" src="jquery.js"></script>
  <script type="text/javascript">
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
  function refresh_setlist(type)
  {
    $.get("get_setlist.php", { type: type} , function(data) { $("#SetList").html(data);} );
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
  function default_rate()
  {
    var currency = document.getElementById("Payee").value;
    switch (currency)
    {
      case "amazon.com": case "toysrus.com": case "shop.lego.com": case "bn.com": case "yoyo.com":
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
    }
  }
  </script>
</head>

<body onload="refresh_setlist();">
<div id="SetList"></div>
<?php
  require("conn.php");
	if($_POST['action']== "submit")
	{
    $LegoID = $_POST['LegoID'];
    if ($_POST['CreateDate'] =='')
    {
      $CreateDate = date("Y-m-d");
    }
    else
    {
      $CreateDate = $_POST['CreateDate'];
    }
    
    $Expense = $_POST['ExpenseCNY'];

    $conn=mysql_connect($mysql_server_name, $mysql_username, $mysql_password) or die ("数据库错误：".mysql_error());
	mysql_query("SET NAMES UTF8", $conn);
	$strsql="INSERT INTO Inventory (`LegoID`, `CreateTime`, `Expense`, `Status`) VALUES ('$LegoID', '$CreateDate', '$Expense', 'Buy');";
	$r=mysql_db_query($mysql_database, $strsql, $conn);

	$InvID = mysql_insert_id();
    $ExpenseType = "Buy";
    $Payee = $_POST['Payee'];
    $RefID = $_POST['RefID'];
    $ExpenseAmount = $_POST['ExpenseAmount'];
    $ExpenseRate = $_POST['ExpenseRate'];
    $ExpenseCNY = $_POST['ExpenseCNY'];
    $Courier = $_POST['Courier'];
    $DeliverID = $_POST['DeliverID'];
    $ShippingDate = $_POST['ShippingDate'];
    if ($_POST['DeliverDate'] =='')
    {
      $DeliverDate = date("Y-m-d");
    }
    else
    {
      $DeliverDate = $_POST['DeliverDate'];
    }
    if ($Courier == 'NA')
    {
      $Inv_Status = "Delivered";
      $strsql = "UPDATE Expense SET `ExpenseType` = 'Delivered' WHERE `ExpID` = $InvID;";
      $r=mysql_db_query($mysql_database, $strsql, $conn);
    }
    $Memo = $_POST['Memo'];
    $strsql="INSERT INTO Expense (`InvID`, `CreateTime`, `ExpenseType`, `Payee`, `RefID`, `ExpenseAmount`, `ExpenseRate`, `ExpenseCNY`, `Courier`, `DeliverID`, `DeliverDate`, `Memo`) VALUES ($InvID, '$ShippingDate', '$ExpenseType', '$Payee', '$RefID', '$ExpenseAmount', '$ExpenseRate', '$ExpenseCNY', '$Courier', '$DeliverID', '$DeliverDate', '$Memo');";
		$r=mysql_db_query($mysql_database, $strsql, $conn);

    echo "新的采购：($InvID) $LegoID 已经添加！";
  }
?>

<form id="add_inv" method="POST" action="add_inv.php">
<p>请输入相关信息：</p>
LEGOID: <input type="text" id="LegoID" name="LegoID" onkeydown="nextfocus();" onblur="get_legoid();" /><br />
<div id="SetPreview"></div>
购买渠道: 
<select id="Payee" name="Payee" onchange="default_rate();">
  <option value="amazon.com">amazon.com</option>
  <option value="amazon.de">amazon.de</option>
  <option value="amazon.fr">amazon.fr</option>
  <option value="amazon.cn">amazon.cn</option>
  <option value="toysrus.com">ToysRus</option>
  <option value="shop.lego.com">Lego S&amp;H</option>
  <option value="walmart.com">Walmart.com</option>
  <option value="bn.com">B&amp;N</option>
  <option value="yoyo.com">YoYo</option>
  <option value="kidsland">KidsLand</option>
  <option value="360buy.com">360Buy</option>
  <option value="taobao.com">taobao.com</option>
</select><br />
订单号: <input type="text" id="RefID" name="RefID" onkeydown="nextfocus();"/><br />
当地金额：<input type="text" id="ExpenseAmount" name="ExpenseAmount" onkeydown="nextfocus();" onblur="cacl_cny();" /><br />
兑换率：
<select id="RateSelector" onchange="select_currency(this.value)" onblur="select_currency(this.value)">
  <option value="USD">USD</option>
  <option value="EUR">EUR</option>
  <option value="CNY">CNY</option>
</select>
<input type="text" id="ExpenseRate" name="ExpenseRate" onkeydown="nextfocus();" onblur="cacl_cny();" /><br />
折合人民币：<input type="text" id="ExpenseCNY" name="ExpenseCNY" onkeydown="nextfocus();" onfocus="cacl_cny();" /><br />
承运人：
<select id="Courier" name="Courier">
  <option value="NA">自提</option>
  <option value="FEDEX">FedEx</option>
  <option value="UPS">UPS</option>
  <option value="USPS">USPS</option>
  <option value="ONTRAC">ONTRAC</option>
  <option value="DHL">DHL</option>
  <option value="DHL.DE">DHL Germany</option>
  <option value="EMS">EMS</option>
  <option value="Z.CN">Z.CN</option>
  <option value="360BUY">360BUY</option>
  <option value="YTO">YTO</option>
</select><br />
运单号：<input type="text" id="DeliverID" name="DeliverID" onkeydown="nextfocus();" /><br />
下单时间：<input type="text" id="CreateDate" name="CreateDate" onkeydown="nextfocus();" /><br /><br />
发货时间：<input type="text" id="ShippingDate" name="ShippingDate" onkeydown="nextfocus();" /><br /><br />
收货时间：<input type="text" id="DeliverDate" name="DeliverDate" onkeydown="nextfocus();" /><br /><br />
其他：<textarea id="Memo" name="Memo"/></textarea><br />
<input type="hidden" name="action" value="submit">
<input type="submit">
</form>
  <?php
echo "<script type=\"text/javascript\">\r\n";

echo "$('#LegoID').val(\"".$_POST['LegoID']."\");\r\n";
echo "$('#Payee option[value=\"".$_POST['Payee']."\"]').attr('selected', true);\r\n";
echo "$('#RefID').val(\"".$_POST['RefID']."\");\r\n";
echo "$('#ExpenseAmount').val(\"".$_POST['ExpenseAmount']."\");\r\n";
echo "$('#ExpenseRate').val(\"".$_POST['ExpenseRate']."\");\r\n";
echo "$('#ExpenseCNY').val(\"".$_POST['ExpenseCNY']."\");\r\n";
echo "$('#Courier option[value=\"".$_POST['Courier']."\"]').attr('selected', true);\r\n";
echo "$('#DeliverID').val(\"".$_POST['DeliverID']."\");\r\n";
echo "$('#CreateDate').val(\"".$_POST['CreateDate']."\");\r\n";
echo "$('#ShippingDate').val(\"".$_POST['ShippingDate']."\");\r\n";
echo "$('#DeliverDate').val(\"".$_POST['DeliverDate']."\");\r\n";
echo "$('#Memo').val(\"".$_POST['Memo']."\");\r\n";

echo "</script>\r\n";
?>
</body>
</html>
