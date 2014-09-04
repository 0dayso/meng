<?php
header("Content-type: text/html; charset=utf-8");

require("conn.php");

date_default_timezone_set('Asia/Shanghai');
$mysqli = new mysqli($mysql_server_name, $mysql_username, $mysql_password, $mysql_database);

if (mysqli_connect_errno()) {
	printf("Database Connect failed: %s\n", mysqli_connect_error());
	exit();
}

$mysqli->query("SET NAMES UTF8;");
$mysqli->query("SET time_zone = '+08:00';");



$ArrSOrder = array();

$query = "SELECT * FROM `Revenue` WHERE RevID > 3;";
$result = $mysqli->query($query);


while ($row = $result->fetch_array(MYSQLI_ASSOC))
{
	$ItemID = $row["InvID"];
	$OrderNumber = $row["TransactionID"];
	$OrderTime = $row["TransactionTime"];
	$Buyer = $row["PayeeID"];
	$BuyerInfo = $row["Memo"];
	$Amount = $row["Amount"];
	$Rate = 1;
	$CNYAmount = ROUND($Amount*$Rate, 2);
	
	if (isset($ArrSOrder[$OrderNumber]))
	{
		$SOrderID = $ArrSOrder[$OrderNumber];
	}
	else
	{
		$queryInsert = "INSERT INTO PSS_SOrder (OrderNumber, OrderTime, Buyer, BuyerInfo) VALUES ('".$OrderNumber."', '".$OrderTime."', '".$Buyer."', '".$BuyerInfo."');";
		echo "<br>".$queryInsert."<br>";
		$mysqli->query($queryInsert);
		$SOrderID = $mysqli->insert_id;
		$ArrSOrder[$OrderNumber] = $SOrderID;
	}
	if ($SOrderID > 0)
	{
		$queryInsert = "INSERT INTO PSS_Price (ItemID, Type, LinkID, Amount, Rate, CNYAmount) VALUES ('".$ItemID."', 'Sold', '".$SOrderID."', '".$Amount."', '".$Rate."', '".$CNYAmount."');";
		echo $queryInsert."<br>";
		$mysqli->query($queryInsert);

	}
}
$result->close();
$mysqli->close();

?>