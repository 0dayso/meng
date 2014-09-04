<html>
<head>
  <title>整单新建</title>
  <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
  <link rel="stylesheet" href="table.css">
  <script type="text/javascript" src="http://ossweb-img.qq.com/images/js/jquery/jquery-1.7.1.min.js"></script>
  <script type="text/javascript" src="scripts.js"></script>
</head>
<body style="margin-left:auto;margin-right:auto">
<form id="new_inv" method="POST">

购买渠道: 
<select id="Payee" name="Payee" onchange="default_rate();">
  <option value="amazon.com">amazon.com</option>
  <option value="amazon.de">amazon.de</option>
  <option value="amazon.fr">amazon.fr</option>
  <option value="amazon.cn">amazon.cn</option>
  <option value="toysrus.com">ToysRus</option>
  <option value="shop.lego.com">Lego S&amp;H</option>
  <option value="bn.com">B&amp;N</option>
  <option value="yoyo.com">YOYO</option>
  <option value="kidsland">KidsLand</option>
  <option value="360buy.com">360Buy</option>
  <option value="taobao.com">taobao.com</option>
</select><br />
订单号: <input type="text" id="RefID" name="RefID" style="width:200px" onkeydown="nextfocus();"/><br />
下单时间：<input type="text" name="CreateDate" onkeydown="nextfocus();" /><br /><br />
承运人：
<select id="Courier" name="Courier">
  <option value="NA">自提</option>
  <option value="FEDEX">FedEx</option>
  <option value="UPS">UPS</option>
  <option value="USPS">USPS</option>
  <option value="ONTRAC">ONTRAC</option>
  <option value="DHL">DHL</option>
  <option value="DHL.DE">DHL Germany</option>
  <option value="STO">STO</option>
  <option value="Others">其他</option>
</select><br />
运单号：<input type="text" name="DeliverID" style="width:200px" onkeydown="nextfocus();" /><br />
发货时间：<input type="text" name="ShippingDate" onkeydown="nextfocus();" /><br /><br />
收货时间：<input type="text" name="DeliverDate" onkeydown="nextfocus();" /><br /><br />
其他：<textarea name="Memo" style="width:600px"/></textarea><br />


<table>
<tr>
  <th>LegoID</th>
  <th>当地金额</th>
  <th style="width:40px" >汇率</th>
  <th style="width:40px">折合人民币</th>
</tr>
<tr>
  <td><input type="text" id="LegoID" name="LegoID" style="width:40px" onkeydown="nextfocus();" onblur="get_legoid();" /></td>
  <td><input type="text" id="ExpenseAmount" name="ExpenseAmount" style="width:60px" onkeydown="nextfocus();" onblur="cacl_cny();" /></td>
  <td>
  	<select id="RateSelector" onchange="select_currency(this.value)" style="width:30px onblur="select_currency(this.value)">
  	  <option value="USD">USD</option>
      <option value="EUR">EUR</option>
      <option value="CNY">CNY</option>
    </select>
    <input type="text" id="ExpenseRate" name="ExpenseRate" style="width:40px onkeydown="nextfocus();" onblur="cacl_cny();" />
  </td>
  <td><input type="text" id="ExpenseCNY" name="ExpenseCNY" style="width:60px onkeydown="nextfocus();" onfocus="cacl_cny();" /></td>
</tr>
</table>
<input type="hidden" name="action" value="submit">
<input type="submit">
</form>
</body>
</html>
