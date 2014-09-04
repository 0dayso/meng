<html>
<head>
  <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
  <link rel="stylesheet" href="table.css">
  <script type="text/javascript" src="http://ossweb-img.qq.com/images/js/jquery/jquery-1.7.1.min.js"></script>
  <script type="text/javascript" src="scripts.js"></script>
</head>
<body>

<table>
  <tr><th></th><th>ID</th><th>名称</th><th>库存</th><th>总量</th><th>品相</th><th>库存平均进价</th><th>价格范围</th><th>20％下限</th><th>重量</th><th>历史运费</th><th>美行成本</th><th>国行成本</th><th>库存押款</th></tr>
<?php
  require("conn.php");
  
  $onlystock = $_GET["stock"];

  date_default_timezone_set('Asia/Shanghai');
  $conn=mysql_connect($mysql_server_name, $mysql_username, $mysql_password) or die ("数据库错误：".mysql_error());
  mysql_query("SET NAMES UTF8;", $conn);
  mysql_query("SET time_zone = '+08:00';", $conn);
  
  $strsql="SELECT Inventory.LegoID AS LegoID, COUNT(Expense.ExpenseCNY) AS Count, ROUND(AVG(Expense.ExpenseCNY),2) AS Postage FROM `Expense` LEFT JOIN Inventory ON Expense.InvID=Inventory.InvID WHERE ExpenseType = 'Postage' GROUP BY Inventory.LegoID";
  $result=mysql_db_query($mysql_database, $strsql, $conn);

  while($row=mysql_fetch_array($result))
  {
    $postage[$row['LegoID']] = $row['Postage'];
  }
  
  //var_dump($postage);
  
  $strsql="SELECT InvID,Inventory.LegoID AS LegoID,CreateTime,Expense,Status,ETitle,CTitle,Weight,USPrice,CNPrice FROM Inventory LEFT JOIN V_LegoSet ON Inventory.LegoID = V_LegoSet.LegoID; "; //WHERE Status='InStock';";
  $result=mysql_db_query($mysql_database, $strsql, $conn);
  $Itemlist = array();
  while($row=mysql_fetch_array($result))
  {
    $LegoID = $row['LegoID'];
    $title = $row['ETitle'];
    if ($row['CTitle'] <> '')
    {
      $title = $row['CTitle'];
    }
    $Item = array("InvID" => $row['InvID'], "Status" => $row['Status'], "Title" => $title, "USPrice" => $row['USPrice'], "CNPrice" => $row['CNPrice'], "Expense" => $row['Expense'], "BuyDate" => $row['CreateTime'], "Weight" => $row['weight']);
    if (isset($Itemlist[$LegoID]))
    {
      $Items = $Itemlist[$LegoID];
      array_push($Items, $Item);
      $Itemlist[$LegoID] = $Items;
    }
  	else
  	{
  	  $Items = array();
  	  array_push($Items, $Item);
  	  $Itemlist[$LegoID] = $Items;
  	}
  }
  ksort($Itemlist);
  foreach ($Itemlist as $LegoID => $Items)
  {
    $total_num = count($Items);

    $total = 0;
    $min = 0;
    $max = 0;
    $num = 0;
    foreach ($Items as $Item)
    {
      if ($Item['Status'] == 'InStock')
      {
        $num = $num +1;
        $total += $Item['Expense'];
      }
      if ($min == 0)
      {
        $min = $Item['Expense'];
      }
      if ($max == 0)
      {
        $max = $Item['Expense'];
      }
      if ($Item['Expense'] < $min)
      {
        $min = $Item['Expense'];
      }
      if ($Item['Expense'] > $max)
      {
        $max = $Item['Expense'];
      }
    }
    $avg = round($total / $num, 2);
    if ($min == $max)
    {
      $range = round($min,2);
    }
    else
    {
      $range = round($min,2)."~".round($max,2);
    }
    if (isset($postage[$LegoID]))
    {
      $hisfee = $postage[$LegoID];
    }
    else
    {
      $hisfee = "N/A";
    }
    $weightfee = round($Item['Weight']/453.59237*1.2*27, 2);
    $weight = round($Item['Weight']/1000, 2)."kg / ".round($Item['Weight']/453.59237, 2)."lb";
    $msrp = round($Item['USPrice']*6.4,2);
    if ($hisfee == "N/A")
    {
      $usprice = "¥".$msrp." + ".$weightfee." = ¥".round($msrp + $weightfee, 2);
    }
    else
    {
      $usprice = "¥".$msrp." + ".$hisfee." = ¥".round($msrp + $hisfee, 2);
    }
    if (($LegoID>10000) and ($LegoID<20000))
    {
      $cnprice = "¥".round($Item['CNPrice']*0.9,2)." / ".round($Item['CNPrice'],2);
    }
    else
    {
      $cnprice = "¥".round($Item['CNPrice']*0.6,2)." / ".round($Item['CNPrice'],2);
    }
    
    if ($onlystock == 1)
    {
      if ($num > 0)
      {
      	echo "<tr><td><input type=\"checkbox\" /></td><td>".$LegoID."</td><td>".$Item['Title']."</td><td>".$num."</td><td>".$total_num."</td><td><input type=\"text\" size=\"6\" /></td><td>¥".$avg."</td><td>¥".$range."</td><td>¥".round($max * 1.2)."</td><td>".$weight."</td><td>".$hisfee."</td><td>".$usprice."</td><td>".$cnprice."</td><td>¥".$total."</td></tr>";
      }
    }
    else
    {
        echo "<tr><td><input type=\"checkbox\" /></td><td>".$LegoID."</td><td>".$Item['Title']."</td><td>".$num."</td><td>".$total_num."</td><td><input type=\"text\" size=\"6\" /></td><td>¥".$avg."</td><td>¥".$range."</td><td>¥".round($max * 1.2)."</td><td>".$weight."</td><td>".$hisfee."</td><td>".$usprice."</td><td>".$cnprice."</td><td>¥".$total."</td></tr>";
    }
  }

?>
</table>
</body>
</html>