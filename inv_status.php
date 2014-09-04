<?php
	require("conn.php");
    $InvID = $_GET["invid"];
	date_default_timezone_set('Asia/Shanghai');
	$conn=mysql_connect($mysql_server_name, $mysql_username, $mysql_password) or die ("数据库错误：".mysql_error());
	mysql_query("SET NAMES UTF8;", $conn);
	mysql_query("SET time_zone = '+08:00';", $conn);

    if (isset($_GET["status"]) && !empty($_GET["status"]))
    {

		$strsql = "UPDATE Inventory SET `Status` = '". $_GET["status"] ."' WHERE `InvID` = $InvID;";
		$r=mysql_db_query($mysql_database, $strsql, $conn);
    }

		$strsql="SELECT * FROM Inventory WHERE InvID = ".$InvID.";";
		$result=mysql_db_query($mysql_database, $strsql, $conn);
		$line = mysql_fetch_assoc($result);
		$editstr = $line['InvID'].",'".$line['Status']."'";

?>
<a href="javascript:void(0)" onclick="edit_status(<?php echo $editstr;?>)"><?php echo $line['Status']; ?></a>