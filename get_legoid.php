<?php
	require("conn.php");
  $legoid=$_GET["legoid"];
  if (preg_match("/^[0-9]+$/", $legoid))
  {
	date_default_timezone_set('Asia/Shanghai');
	$conn=mysql_connect($mysql_server_name, $mysql_username, $mysql_password) or die ("数据库错误：".mysql_error());
	mysql_query("SET NAMES UTF8;", $conn);
	mysql_query("SET time_zone = '+08:00';", $conn);
  $strsql="SELECT * FROM legoset WHERE legoid = '$legoid'";
	$result=mysql_db_query($mysql_database, $strsql, $conn);
	$query=mysql_fetch_array($result);

  $title=$query['title'];
  $CNtitle=$query['CNtitle'];
    
  echo "Title: ".$title."<br />";
  echo "CNTitle: ".$CNtitle."<br />";
  }
?>