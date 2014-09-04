<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <link rel="stylesheet" href="table.css">
	<script type="text/javascript" src="http://ossweb-img.qq.com/images/js/jquery/jquery-1.7.1.min.js"></script>
	<script type="text/javascript" src="scripts.js"></script>
	<script language="JavaScript">
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
	</script>
    <title>入库单</title>
</head>
<body>
<span>购买渠道: 
<select id="Seller" name="Seller" onchange="default_rate();">
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
</select></span>
<span>帐号：
<select id="Buyer" name="Buyer">
  <option value="hoker_long@sina.com">hoker_long@sina.com</option>
  <option value="hoker.long@qq.com">hoker.long@qq.com</option>
  <option value="hoker.long@gmail.com">hoker.long@gmail.com</option>
  <option value="lana@live.com">lana@live.com</option>
</select></span>
<span>支付方式：
<select id="Payby" name="Payby">
  <option value="****6674">COMM Master(6674)</option>
  <option value="****6169">SPDB Visa(6169)</option>
</select></span>
<br />
<span>下单时间：<input type="text" size="23" id="Datetime" name="Datetime" value="<?php date_default_timezone_set('Asia/Shanghai'); echo $showtime=date('Y-m-d H:i:s'); ?>"/></span>
<span>
<select id="Timezone" name="Timezone" >
  <option value="+8">CST</option>
  <option value="-8">PST</option>
  <option value="-12">EST</option>
</select>
</span>
<br />
<span>订单号：<input type="text" size="20" name="ordernum" /></span>
<span>总金额：<input type="text" size="7" disabled/>
<select id="Timezone" name="Timezone" >
  <option value="1">CNY</option>
  <option value="6.2">USD</option>
  <option value="8.35">EUR</option>
</select></span>
<span>=<input type="text" size="7" disabled/></span>

<table>
<tr><th>数量</th><th>LEGO编号</th><th>单价</th><th>总价</th><th>操作</th><tr>
<tr><td><input type="text" size="1" name="item_1_qlt" /></td><td><input type="text" size="6" name="item_1_legoid" /></td><td><input type="text" size="5" name="item_1_price" /></td><td><input type="text" size="5" name="item_1_sum" /></td><td><input type="button" name="item_1_add" value="+"/></td></tr>

</body>
</html>