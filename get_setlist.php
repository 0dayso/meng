<div style="float:left;width:100%;">
  <div id="search" style="float:left;width:40%;">
    <input id="select_keyword" type="text" size="8"></input>
    <select id="select_type">
      <option value="Any">Any</option>
      <option value="Buy">Buy</option>
      <option value="InTransit">InTransit</option>
      <option value="Delivered">Delivered</option>
      <option value="InStock">InStock</option>
      <option value="Sold">Sold</option>
      <option value="Opened">Opened</option>
    </select>
    <input type="button" value="Search" onclick="search();"></input>
  </div>
<?php
  require("conn.php");
  $type=$_GET['type'];
  $keyword=$_GET['keyword'];
  if ($type != '' && $type != 'Any')
  {
    $strtype = " AND status='".$type."'";
  }
  elseif ($type == "Any")
  {
    $strtype = "";
  }
  else
  {
    $strtype = "";
  }
  if ($keyword != '')
  {
    $strkeyword = " AND legoid='".$keyword."'";
  }
  else
  {
    $strkeyword = "";
  }
  
  $perpage = 10;
  
  if ($_GET['page'] == '')
  {
    $currentpage = 1;
    $startrow = 0;
  }
  else
  {
    $currentpage = $_GET['page'];
    $startrow = $perpage * ($currentpage - 1);
  }
	date_default_timezone_set('Asia/Shanghai');
	$conn=mysql_connect($mysql_server_name, $mysql_username, $mysql_password) or die ("数据库错误：".mysql_error());
	mysql_query("SET NAMES UTF8;", $conn);
	mysql_query("SET time_zone = '+08:00';", $conn);
  
  $strsql="SELECT COUNT(ItemID) FROM PSS_Item WHERE 1=1".$strtype.$strkeyword.";";
  $result = mysql_db_query($mysql_database, $strsql, $conn);
  
  $count = mysql_fetch_row($result);
  $numbers = $count[0];

  $strsql="SELECT * FROM PSS_Item INNER JOIN PSS_POrder ON PSS_Item.POrderID = PSS_POrder.POrderID WHERE 1=1".$strtype.$strkeyword." ORDER BY ItemID DESC, LegoID LIMIT ".$startrow.",".$perpage.";";
  $result = mysql_db_query($mysql_database, $strsql, $conn);
	
  while($row=mysql_fetch_array($result))
	{
		$rows[]=$row;
	}
  $pages = ceil($numbers / $perpage);
  echo "  <div id=\"PageScroll\" style=\"float:left;width:60%;\">\r\n";
if ($currentpage != 1)
{
  echo "  <a href=\"javascript:void(0)\" onclick=\"pageup();\"><<</a>&nbsp;\r\n";
}
for ($i = 1; $i <= $pages; $i++)
{
  if ($i == $currentpage)
  {
    echo "  <font class=\"currentpage\">".$i."</font>&nbsp;\r\n";
  }
  else
  {
    echo "  <a href=\"javascript:void(0)\" class=\"page\" onclick=\"page(".$i.");\">".$i."</a>&nbsp;\r\n";
  }
  
}
if ($currentpage != $pages)
{
  echo "  <a href=\"javascript:void(0)\" onclick=\"pagedown();\">>></a>\r\n";
}
?>
  </div>
</div>
<div style="float:left;width:100%;">
  <table style="text-align:center">
		<tr>
      <th>操作</th>
			<th>编号</th>
			<th>下单日期</th>
			<th>支出</th>
			<th>收入</th>
			<th>状态</th>
			<th>位置</th>
		</tr>
<?php
	foreach($rows as $line) 
	{
	  $editstr = $line['ItemID'].",'".$line['Status']."'";
?>
    <tr onmousemove="omm(this)" onmouseout="omo(this)">
      <td><a href="javascript:void(list=<?php echo $line['ItemID']; ?>);" onclick="show_list(this, <?php echo $line['ItemID'];?>);"><img alt="详情" src="/images/list.png"></a></td>
      <td><?php echo $line['LegoID']; ?></td>
			<td><?php list($date, $time) = split(" ", $line['OrderTime']); echo $date; ?></td>
			<td id="exp_<?php echo $line['InvID']; ?>"><?php echo sprintf("%01.2f", $line['Expense']); ?></td>
			<td id="rev_<?php echo $line['InvID']; ?>"><?php echo sprintf("%01.2f", $line['Revenue']); ?></td>
			<td><?php echo $line['Status']; ?></td>
			<td><?php echo $line['Location']; ?></td>
    </tr>
<?php
  }
?>
  </table>
</div>