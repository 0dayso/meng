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


if ($_GET["action"] == "batchimport")
{
	foreach($_POST as $k=>$v)
	{
		if (preg_match("/^rad_(\d+)/",$k,$match))
		{
			$ItemID = $match[1];
			if ($v == "import")
			{
				$LegoID = $_POST["$ItemID"];
				$query = "INSERT INTO TB_Item (ItemID, LegoID, Seller) VALUES (".$mysqli->real_escape_string($ItemID).", '".$mysqli->real_escape_string($LegoID)."', '".$mysqli->real_escape_string("懒懒淑女")."');";
				echo $query;
				$result = $mysqli->query($query);
				echo $result;
			}
			elseif ($v == "ignore")
			{
				echo "Ignore<br/>";
			}
			elseif ($v == "never")
			{
				echo "Never<br/>";
			}
		}
	}
}
elseif ($_GET["action"] == "updatecode")
{
	include "appconf.php";
	include "TopSdk.php";	

	$c = new TopClient;
	$c->appkey = $client_id;
	$c->secretKey = $client_secret;
	$sessionKey = $access_token;

	$req = new ItemUpdateRequest;

	foreach($_POST as $k=>$v)
	{
		if (preg_match("/^rad_(\d+)/",$k,$match))
		{
			$ItemID = $match[1];
			if ($v == "import")
			{
				$ItemCode = $_POST["$ItemID"];
				$req->setNumIid($ItemID);
				$req->setOuterId($ItemCode);
				$resp = $c->execute($req, $sessionKey);
				echo "Update $ItemID to $ItemCode<br/>";
			}
			elseif ($v == "ignore")
			{
				echo "Ignore<br/>";
			}
		}
	}

}
elseif ($_GET["action"] == "updatebarcode")
{
	include "appconf.php";
	include "TopSdk.php";	

	$c = new TopClient;
	$c->appkey = $client_id;
	$c->secretKey = $client_secret;
	$sessionKey = $access_token;

	$req = new ItemBarcodeUpdateRequest;

	foreach($_POST as $k=>$v)
	{
		if (preg_match("/^rad_(\d+)/",$k,$match))
		{
			$ItemID = $match[1];
			if ($v == "import")
			{
				$ItemCode = $_POST["$ItemID"];
				$req->setItemId($ItemID);
				$req->setItemBarcode($ItemCode);
				$resp = $c->execute($req, $sessionKey);
				echo "Update $ItemID to $ItemCode<br/>";
			}
			elseif ($v == "ignore")
			{
				echo "Ignore<br/>";
			}
		}
	}

}

$mysqli->close();

?>