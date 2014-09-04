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
	date_default_timezone_set('UTC');
	$mysqli = new mysqli($mysql_server_name, $mysql_username, $mysql_password, $mysql_database);

	if (mysqli_connect_errno()) {
		printf("Database Connect failed: %s\n", mysqli_connect_error());
		exit();
	}

	$mysqli->query("SET NAMES UTF8;");
	$mysqli->query("SET time_zone = '+00:00';");
	
	if (isset($_GET["s"]))
	{
		$Shift = $_GET["s"];
	}
	else
	{
		$hour = intval(date('G'));
		if ($hour < 1)
		{
			//SNV Shift;
			$Shift = date('Ymd', strtotime($currenttime. ' - 1 day'))."-SNV";
		}
		elseif ($hour >= 1 & $hour<13)
		{
			//PEK Shift;
			$Shift = date('Ymd')."-PEK";
		}
		else
		{
			//SNV Shift;
			$Shift = date('Ymd')."-SNV";
		}
	}

	$Year = substr($Shift,0,4);
	$Month = substr($Shift,4,2);
	$Day = substr($Shift,6,2);
	$Rotation = substr($Shift,9,3);
	
	if ($Rotation == "PEK")
	{
		$starttime = date('Y-m-d 01:00:00', strtotime($Year."-".$Month."-".$Day));
		$endtime = date('Y-m-d 13:00:00', strtotime($Year."-".$Month."-".$Day));
	}
	else
	{
		$starttime = date('Y-m-d 13:00:00', strtotime($Year."-".$Month."-".$Day));
		$endtime = date('Y-m-d 01:00:00', strtotime($Year."-".$Month."-".$Day." + 1 day"));
	}
	
	echo $starttime." - ".$endtime."<br/>";	
	$query = "SELECT Hostgroup, Workflow, Service, COUNT(PID) AS Count, Message FROM Pagers WHERE Timestamp BETWEEN '$starttime' AND '$endtime' GROUP BY Hostgroup,Workflow,Service;";
	$result = $mysqli->query($query);

    echo "<table><tr><th>Hostgroup</th><th>Workflow</th><th>Service</th><th>Count</th><th>Message</th></tr>";
	while ($row = $result->fetch_array(MYSQLI_ASSOC))
	{
		echo "<tr><td>".$row["Hostgroup"]."</td><td>".$row["Workflow"]."</td><td>".$row["Service"]."</td><td>".$row["Count"]."</td><td>".$row["Message"]."</td></tr>";
	}
	echo "</table>";
?>
</body>
</html>