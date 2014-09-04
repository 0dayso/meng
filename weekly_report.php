<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <link rel="stylesheet" href="table.css">
</head>
<body>
<?php
	error_reporting(E_ALL);
	ini_set('display_errors', '1');
	
	require("conn.php");

	$mysqli = new mysqli($mysql_server_name, $mysql_username, $mysql_password, $mysql_database);

	if (mysqli_connect_errno()) {
		printf("Database Connect failed: %s\n", mysqli_connect_error());
		exit();
	}

	$mysqli->query("SET NAMES UTF8;");
	$mysqli->query("SET time_zone = '+00:00';");
	
	$currenttime = date('Y-m-d H:i:s');
	$starttime = date('Y-m-d H:i:s', strtotime($currenttime. ' - 14 day'));
	
	$query = "SELECT Shift, Count(Service) AS Services, SUM(Pagers) AS Pagers FROM (SELECT IF(DATE_FORMAT(Timestamp, '%k') < 1, CONCAT(DATE_FORMAT(DATE_ADD(Timestamp, INTERVAL -1 DAY), '%Y%m%d'), '-SNV'), IF(DATE_FORMAT(Timestamp, '%k') < 13, CONCAT(DATE_FORMAT(Timestamp, '%Y%m%d'), '-PEK'), CONCAT(DATE_FORMAT(Timestamp, '%Y%m%d'), '-SNV'))) AS Shift, Service, COUNT(PID) AS Pagers FROM Pagers WHERE Timestamp >= '$starttime' GROUP BY Shift,Service) X GROUP BY Shift;";
	$result = $mysqli->query($query);

    echo "<table><tr><th>Shift</th><th>Services</th><th>Pagers</th></tr>";
	while ($row = $result->fetch_array(MYSQLI_ASSOC))
	{
		echo "<tr><td><a href=\"detail.php?s=".$row["Shift"]."\" target=\"_blank\">".$row["Shift"]."</a></td><td>".$row["Services"]."</td><td>".$row["Pagers"]."</td></tr>";
	}
	echo "</table>";
	$mysqli->close();
?>
</body>
</html>