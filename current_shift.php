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
	$previoustime = date('Y-m-d H:i:s', strtotime($currenttime. ' - 30 minute'));
	
	echo $previoustime." - ".$currenttime."<br/>";	
	$query = "SELECT Hostgroup, Workflow, Service, COUNT(PID) AS Count, Message FROM Pagers WHERE Timestamp BETWEEN '$previoustime' AND '$currenttime' GROUP BY Hostgroup,Workflow,Service;";
	$result = $mysqli->query($query);

	echo "<table><tr><th>Hostgroup</th><th>Workflow</th><th>Service</th><th>Count</th><th>Message</th></tr>";
	while ($row = $result->fetch_array(MYSQLI_ASSOC))
	{
		echo "<tr><td>".$row["Hostgroup"]."</td><td>".$row["Workflow"]."</td><td>".$row["Service"]."</td><td>".$row["Count"]."</td><td>".$row["Message"]."</td></tr>";
	}
	echo "</table>";
	
	$hour = intval(date('G'));
	if ($hour < 1)
	{
		//SNV Shift;
		$starttime = date('Y-m-d', strtotime($currenttime. ' - 1 day'))." 13:00:00";
		$endtime = date('Y-m-d')." 01:00:00";
	}
	elseif ($hour >= 1 & $hour<13)
	{
		//PEK Shift;
		$starttime = date('Y-m-d')." 01:00:00";
		$endtime = date('Y-m-d')." 13:00:00";
	}
	else
	{
		//SNV Shift;
		$starttime = date('Y-m-d')." 13:00:00";
		$endtime = date('Y-m-d', strtotime($currenttime. ' + 1 day'))." 01:00:00";
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