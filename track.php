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
  	$i = 1;
  	foreach ($LegoCounter as $LegoID => $Count)
  	{
  	  $ret = $ret.$LegoID."*".$Count." ";
  	  if ($i++ % 5 == 0)
  	  {
  	    $ret = rtrim($ret, " ");
  	    $ret = $ret."<br/>";
      }
  	}
  	return trim($ret);
  }
  
  date_default_timezone_set('Asia/Shanghai');
  $conn=mysql_connect($mysql_server_name, $mysql_username, $mysql_password) or die ("数据库错误：".mysql_error());
  mysql_query("SET NAMES UTF8;", $conn);
  mysql_query("SET time_zone = '+08:00';", $conn);
  
  $strsql="SELECT Status, COUNT(*) AS Count, SUM(Expense) AS Expense, SUM(Revenue)-SUM(Expense) AS Profit FROM Inventory GROUP BY Status";
  $result=mysql_db_query($mysql_database, $strsql, $conn);


  $c_buy = 0;
  $e_buy = 0;
  $c_delivered = 0;
  $e_delivered = 0;
  $c_transit = 0;
  $e_transit = 0;
  $c_stock = 0;
  $e_stock = 0;
  $c_sold = 0;
  $e_sold = 0;
  $p_sold = 0;

  while($row=mysql_fetch_array($result))
  {
    switch ($row['Status'])
    {
      case "Buy":
        $c_buy += $row['Count'];
        $e_buy += $row['Expense'];
        break;
      case "Delivered":
        $c_delivered += $row['Count'];
        $e_delivered += $row['Expense'];
        break;
      case "InTransit":
        $c_transit += $row['Count'];
        $e_transit += $row['Expense'];
        break;
      case "InStock":
        $c_stock += $row['Count'];
        $e_stock += $row['Expense'];
        break;
      case "Sold":
        $c_sold += $row['Count'];
        $e_sold += $row['Expense'];
        $p_sold += $row['Profit'];
        break;
      case "Opened":
        $c_open += $row['Count'];
        $e_open += $row['Expense'];
        break;
    }
  }

  
  $strsql="SELECT * FROM Inventory LEFT JOIN Expense ON Inventory.InvID = Expense.InvID WHERE Status = 'InTransit' AND ExpenseType = 'Postage';";
  $result=mysql_db_query($mysql_database, $strsql, $conn);
  
  $DeliverIDs = array();
  $Couriers = array();

  while($row=mysql_fetch_array($result))
  {
    $DID = $row['Courier'].":".$row['DeliverID'];
        
    $InvID = $row['InvID'];
    $Couriers[$DID] = array("Payee" => $row['Payee'], "RefID" => $row['RefID']);

    if (isset($DeliverIDs[$DID]))
    {
      $DeliverIDs[$DID][$InvID] = $row['LegoID'];
    }
    else
    {
	  $DeliverIDs[$DID] = array($InvID => $row['LegoID']);
    }
  }

  $strsql="SELECT * FROM Inventory LEFT JOIN Expense ON Inventory.InvID = Expense.InvID WHERE Status = 'Buy' AND ExpenseType = 'Buy';";
  $result=mysql_db_query($mysql_database, $strsql, $conn);
  
  $ExpressIDs = array();
  
  while($row=mysql_fetch_array($result))
  {
    $DID = $row['Courier'].":".$row['DeliverID'];
        
    $InvID = $row['InvID'];
    $Couriers[$DID] = array("Payee" => $row['Payee'], "RefID" => $row['RefID']);

    if (isset($ExpressIDs[$DID]))
    {
      $ExpressIDs[$DID][$InvID] = $row['LegoID'];
    }
    else
    {
	  $ExpressIDs[$DID] = array($InvID => $row['LegoID']);
    }
  }
  
  $strsql="SELECT * FROM Inventory LEFT JOIN Expense ON Inventory.InvID = Expense.InvID WHERE Status = 'Delivered' AND ExpenseType = 'Buy';";
  $result=mysql_db_query($mysql_database, $strsql, $conn);
  
  $DeliveredIDs = array();
  
  while($row=mysql_fetch_array($result))
  {
    $DID = $row['Courier'].":".$row['DeliverID'];
        
    $InvID = $row['InvID'];
    $Couriers[$DID] = array("Payee" => $row['Payee'], "RefID" => $row['RefID']);

    if (isset($DeliveredIDs[$DID]))
    {
      $DeliveredIDs[$DID][$InvID] = $row['LegoID'];
    }
    else
    {
	  $DeliveredIDs[$DID] = array($InvID => $row['LegoID']);
    }
  }
    
?>
<html>
<head>
  <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
  <link rel="stylesheet" href="table.css">
  <script type="text/javascript" src="http://ossweb-img.qq.com/images/js/jquery/jquery-1.7.1.min.js"></script>
  <script type="text/javascript" src="scripts.js"></script>
</head>
<body>
<p><?php echo "新购: ".$c_buy."件(¥".$e_buy.")\r\n"; ?></p>
<table>
<tr><th>单号</th><th>订单号</th><th>物品内容</th><th>当前状态</th></tr>
<?php
  foreach ($ExpressIDs as $ID => $Items)
  {
    switch ($Couriers[$ID]['Payee'])
    {
      case "amazon.com":
        $OrderUrl = "https://www.amazon.com/gp/css/summary/edit.html?orderID=".$Couriers[$ID]['RefID'];
        break;
      case "amazon.fr":
        $OrderUrl = "
        https://www.amazon.fr/gp/css/summary/edit.html?orderID=".$Couriers[$ID]['RefID'];
        break;
      case "toysrus.com":
        $OrderUrl = "https://www.toysrus.com/checkout/index.jsp?process=orderTrackingDetail&orderId=".$Couriers[$ID]['RefID'];
        break;
      case "bn.com":
        $OrderUrl = "https://cart2.barnesandnoble.com/account/op.asp?stage=orderDetail&moid=".$Couriers[$ID]['RefID'];
        break;
      default:
        $OrderUrl = "#";
    }
    if ($OrderUrl != "#")
    {
      $OrderInfo = "<a href=\"$OrderUrl\" target=\"_blank\">".$Couriers[$ID]['RefID']."</a>";
    }
    else
    {
      $OrderInfo = $Couriers[$ID]['RefID'];
    }
    $list = str_replace(" ", ",", get_Items_string($Items));
    list($Courier, $DeliverID) = split(":", $ID, 2);
    echo "<tr><td>".$ID."</td><td>".$OrderInfo."</td><td>".$list."</td><td id=\"td_".$DeliverID."\"><script>query_express_one('".$Courier."', '".$DeliverID."');</script></td></tr>\r\n";
  }
?>
</table>
<p><?php echo "已送达: ".$c_delivered."件(¥".$e_delivered.")\r\n"; ?></p>
<table>
<tr><th>单号</th><th>订单号</th><th>物品内容</th><th>当前状态</th></tr>
<?php
  foreach ($DeliveredIDs as $ID => $Items)
  {
    switch ($Couriers[$ID]['Payee'])
    {
      case "amazon.com":
        $OrderUrl = "https://www.amazon.com/gp/css/summary/edit.html?orderID=".$Couriers[$ID]['RefID'];
        break;
      case "amazon.fr":
        $OrderUrl = "
        https://www.amazon.fr/gp/css/summary/edit.html?orderID=".$Couriers[$ID]['RefID'];
        break;
      case "toysrus.com":
        $OrderUrl = "https://www.toysrus.com/checkout/index.jsp?process=orderTrackingDetail&orderId=".$Couriers[$ID]['RefID'];
        break;
      case "bn.com":
        $OrderUrl = "https://cart2.barnesandnoble.com/account/op.asp?stage=orderDetail&moid=".$Couriers[$ID]['RefID'];
        break;
      default:
        $OrderUrl = "#";
    }
    if ($OrderUrl != "#")
    {
      $OrderInfo = "<a href=\"$OrderUrl\" target=\"_blank\">".$Couriers[$ID]['RefID']."</a>";
    }
    else
    {
      $OrderInfo = $Couriers[$ID]['RefID'];
    }
    $list = str_replace(" ", ",", get_Items_string($Items));
    list($Courier, $DeliverID) = split(":", $ID, 2);
    echo "<tr><td>".$ID."</td><td>".$OrderInfo."</td><td>".$list."</td><td id=\"td_".$DeliverID."\"><script>query_express_one('".$Courier."', '".$DeliverID."');</script></td></tr>\r\n";
  }
?>
</table>

<p><?php echo "转运中: ".$c_transit."件(¥".$e_transit.")\r\n"; ?></p>
<table>
<tr><th>单号</th><th>经由</th><th>物品内容</th><th>当前状态</th></tr>
<?php
  foreach ($DeliverIDs as $ID => $Items)
  {
    list($Courier, $DeliverID) = split(":", $ID, 2);
    if ($DeliverID != $Couriers[$ID]['RefID'])
    {
        switch ($Couriers[$ID]['Payee'])
        {
          case "qq-ex.com":
            $DeliverUrl = "http://www.qq-ex.com/status/search_order/index/".$Couriers[$ID]['RefID'];
            break;
          case "thunderex.com":
            $DeliverUrl = "http://www.thunderex.com/SearchEx.aspx?ordernum=".$Couriers[$ID]['RefID'];
            break;
          case "culexpress.com":
            $DeliverUrl = "http://www.culexpress.com/CulBill.aspx?wlnum=".$Couriers[$ID]['RefID'];
            break;
          case "transparcel.com":
            $DeliverUrl = "http://www.transparcel.com/member/shipDetail.asp?T=1&slCode=".$Couriers[$ID]['RefID'];
            break;
          case "USPS":
            $DeliverUrl = "https://tools.usps.com/go/TrackConfirmAction?formattedLabel=".$Couriers[$ID]['RefID'];
            break;
          case "DHL.DE":
            $DeliverUrl = "http://nolp.dhl.de/nextt-online-public/set_identcodes.do?lang=en&idc=".$Couriers[$ID]['RefID'];
            break;
          case "tr.4px.com":
            $DeliverUrl = "http://tr.4px.com/Cart/OrderInfo.aspx?c=".$Couriers[$ID]['RefID'];
            break;
          default:
            $DeliverUrl = "#";
        }
        if ($DeliverUrl != "#")
        {
          $DeliverUrl = "<a href=\"$DeliverUrl\" target=\"_blank\">".$Couriers[$ID]['RefID']."</a>";
        }
        else
        {
          $DeliverUrl = $Couriers[$ID]['RefID'];
        }
    }
    else
    {
      $DeliverUrl = "";
    }
    $list = str_replace(" ", ",", get_Items_string($Items));

    echo "<tr><td>".$ID."</td><td>".$DeliverUrl."</td><td>".$list."</td><td id=\"td_".$DeliverID."\"><script>query_express_one('".$Courier."', '".$DeliverID."');</script></td></tr>\r\n";
  }
?>
</table>
<p>已发货</p>
<table>
<tr><th>Taobao订单号</th><th>快递单号</th><th>发货时间</th><th>在途天数</th><th>签收时间</th><th>当前状态</th></tr>
<?php
  $strsql="SELECT * FROM V_ExpressDeliverDate ORDER BY CreateTime ASC;";
  $result=mysql_db_query($mysql_database, $strsql, $conn);  
  while($row=mysql_fetch_array($result))
  {
    echo "<tr><td><a href=\"http://trade.taobao.com/trade/detail/trade_item_detail.htm?bizOrderId=".$row['RefID']."\" target=\"_blank\">".$row['RefID']."</a></td>\r\n";
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