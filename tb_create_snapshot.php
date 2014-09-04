<?php
require("conn.php");

function gettimestamp()
{  
    return explode(' ', microtime());
}
$start_time_array = gettimestamp();//获取php开始的时间

date_default_timezone_set('Asia/Shanghai');
$mysqli = new mysqli($mysql_server_name, $mysql_username, $mysql_password, $mysql_database);

if (mysqli_connect_errno()) {
	printf("Database Connect failed: %s\n", mysqli_connect_error());
	exit();
}

$mysqli->query("SET NAMES UTF8;");
$mysqli->query("SET time_zone = '+08:00';");

$CreateTime = date("Y-m-d H:i:s");

$query = "INSERT INTO TB_Snapshot_List (CreateTime) VALUES ('".$mysqli->real_escape_string($CreateTime)."')";
$mysqli->query($query);

$SSID = $mysqli->insert_id;
if(isset($SSID))
{
	$query = "SELECT * FROM V_TB_Price;";
	$result = $mysqli->query($query);
	$count = 0;
	while ($row = $result->fetch_array(MYSQLI_ASSOC))
	{
		$query = "INSERT INTO TB_Snapshot (SSID,LegoID,Price,TaobaoID, Volume,Sellers) VALUES (".$mysqli->real_escape_string($SSID).", '".$mysqli->real_escape_string($row['LegoID'])."', ".$mysqli->real_escape_string($row['LowPrice']).", ".$mysqli->real_escape_string($row['TaobaoID']).", ".$mysqli->real_escape_string($row['TotalVolume']).",  ".$mysqli->real_escape_string($row['Sellers']).");";
		$mysqli->query($query);
		$count++;
	}
}
$mysqli->close();

$end_time_array = gettimestamp();
$time=round(($end_time_array[0] + $end_time_array[1] - $start_time_array[0] - $start_time_array[1])*1000);
echo "Snapshot for ".$count." records on ".$CreateTime." by ".$time."ms.\r\n";
?>