<?php
	require("conn.php");
    $ExpID = $_GET["expid"];
    $Field = $_GET["field"];
    $Value = $_GET["value"];

	date_default_timezone_set('Asia/Shanghai');
	$conn=mysql_connect($mysql_server_name, $mysql_username, $mysql_password) or die ("数据库错误：".mysql_error());
	mysql_query("SET NAMES UTF8;", $conn);
	mysql_query("SET time_zone = '+08:00';", $conn);

    if (isset($Value) && !empty($Value))
    {

		$strsql = "UPDATE Expense SET `" . $Field . "` = '" . $Value . "' WHERE `ExpID` = " . $ExpID . ";";
		$r=mysql_db_query($mysql_database, $strsql, $conn);
    }

		$strsql="SELECT * FROM Expense WHERE ExpID = ".$ExpID.";";
		$result=mysql_db_query($mysql_database, $strsql, $conn);
		$line = mysql_fetch_assoc($result);
		
		switch ($Field)
		{
			case "DeliverDate":
				list($date, $time) = split(" ", $line['DeliverDate']);
				$strOut = $date;
				break;
			default:
				$strOut = $line['$Field'];
		}

?>
<a href="javascript:void(0)" onclick="edit_exp('<?php echo $Field; ?>',<?php echo $ExpID; ?>)"><?php echo $strOut; ?></a>