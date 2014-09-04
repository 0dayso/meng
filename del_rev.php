<?php
  require("conn.php");
  $RevID = $_POST['revid'];
  $InvID = $_POST['invid'];
  $conn=mysql_connect($mysql_server_name, $mysql_username, $mysql_password) or die ("数据库错误：".mysql_error());
  mysql_query("SET NAMES UTF8;", $conn);
  mysql_query("SET time_zone = '+08:00';", $conn);

  $strsql = "DELETE FROM Revenue WHERE `InvID` = ".$InvID." AND `RevID`= ".$RevID.";";
  $r=mysql_db_query($mysql_database, $strsql, $conn);
  
  $strsql = "SELECT SUM(`Amount`) FROM  `Revenue` WHERE InvID = ".$InvID.";";
  $r=mysql_db_query($mysql_database, $strsql, $conn);
  $sumrev = mysql_fetch_row($r);
    
  $strsql="UPDATE Inventory SET `Revenue` = (SELECT SUM(`Amount`) FROM  `Revenue` WHERE InvID = ".$InvID.") WHERE InvID = ".$InvID.";";
  $r=mysql_db_query($mysql_database, $strsql, $conn);

  mysql_close($conn);
  
  if ($sumrev[0] == NULL)
  {
  	$sumrev[0] = 0;
  }
  echo $sumrev[0];
?>