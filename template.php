<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
</head>
<body>

<form action='' method='get'>
<p>请输入LegoID：</p>
<input type="text" name="legoid" /> 
<input type="submit" value="生成"> 
</form>

	<h3>模版生成：</h3>
<div id="main" width="708px">
<textarea rows="30" cols="80">
<?php
$mysql_server_name="u01.netjsp.com";
$mysql_username="za00000";
$mysql_password="usKc5cip";
$mysql_database="za00000";

	$legoid=$_GET["legoid"];
	date_default_timezone_set('Asia/Shanghai');

	$conn=mysql_connect($mysql_server_name, $mysql_username, $mysql_password) or die ("数据库错误：".mysql_error());
	mysql_query("SET NAMES UTF8;", $conn);
	mysql_query("SET time_zone = '+08:00';", $conn);

	$strsql="SELECT * FROM legoset WHERE legoid = '$legoid'";
	$result=mysql_db_query($mysql_database, $strsql, $conn);
	$query=mysql_fetch_array($result);

    $title=$query['title'];
    $CNtitle=$query['CNtitle'];
    
    echo "乐高㊣ LEGO $legoid 系列 $CNtitle 北京现货\r\n";
?>

<p>
	<img align="absMiddle" src="http://img02.taobaocdn.com/imgextra/i2/12352442/T2Q1aiXfxXXXXXXXXX_!!12352442.gif" /></p>
<p>
	<span style="font-family:microsoft yahei;color:#ff9900;font-size:18.0px;font-weight:bold;">北京现货，可自提~~</span></p>
<p>
	----------------------------------------------------------------------------------------------------------------------</p>
<p>
	<span style="font-family:microsoft yahei;font-size:18.0px;font-weight:bold;">商品名称：<?php echo $query['title']; ?> / <?php echo $query['CNtitle']; ?><br />
	商品系列：系列<br />
	商品型号：<?php echo $query['legoid']; ?><br />
	商品品牌：丹麦品牌LEGO乐高<br />
	适合年龄：5-12岁<br />
	主要材质：环保塑胶类<br />
	商品保养：可清洗消毒<br />
	颗 粒 数：<?php echo $query['pieces']; ?><br />
	人 仔 数：<br />
	专柜价格：<?php echo $query['CNPrice']; ?></span></p>
<p>
	<span style="font-family:microsoft yahei;color:#4667a4;font-size:16.0px;font-weight:bold;"></span></p>
<p>
	<span style="font-family:microsoft yahei;font-size:14.0px;"><br />
    <br />
    <br /></span></p>

</textarea>
</div>
</body>
</html>
