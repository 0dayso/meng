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


if (isset($_POST["ttid"]))
{
	$ttid = $_POST["ttid"];
	$deliveryid = $_POST["did"];
	$query = "UPDATE Tiantian_Delivery SET DeliveryID='".$mysqli->real_escape_string($deliveryid)."' WHERE TTID='".$mysqli->real_escape_string($ttid)."';";
	$result = $mysqli->query($query);
	echo "OK";
}
else
{

	$query = "SELECT DeliveryID,Vendor,OrderNumber,ShippingTime,DeliveryTime FROM PSS_Delivery WHERE ShippingTo LIKE 'TIAN%%';";
	$result = $mysqli->query($query);

	$DeliveryOrdersByOrderID = array();
	$DeliveryOrdersByDeliveryID = array();
	while ($row = $result->fetch_array(MYSQLI_ASSOC))
	{
		$OrderID = substr($row["OrderNumber"], -8);
		$DeliveryID = $row["DeliveryID"];
		$OrderInfo = new stdClass();
		$OrderInfo->{'DeliveryID'} = $DeliveryID;
		$OrderInfo->{'Vendor'} = $row["Vendor"];
		$OrderInfo->{'OrderNumber'} = $row["OrderNumber"];
		if ($row["DeliveryTime"] == "0000-00-00 00:00:00")
		{
			$OrderInfo->{'Time'} = $row["ShippingTime"];
		}
		else
		{
			$OrderInfo->{'Time'} = $row["DeliveryTime"];
		}
		
		$queryitemstr = "SELECT LegoID FROM PSS_Price INNER JOIN PSS_Item ON PSS_Price.ItemID = PSS_Item.ItemID WHERE Type='Delivery' AND LinkID='".$OrderInfo->{'DeliveryID'}."';";
		$resultitemstr = $mysqli->query($queryitemstr);
		$arrItems = array();
		while ($rowitemstr = $resultitemstr->fetch_array(MYSQLI_ASSOC))
		{
			$LegoID = $rowitemstr["LegoID"];
			if (array_key_exists($LegoID, $arrItems))
			{
					$arrItems["$LegoID"] = intval($arrItems["$LegoID"]) + 1;
			}
			else
			{
				$arrItems["$LegoID"] = intval(1);
			}

		}
		$strItems = "";
		ksort($arrItems);
		foreach ($arrItems as $LegoID => $count)
		{
			$strItems = $strItems.$LegoID."*".$count.", ";
		}

		$strItems = trim($strItems, ", ");
		
		$OrderInfo->{'Items'} = $strItems;
		
		$LegoIDs = implode(",", array_keys($arrItems));
		$queryitemstr = "SELECT LegoID,Length,Width,Height FROM DB_Set WHERE LegoID IN (".$LegoIDs.");";
		$resultitemstr = $mysqli->query($queryitemstr);
		while ($rowitemstr = $resultitemstr->fetch_array(MYSQLI_ASSOC))
		{
			$LegoID = $rowitemstr["LegoID"];
			$arrDimensions["$LegoID"] = $rowitemstr["Length"]."x".$rowitemstr["Width"]."x".$rowitemstr["Height"]."(".$LegoID.")";
		}
		$strDimensions = "";
		foreach ($arrItems as $LegoID => $count)
		{
			$strDimensions = $strDimensions.$arrDimensions[$LegoID]."*".$count.", ";
		}
		$strDimensions = trim($strDimensions, ", ");
		$OrderInfo->{'Dimensions'} = $strDimensions;
		
		$DeliveryOrdersByOrderID[$OrderID] = $OrderInfo;
		$DeliveryOrdersByDeliveryID[$DeliveryID] = $OrderInfo;
	}
	


	if ($_GET["instock"])
	{
		$query = "SELECT TTID, Status, DeliveryID, Time, InVendor, InNumber, InWeight From Tiantian_Delivery WHERE Status='货物已到天天' ORDER BY TTID DESC;";
		$result = $mysqli->query($query);
	}
	else
	{
		$QueryID = $_GET["id"];

		$query = "SELECT TTID, Status, DeliveryID, Time, InVendor, InNumber, InWeight From Tiantian_Delivery WHERE TTID = 'TT000".$QueryID."' OR Status LIKE '%%".$QueryID."' OR Detail LIKE '%%ID".$QueryID."%%' ORDER BY TTID DESC;";
		$result = $mysqli->query($query);
	}

?>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <link rel="stylesheet" href="table.css">
	<script type="text/javascript" src="jquery.js"></script>
	<script language="JavaScript">
	function update_deliveryid(ttid, did)
	{
		$.post("tiantian_link.php", { ttid: ttid, did: did} , function(data) { $("#"+ttid).html(did); } );
	}
	</script>
    <title>Tiantian运单Link</title>
</head>
<body>
	<table>
	<tr><th>TTID</th><th>Time</th><th>Status</th><th>Vendor</th><th>TrackNum</th><th>Delivery TrackNum</th><th>Items</th><th>Dimensions</th><th>Link</th><th>Weight</th></tr>

<?

	while ($row = $result->fetch_array(MYSQLI_ASSOC))
	{
		if ($row["DeliveryID"] <> 0)
		{
			$DeliveryID = $row["DeliveryID"];
			$DeliveryOrderNum = $DeliveryOrdersByDeliveryID[$DeliveryID]->{'Vendor'}.":".$DeliveryOrdersByDeliveryID[$DeliveryID]->{'OrderNumber'};
			$DeliveryTime = $DeliveryOrdersByDeliveryID[$DeliveryID]->{'Time'};
			$DeliveryItems = $DeliveryOrdersByDeliveryID[$DeliveryID]->{'Items'};
			$DeliveryDimensions = $DeliveryOrdersByDeliveryID[$DeliveryID]->{'Dimensions'};
		}
		else
		{
			$ordernum = substr($row["InNumber"], -8);
			
			if (isset($DeliveryOrdersByOrderID[$ordernum]))
			{
				if ($row["Status"] == '自购申请已提交' )
				{
					//$DeliveryID = "未入库";
					$DeliveryID = "<a href=\"javascript:update_deliveryid('".$row["TTID"]."','".$DeliveryOrdersByOrderID[$ordernum]->{'DeliveryID'}."');\">".$DeliveryOrdersByOrderID[$ordernum]->{'DeliveryID'}."</a>";

				}
				elseif ($row["Status"] == '无效订单' )
				{
					$DeliveryID = "无效订单";
				}
				else
				{
					$DeliveryID = "<a href=\"javascript:update_deliveryid('".$row["TTID"]."','".$DeliveryOrdersByOrderID[$ordernum]->{'DeliveryID'}."');\">".$DeliveryOrdersByOrderID[$ordernum]->{'DeliveryID'}."</a>";
				}
				$DeliveryOrderNum = $DeliveryOrdersByOrderID[$ordernum]->{'Vendor'}.":".$DeliveryOrdersByOrderID[$ordernum]->{'OrderNumber'};
				$DeliveryTime = $DeliveryOrdersByOrderID[$ordernum]->{'Time'};
				$DeliveryItems = $DeliveryOrdersByOrderID[$ordernum]->{'Items'};
				$DeliveryDimensions = $DeliveryOrdersByOrderID[$ordernum]->{'Dimensions'};
			}
			else
			{
				$DeliveryID = "无匹配";
				$DeliveryOrderNum = "";
				$DeliveryTime = "";
				$DeliveryItems = "";
				$DeliveryDimensions = "";
			}
		}
		echo "<tr><td><a href=\"http://www.tiantian8.us/user3.php?act=detail_zigou&zigou_id=".substr($row["TTID"], -5)."\">".$row["TTID"]."</a></td><td>".$row["Time"]."</td><td>".$row["Status"]."</td><td>".$row["InVendor"]."</td><td>".$row["InNumber"]."</td><td>".$DeliveryOrderNum."</td><td>".$DeliveryItems."</td><td>".$DeliveryDimensions."</td><td><span id=\"".$row["TTID"]."\">".$DeliveryID."</span></td><td>".$row["InWeight"]."</td></tr>";

	}
}

$mysqli->close();

?>
</table>
</body>
</html>