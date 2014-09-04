<?php
	require("conn.php");
	$InvID = $_GET["invid"];
	date_default_timezone_set('Asia/Shanghai');
	$conn=mysql_connect($mysql_server_name, $mysql_username, $mysql_password) or die ("数据库错误：".mysql_error());
	mysql_query("SET NAMES UTF8;", $conn);
	mysql_query("SET time_zone = '+08:00';", $conn);
	
	$strsql="SELECT * FROM Revenue WHERE InvID = ".$InvID.";";
	$result=mysql_db_query($mysql_database, $strsql, $conn);

  while($row=mysql_fetch_array($result))
	{
		$rows[]=$row;
	}

?>
  <table style="text-align:center">
		<tr>
      <th></th>
      <th>交易日期</th>
      <th>成交金额</th>
      <th>交易号</th>
      <th>旺旺ID</th>
      <th>备注</th>
		</tr>
<?php
  foreach($rows as $line) 
  {
    $strTransactionID = "<a href=\"http://trade.taobao.com/trade/detail/trade_item_detail.htm?bizOrderId=".$line['TransactionID']."\" target=\"_blank\">".$line['TransactionID']."</a>";
?>
      <tr>
        <td>
          <a href="javascript:void(edit=<?php echo $line['RevID']; ?>)" onclick="edit_revenue(<?php echo $line['RevID']; ?>);"><img src="/images/edit_small.png"></a>
          <a href="javascript:void(del=<?php echo $line['RevID']; ?>)" onclick="del_revenue(<?php echo $line['InvID'].",".$line['RevID']; ?>);"><img src="/images/delete_small.png"></a>
        </td>
        <td><?php list($date, $time) = split(" ", $line['CreateTime']); echo $date; ?></td>
        <td><?php echo sprintf("%01.2f", $line['Amount']); ?></td>
        <td><?php echo $strTransactionID; ?></td>
        <td><?php echo $line['PayeeID']; ?></td>
        <td><?php echo $line['Memo']; ?></td>
      </tr>
<?php
  }
?>
  </table>
  
  