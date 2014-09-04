<?php
//
// 旧版查询库存
//
	require("conn.php");
	$legoid = $_GET["legoid"];
	if (isset($legoid))
	{
		date_default_timezone_set('Asia/Shanghai');
		$conn=mysql_connect($mysql_server_name, $mysql_username, $mysql_password) or die ("数据库错误：".mysql_error());
		mysql_query("SET NAMES UTF8;", $conn);
		mysql_query("SET time_zone = '+08:00';", $conn);
		
		$strsql="SELECT COUNT(LegoID) AS Count FROM Inventory WHERE LegoID = '".$legoid."' AND Status='InStock';";
		$result=mysql_db_query($mysql_database, $strsql, $conn);
		if ($row=mysql_fetch_array($result))
		{
			echo $row['Count'];
		}
		mysql_free_result($result);
	}
?>