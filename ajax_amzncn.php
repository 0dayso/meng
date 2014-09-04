<?php

//
// 获取amazon cn 价格信息
//

require("AmazonECS.class.php");

defined('AWS_API_KEY') or define('AWS_API_KEY', 'AKIAIEV7RMC67RKFMVLA');
defined('AWS_API_SECRET_KEY') or define('AWS_API_SECRET_KEY', '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');
defined('AWS_ASSOCIATE_TAG') or define('AWS_ASSOCIATE_TAG', 'brickcn-23');


require("conn.php");
date_default_timezone_set('Asia/Shanghai');

$LegoID = $_GET["legoid"];
$ASIN = $_GET["asin"];

if (isset($LegoID))
{
	if (!isset($ASIN))
	{
		$mysqli = new mysqli($mysql_server_name, $mysql_username, $mysql_password, $mysql_database);

		if (mysqli_connect_errno()) {
			printf("Database Connect failed: %s\n", mysqli_connect_error());
			exit();
		}

		$mysqli->query("SET NAMES UTF8;");
		$mysqli->query("SET time_zone = '+08:00';");
		$query = "SELECT ASIN FROM PW_AmazonInfo WHERE Country='CN' AND LegoID='".$LegoID."';";
		$result = $mysqli->query($query);
		$row = $result->fetch_array(MYSQLI_ASSOC);
		$ASIN = $row["ASIN"];
		$mysqli->close();
	}
	try
	{
		$amazonEcs = new AmazonECS(AWS_API_KEY, AWS_API_SECRET_KEY, 'CN', AWS_ASSOCIATE_TAG);

		$amazonEcs->associateTag(AWS_ASSOCIATE_TAG);
    	$response = $amazonEcs->responseGroup('ItemAttributes,OfferFull')->lookup($ASIN);

		$Title = $response->Items->Item->ItemAttributes->Title;
		$Merchant = $response->Items->Item->Offers->Offer->Merchant->Name;
		$Price = $response->Items->Item->ItemAttributes->ListPrice->Amount / 100;
		if ($Price > 0)
		{

			$mysqli = new mysqli($mysql_server_name, $mysql_username, $mysql_password, $mysql_database);

			if (mysqli_connect_errno()) {
				printf("Database Connect failed: %s\n", mysqli_connect_error());
				exit();
			}

			$mysqli->query("SET NAMES UTF8;");
			$mysqli->query("SET time_zone = '+08:00';");
			$query = "UPDATE DB_Set SET CNPrice=".$mysqli->real_escape_string($Price)." WHERE LegoID='".$mysqli->real_escape_string($LegoID)."' LIMIT 1;";
			$result = $mysqli->query($query);
			
			$mysqli->close();
		
			echo sprintf("%01.2f", $Price);
		}
		else
		{
			echo "Amazon无报价";
		}
	}
	catch(Exception $e)
	{
		echo $e->getMessage();
	}
}


?>