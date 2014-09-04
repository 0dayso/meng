<?php
$DeliveryID = $_GET["did"];

if (isset($DeliveryID))
{
	require("conn.php");
	date_default_timezone_set('Asia/Shanghai');
	$mysqli = new mysqli($mysql_server_name, $mysql_username, $mysql_password, $mysql_database);

	if (mysqli_connect_errno()) {
		printf("Database Connect failed: %s\n", mysqli_connect_error());
		exit();
	}

	$mysqli->query("SET NAMES UTF8;");
	$mysqli->query("SET time_zone = '+08:00';");
	$query = "SELECT PSS_Delivery.DeliveryID AS DeliveryID,Vendor,OrderNumber,LegoID,PSS_Item.ItemID AS ItemID FROM PSS_Delivery INNER JOIN PSS_Price ON PSS_Delivery.DeliveryID = PSS_Price.LinkID LEFT JOIN PSS_Item ON PSS_Price.ItemID=PSS_Item.ItemID WHERE PSS_Delivery.DeliveryID='".$mysqli->real_escape_string($DeliveryID)."' AND PSS_Price.Type = 'Delivery' AND PSS_Item.Status='InTransit'";

	//$arrItems = array();
	$arrItemIDs = array();
	$result = $mysqli->query($query);
	while ($row = $result->fetch_array(MYSQLI_ASSOC))
	{
		$Vendor = $row["Vendor"];
		$OrderNumber = $row["OrderNumber"];
		
		/*
		$LegoID = $row["LegoID"];
		if (array_key_exists($LegoID, $arrItems))
		{
			$arrItems["$LegoID"] = intval($arrItems["$LegoID"]) + 1;
		}
		else
		{
			$arrItems["$LegoID"] = intval(1);
		}
		*/
		$ItemID = $row["ItemID"];
		array_push($arrItemIDs, $ItemID);
	}
	
	/*
	$strItems = "";
	ksort($arrItems);
	foreach ($arrItems as $LegoID => $count)
	{
		$strItems = $strItems.$LegoID."*".$count.", ";
	}

	$strItems = trim($strItems, ", ");
	*/		
	$Items = implode(",", $arrItemIDs);
	
	$query = "SELECT LegoID,Amount FROM PSS_Item INNER JOIN PSS_Price ON PSS_Price.ItemID = PSS_Item.ItemID  WHERE PSS_Item.ItemID IN (".$Items.") AND Type='Purchase';";
	$result = $mysqli->query($query);
	
	$arrItems = array();
	while ($row = $result->fetch_array(MYSQLI_ASSOC))
	{
		$LegoID = $row["LegoID"];
		$Price = $row["Amount"] * -1;
		if (array_key_exists($LegoID, $arrItems))
		{
			$ItemInfo = new stdClass();
			$ItemInfo->{'Count'} = $arrItems["$LegoID"]->{'Count'} + 1;
			$ItemInfo->{'SubPrice'} = $Price;
			$ItemInfo->{'TotalPrice'} = $arrItems["$LegoID"]->{'TotalPrice'} + $Price;
			$arrItems["$LegoID"] = $ItemInfo;
		}
		else
		{
			$ItemInfo = new stdClass();
			$ItemInfo->{'Count'} = 1;
			$ItemInfo->{'SubPrice'} = $Price;
			$ItemInfo->{'TotalPrice'} = $Price;
			$arrItems["$LegoID"] = $ItemInfo;
		}
	}
	ksort($arrItems);

	$strItems = "";
	$strDetails = "[";
	foreach ($arrItems as $LegoID => $ItemInfo)
	{
		$strItems = $strItems."LEGO ".$LegoID."*".$ItemInfo->{'Count'}.", ";
		//"22000000","玩具","LEGO","75025","4","","71.24","284.96"
		$strDetails = $strDetails."\"22000000\",\"玩具\",\"LEGO\",\"".$LegoID."\",\"".$ItemInfo->{'Count'}."\",\"\",\"".$ItemInfo->{'SubPrice'}."\",\"".$ItemInfo->{'TotalPrice'}."\",";
	}
	$strItems = trim($strItems, ", ");
	$strDetails = trim($strDetails, ", ");
	$strDetails .= "]";

	$goods = array(
		'cangku_id' => '5',
		'goods_wuliu' => $Vendor,
		'goods_yundan' => $OrderNumber,
		'goods_desc' => $strItems,
		'detail' => $strDetails,
	);

	$fields_string = "goods={";
	foreach($goods as $key=>$value)
	{
		if ($key <> "detail")
		{
			$fields_string .= "\"".$key."\":\"".$value."\",";
		}
		else
		{
			$fields_string .= "\"".$key."\":".$value.",";
		}
	}
	$fields_string = rtrim($fields_string, ',');
	$fields_string .= "}";
	
	$url = "http://www.tiantian8.com/flow.php?step=add_to_cart3";
	$ch = curl_init();
	$timeout = 5; 
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_COOKIE, $tiantian_cookie);
	curl_setopt($ch, CURLOPT_POST, 1);
	curl_setopt($ch, CURLOPT_POSTFIELDS, $fields_string);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);

	//Execute the  cURL session.
	$strResponse = curl_exec($ch);

	//Get the Error Code returned by Curl.
	$curlErrno = curl_errno($ch);
	if($curlErrno){
		$curlError = curl_error($ch);
		throw new Exception($curlError);
	}
	//Close the Curl Session.
	curl_close($ch);
	
	echo $strResponse;

}
// {"cangku_id":"5","goods_wuliu":"UPS","goods_yundan":"TEST","goods_desc":"detail","detail":["04000000","1","1","1","1","","1","1","05000000","2","2","2","2","","2","2"]}
// goods:{"cangku_id":"5","goods_wuliu":"UPS","goods_yundan":"1Z7639X00339681685","goods_desc":"LEGO 75025*4","detail":["22000000","玩具","LEGO","75025","4","","71.24","284.96"]}



?>