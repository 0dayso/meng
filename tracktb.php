<?php
  require("conn.php");
  

  function get_Items_string($Items)
  {
    $LegoCounter = array();
  	foreach ($Items as $ID => $LegoID)
  	{
  	  //$LegoID = $LegoInfo['LegoID'];
  		if (isset($LegoCounter[$LegoID]))
  		{
  		  $LegoCounter[$LegoID] = $LegoCounter[$LegoID] +1;
  		}
  		else
  		{
  		  $LegoCounter[$LegoID] = 1;
  		}
  	}
  	asort($LegoCounter);
  	$ret = "";
  	foreach ($LegoCounter as $LegoID => $Count)
  	{
  	  $ret = $ret.$LegoID."*".$Count." ";
  	}
  	return trim($ret);
  }
  
  date_default_timezone_set('Asia/Shanghai');
  $conn=mysql_connect($mysql_server_name, $mysql_username, $mysql_password) or die ("数据库错误：".mysql_error());
  mysql_query("SET NAMES UTF8;", $conn);
  mysql_query("SET time_zone = '+08:00';", $conn);
  
  $strsql="SELECT * FROM V_ExpressDeliverDate ORDER BY CreateTime ASC";
  $result=mysql_db_query($mysql_database, $strsql, $conn);
?>
<html>
<head>
  <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
  <link rel="stylesheet" href="table.css">
  <script type="text/javascript" src="http://ossweb-img.qq.com/images/js/jquery/jquery-1.7.1.min.js"></script>
  <script type="text/javascript" src="scripts.js"></script>
</head>
<table>
<tr><th>Taobao订单号</th><th>快递单号</th><th>发货时间</th><th>在途天数</th><th>签收时间</th><th>当前状态</th></tr>
<?php
  
  while($row=mysql_fetch_array($result))
  {
    echo "<tr><td><a href=\"http://wuliu.taobao.com/user/order_detail_new.htm?trade_id=".$row['RefID']."\" target=\"_blank\">".$row['RefID']."</a></td>\r\n";
    $DID = $row['Courier'].":".$row['DeliverID'];
    echo "<td>".$DID."</td>\r\n";
	echo "<td>".$row['CreateTime']."</td>\r\n";
	$days = intval((time()-strtotime($row['CreateTime']))/86400);
	echo "<td>".$days."</td>\r\n";
    echo "<td id=\"DeliverDate_".$row['ExpID']."\"><a href=\"javascript:void(0)\" onclick=\"edit_exp('DeliverDate', ".$row['ExpID'].")\">0000-00-00</a></td>\r\n";
	list($Courier, $DeliverID) = split(":", $DID, 2);
	echo "<td id=\"td_".$DeliverID."\"><script>query_express_one('".$Courier."', '".$DeliverID."');</script></td></tr>\r\n";    
  }
?>
</table>
</body>
</html>