<?php
	require("conn.php");

	date_default_timezone_set('Asia/Shanghai');
	$mysqli = new mysqli($mysql_server_name, $mysql_username, $mysql_password, $mysql_database);

	if (mysqli_connect_errno()) {
		printf("Database Connect failed: %s\n", mysqli_connect_error());
		exit();
	}

	$mysqli->query("SET NAMES UTF8;");
	$mysqli->query("SET time_zone = '+08:00';");
	$ItemID = $_GET["itemid"];
	$query = "SELECT * FROM PSS_Price INNER JOIN PSS_POrder ON PSS_Price.LinkID = PSS_POrder.POrderID WHERE Type = 'Purchase' AND ItemID='".$mysqli->real_escape_string($ItemID)."';";
	$result = $mysqli->query($query);
	while ($row = $result->fetch_array(MYSQLI_ASSOC))
	{
		list($date, $time) = explode(" ", $row['OrderTime']); 
		echo $date." [Purchase:".$row["LinkID"]."] ".$row["Seller"].":".$row["OrderNumber"]." (".$row["Amount"]."*".$row["Rate"]."=)".$row["CNYAmount"]."<br/>";
	}
	$query = "SELECT * FROM PSS_Price INNER JOIN PSS_Delivery ON PSS_Price.LinkID = PSS_Delivery.DeliveryID WHERE Type = 'Delivery' AND ItemID='".$mysqli->real_escape_string($ItemID)."';";
	$result = $mysqli->query($query);
	while ($row = $result->fetch_array(MYSQLI_ASSOC))
	{
		list($date, $time) = explode(" ", $row['DeliveryTime']); 
		echo $date." [Delivery:".$row["LinkID"]."] ".$row["Vendor"].":".$row["OrderNumber"]." (".$row["Amount"]."*".$row["Rate"]."=)".$row["CNYAmount"]."<br/>";
	}
	
	$query = "SELECT * FROM PSS_Price INNER JOIN PSS_SOrder ON PSS_Price.LinkID = PSS_SOrder.SOrderID WHERE Type = 'Sold' AND ItemID='".$mysqli->real_escape_string($ItemID)."';";
	$result = $mysqli->query($query);
	while ($row = $result->fetch_array(MYSQLI_ASSOC))
	{
		list($date, $time) = explode(" ", $row['OrderTime']); 
		echo $date." [Sold:".$row["LinkID"]."] ".$row["Buyer"].":".$row["OrderNumber"]." (".$row["Amount"]."*".$row["Rate"]."=)".$row["CNYAmount"]."<br/>";
	}
?>