<html>
<head>
  <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
  <link rel="stylesheet" href="table.css">
  <script type="text/javascript" src="http://ossweb-img.qq.com/images/js/jquery/jquery-1.7.1.min.js"></script>
  <script type="text/javascript" src="scripts.js"></script>
</head>
<body>
<?php
  require("conn.php");
  
  $month = $_GET["month"];
  $curmonth = date("m");
  if ($month == "")
  {
    $month = $curmonth;
  }

  date_default_timezone_set('Asia/Shanghai');
  $conn=mysql_connect($mysql_server_name, $mysql_username, $mysql_password) or die ("数据库错误：".mysql_error());
  mysql_query("SET NAMES UTF8;", $conn);
  mysql_query("SET time_zone = '+08:00';", $conn);
  $strsql="SELECT Location,Status, COUNT(*) AS Count, ROUND(-SUM(Expense),2) AS Expense, ROUND(SUM(Revenue)+SUM(Expense),2) AS Profit FROM PSS_Item GROUP BY Status,Location";
  $result=mysql_db_query($mysql_database, $strsql, $conn);


  $c_buy = 0;
  $e_buy = 0;
  $c_delivered = 0;
  $e_delivered = 0;
  $c_ptransit = 0;
  $e_ptransit = 0;
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

      case "InStock":
      	if (preg_match('/LEMO-/', $row['Location']))
      	{
			$c_stock += $row['Count'];
			$e_stock += $row['Expense'];
        }
        else
        {
        	$c_ptransit += $row['Count'];
        	$e_ptransit += $row['Expense'];
        }
        break;
        
      case "InTransit":
        if (preg_match('/LEMO-/', $row['Location']))
		{
			$c_transit += $row['Count'];
			$e_transit += $row['Expense'];
		}
		else
		{
			$c_delivered += $row['Count'];
        	$e_delivered += $row['Expense'];
		}
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

  echo "<h4>新购:".$c_buy."件(¥".$e_buy.")\r\n";
  echo "发货中:".$c_delivered."件(¥".$e_delivered.")\r\n";
  echo "待转运:".$c_ptransit."件(¥".$e_ptransit.")\r\n";
  echo "转运中:".$c_transit."件(¥".$e_transit.")\r\n";
  echo "库存:".$c_stock."件(¥".$e_stock.")\r\n";
  echo "总库存含在途:".($c_buy+$c_delivered+$c_ptransit+$c_transit+$c_stock)."件(¥".($e_buy+$e_delivered+$e_ptransit+$e_transit+$e_stock).")\r\n";
  echo "已售:".$c_sold."件(¥".$e_sold.", ¥".$p_sold.")\r\n";
  echo "不记账:".$c_open."件(¥".$e_open.")\r\n</h4>";

  if ($curmonth < $month)
  {
    $strsql="SELECT * FROM PSS_SOrder WHERE PSS_SOrder.OrderTime >= DATE(CONCAT(YEAR(CURDATE())-1,'-".$month."-01')) AND PSS_SOrder.OrderTime < DATE_ADD(DATE(CONCAT(YEAR(CURDATE())-1,'-".$month."-01')), INTERVAL 1 MONTH) ORDER BY PSS_SOrder.OrderTime DESC";
  }
  else
  {
    $strsql="SELECT * FROM PSS_SOrder WHERE PSS_SOrder.OrderTime >= DATE(CONCAT(YEAR(CURDATE()),'-".$month."-01')) AND PSS_SOrder.OrderTime < DATE_ADD(DATE(CONCAT(YEAR(CURDATE()),'-".$month."-01')), INTERVAL 1 MONTH) ORDER BY PSS_SOrder.OrderTime DESC";
  }
  $result=mysql_db_query($mysql_database, $strsql, $conn);

  $total_expense = 0;
  $total_revenue = 0;
  $total_profit = 0;
  $total_num = 0;

  $strTable = "<table><tr><th>日期</th><th>订单号</th><th>买方</th><th>地址</th><th>LegoID</th><th>成本</th><th>销售</th><th>利润</th><th>利润率</th></tr>";
  while($row=mysql_fetch_array($result))
  {
  	
  	$strsqlitem = "SELECT PSS_Item.ItemID AS ItemID,LegoID,Expense,Revenue FROM PSS_Price INNER JOIN PSS_Item ON PSS_Price.ItemID = PSS_Item.ItemID WHERE PSS_Price.Type='Sold' AND PSS_Price.LinkID='".$row['SOrderID']."';";
  	$resultitem = mysql_db_query($mysql_database, $strsqlitem, $conn);
  	
  	$itemnum = mysql_num_rows($resultitem);
  	if ($itemnum > 1)
  	{
  		$strTable .= "<tr><td rowspan=\"".$itemnum."\">".$row['OrderTime']."</td><td rowspan=\"".$itemnum."\"><a href=\"http://trade.taobao.com/trade/detail/trade_item_detail.htm?bizOrderId=".$row['OrderNumber']."\">".$row['OrderNumber']."</a></td><td rowspan=\"".$itemnum."\">".$row['Buyer']."</td><td rowspan=\"".$itemnum."\">".$row['BuyerInfo']."</td>";
	}
	else
	{
		$strTable .= "<tr><td>".$row['OrderTime']."</td><td><a target=\"_blank\" href=\"http://trade.taobao.com/trade/detail/trade_item_detail.htm?bizOrderId=".$row['OrderNumber']."\">".$row['OrderNumber']."</a></td><td>".$row['Buyer']."</td><td>".$row['BuyerInfo']."</td>";
	}

  	if ($rowitem=mysql_fetch_array($resultitem))
  	{
  		$expense = -1*floatval($rowitem['Expense']);
  		$revenue = floatval($rowitem['Revenue']);
  		$profit = round($revenue-$expense, 2);
  		$total_profit += $profit;
    	$total_revenue += $revenue;
    	$total_expense += $expense;
  		$total_num++;
  		if (floatval($rowitem['Expense']) == 0)
  		{
  			$profitrate = 100;
  		}
  		else
  		{
  			$profitrate = round($profit/(-1*$rowitem['Expense'])*100, 2);
  		}
  		$strTable .= "<td><a href=\"javascript:void(".$rowitem['ItemID'].");\" onclick=\"show_list(this, ".$rowitem['ItemID'].");\">".$rowitem['LegoID']."</a></td><td>".$expense."</td><td>".$revenue."</td><td>".$profit."</td><td>".$profitrate."%</td>";
  	}
  	$strTable .= "</tr>";
  	while ($rowitem=mysql_fetch_array($resultitem))
  	{
  		$expense = -1*floatval($rowitem['Expense']);
  		$revenue = floatval($rowitem['Revenue']);
  		$profit = round($revenue-$expense, 2);
  		$total_profit += $profit;
    	$total_revenue += $revenue;
    	$total_expense += $expense;
  		$total_num++;
  		if (floatval($rowitem['Expense']) == 0)
  		{
  			$profitrate = 100;
  		}
  		else
  		{
  			$profitrate = round($profit/(-1*$rowitem['Expense'])*100, 2);
  		}
  		$strTable .= "<tr><td><a href=\"javascript:void(".$rowitem['ItemID'].");\" onclick=\"show_list(this, ".$rowitem['ItemID'].");\">".$rowitem['LegoID']."</a></td><td>".$expense."</td><td>".$revenue."</td><td>".$profit."</td><td>".$profitrate."%</td></tr>";
  	}
  	
  	/*
    if ($row['Amount'] == $row['Revenue'])
    {
      $profit = sprintf("%01.2f", $row['Revenue'] - $row['Expense']);
      $total = $total + $profit;
      $total_revenue = $total_revenue + $row['Revenue'];
      $profit = $profit." (".sprintf("%01.2f", $profit/$row['Expense']*100)."%)";

    }
    else
    {
      $profit = sprintf("%01.2f", $row['Revenue'] - $row['Expense']);
      $total_eta = $total_eta + $profit;
      $total_revenue = $total_revenue + $row['Amount'];
      $profit = "~".$profit." (~".sprintf("%01.2f", $profit/$row['Amount']*100)."%)";
    }
    $count++;
    
    $strTable = $strTable . "<tr><td><a href=\"javascript:void(".$row['InvID'].");\" onclick=\"show_list(this, ".$row['InvID'].");\">".$row['LegoID']."</a></td><td>".$row['TransactionTime']."</td><td><a href=\"http://trade.taobao.com/trade/detail/trade_item_detail.htm?bizOrderId=".$row['TransactionID']."\" target=\"_blank\">".$row['TransactionID']."</a></td><td>".$profit."</td><td>".$row['PayeeID']."</td><td>".$row['Memo']."</td></tr>";
    */
  }
  $strTable .= "</table>";
  echo "<h3>".$month."月销售: ".$total_num."件 (销售额: ¥".sprintf("%01.2f", $total_revenue).", 利润: ¥".sprintf("%01.2f", $total_profit).", 利润率: ".sprintf("%01.2f", ($total_profit/$total_expense)*100)."%)</h3>";
  echo $strTable;
?>
<div id="InvOperation"></div>
</body>
</html>