<?php
  require("conn.php");
  $ExpID = $_POST['expid'];
  $InvID = $_POST['invid'];
  $conn=mysql_connect($mysql_server_name, $mysql_username, $mysql_password) or die ("数据库错误：".mysql_error());
  mysql_query("SET NAMES UTF8;", $conn);
  mysql_query("SET time_zone = '+08:00';", $conn);

  $strsql = "DELETE FROM Expense WHERE `InvID` = ".$InvID." AND `ExpID`= ".$ExpID.";";
  $r=mysql_db_query($mysql_database, $strsql, $conn);
  
  $strsql = "SELECT SUM(`ExpenseCNY`) FROM  `Expense` WHERE InvID = ".$InvID.";";
  $r=mysql_db_query($mysql_database, $strsql, $conn);
  $sumexp = mysql_fetch_row($r);
    
  $strsql="UPDATE Inventory SET `Expense` = (SELECT SUM(`ExpenseCNY`) FROM  `Expense` WHERE InvID = ".$InvID.") WHERE InvID = ".$InvID.";";
  $r=mysql_db_query($mysql_database, $strsql, $conn);

  mysql_close($conn);
  
  if ($sumexp[0] == NULL)
  {
  	$sumexp[0] = 0;
  }
  echo $sumexp[0];
?>