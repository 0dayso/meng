<?php
	require("conn.php");
  $InvID = $_GET["invid"];
	date_default_timezone_set('Asia/Shanghai');
	$conn=mysql_connect($mysql_server_name, $mysql_username, $mysql_password) or die ("数据库错误：".mysql_error());
	mysql_query("SET NAMES UTF8;", $conn);
	mysql_query("SET time_zone = '+08:00';", $conn);

  $strsql="SELECT * FROM Expense WHERE InvID = ".$InvID.";";
	$result=mysql_db_query($mysql_database, $strsql, $conn);

  while($row=mysql_fetch_array($result))
	{
		$rows[]=$row;
	}

?>
  <table style="text-align:center">
		<tr>
      <th></th>
      <th>类型</th>
      <th>支出日期</th>
      <th>收款人</th>
      <th>参考ID</th>
      <th>金额</th>
      <th>承运方</th>
      <th>送抵日期</th>
      <th>备注</th>
		</tr>
<?php
	foreach($rows as $line) 
	{
    switch ($line['Payee'])
    {
      case "amazon.com":
        $OrderUrl = "https://www.amazon.com/gp/css/summary/edit.html?orderID=".$line['RefID'];
        break;
      case "amazon.fr":
        $OrderUrl = "
        https://www.amazon.fr/gp/css/summary/edit.html?orderID=".$line['RefID'];
        break;
      case "toysrus.com":
        $OrderUrl = "https://www.toysrus.com/checkout/index.jsp?process=orderTrackingDetail&orderId=".$line['RefID'];
        break;
      case "qq-ex.com":
        $OrderUrl = "http://www.qq-ex.com/status/search_order/index/".$line['RefID'];
        break;
      case "thunderex.com":
        $OrderUrl = "http://www.thunderex.com/SearchEx.aspx?ordernum=".$line['RefID'];
        break;
      case "taobao.com":
        $OrderUrl = "http://trade.taobao.com/trade/detail/trade_item_detail.htm?bizOrderId=".$line['RefID'];
        break;
      case "culexpress.com":
        $OrderUrl = "http://www.culexpress.com/CulBill.aspx?wlnum=".$line['RefID'];
        break;
      case "transparcel.com":
        $OrderUrl = "http://www.transparcel.com/member/shipDetail.asp?T=1&slCode=".$line['RefID'];
        break;
      case "USPS":
        $OrderUrl = "https://tools.usps.com/go/TrackConfirmAction?formattedLabel=".$line['RefID'];
        break;
      case "DHL.DE":
        $OrderUrl = "http://nolp.dhl.de/nextt-online-public/set_identcodes.do?lang=en&idc=".$line['RefID'];
        break;
      case "tr.4px.com":
        $OrderUrl = "http://tr.4px.com/Cart/OrderInfo.aspx?c=".$line['RefID'];
        break;
      default:
        $OrderUrl = "#";
    }
    if ($OrderUrl != "#")
    {
      $OrderInfo = "<a href=\"$OrderUrl\" target=\"_blank\">".$line['RefID']."</a>";
    }
    else
    {
      $OrderInfo = $line['RefID'];
    }
    
    switch ($line['Courier'])
    {
      case "UPS":
        $DeliverUrl = "http://wwwapps.ups.com/etracking/tracking.cgi?tracknum=".$line['DeliverID'];
        break;
      case "FEDEX":
        $DeliverUrl = "http://www.fedex.com/Tracking?tracknumbers=".$line['DeliverID'];
        break;
      case "USPS":
        $DeliverUrl = "https://tools.usps.com/go/TrackConfirmAction?formattedLabel=".$line['DeliverID'];
        break;
      case "ONTRAC":
        $DeliverUrl = "http://www.ontrac.com/trackingres.asp?tracking_number=".$line['DeliverID'];
        break;
      case "DHL.DE":
        $DeliverUrl = "http://nolp.dhl.de/nextt-online-public/set_identcodes.do?lang=en&idc=".$line['DeliverID'];
        break;
      case "EMS":
        $DeliverUrl = "query_ems.php?type=c&emsid=".$line['DeliverID'];
        break;
      case "EMS.TX":
        $DeliverUrl = "query_ems.php?type=tx&emsid=".$line['DeliverID'];
        break;
      default:
        $DeliverUrl = "#";
    }
    if ($DeliverUrl != "#")
    {
      $CourierInfo = "<a href=\"$DeliverUrl\" target=\"_blank\">".$line['Courier'].":".$line['DeliverID']."</a>";
    }
    else
    {
      $CourierInfo = $line['Courier'].":".$line['DeliverID'];
    }
?>
      <tr>
        <td>
          <a href="javascript:void(edit=<?php echo $line['ExpID']; ?>)" onclick="edit_expense(<?php echo $line['ExpID']; ?>);"><img src="/images/edit_small.png"></a>
          <a href="javascript:void(del=<?php echo $line['ExpID']; ?>)" onclick="del_expense(<?php echo $line['InvID'].",".$line['ExpID']; ?>);"><img src="/images/delete_small.png"></a>
        </td>
        <td><?php echo $line['ExpenseType']; ?></td>
        <td><?php list($date, $time) = split(" ", $line['CreateTime']); echo $date; ?></td>
        <td><?php echo $line['Payee']; ?></td>
        <td><?php echo $OrderInfo; ?></td>
        <td><?php echo $line['ExpenseAmount']."*".$line['ExpenseRate']."=".sprintf("%01.2f", $line['ExpenseCNY']); ?></td>
        <td><?php echo $CourierInfo; ?></td>
        <td id="DeliverDate_<?php echo $line['ExpID'];?>"><a href="javascript:void(0)" onclick="edit_exp('DeliverDate',<?php echo $line['ExpID'];?>)"><?php list($date, $time) = split(" ", $line['DeliverDate']); echo $date; ?></a></td>
        <td><?php echo $line['Memo']; ?></td>
      </tr>
<?php
  }
?>
  </table>