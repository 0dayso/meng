<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <link rel="stylesheet" href="table.css">
    <script src="sorttable.js"></script>
    <title>Taobao价格查询</title>
</head>
<body>
<table>
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

$query = "SELECT LegoID, MIN(Price) AS LowPrice, SUM(Volume) AS TotalVol, COUNT(TaobaoID) AS Sellers FROM `TB_Item_Price` WHERE Filter=0 GROUP BY LegoID ORDER By LowPrice;";
$result = $mysqli->query($query);
echo "<table class=\"sortable\"><tr><th>LegoID</th><th>LowPrice</th><th>TotalVol</th><th>Sellers</th></tr>";

while ($row = $result->fetch_array(MYSQLI_ASSOC))
{
	$LegoID = $row['LegoID'];
	$Price = $row['LowPrice'];
	$TotalVol = $row['TotalVol'];
	$Sellers = $row['Sellers'];
	echo "<tr><td><a href='tb_price.php?legoid=".$LegoID."'>".$LegoID."</a></td><td>".$Price."</td><td>".$TotalVol."</td><td>".$Sellers."</td></tr>";
}
echo "</table>";

/*
Low price & Sold vol
*/
?>
</body>
</html>