<?php
require("conn.php");
date_default_timezone_set('Asia/Shanghai');

$ret = array();
if ($_GET['op'] == "del" && isset($_GET['id']))
{
	$DeleteID = $_GET['id'];
	$mysqli = new mysqli($mysql_server_name, $mysql_username, $mysql_password, $mysql_database);

	if (mysqli_connect_errno())
	{
		$ret['ReturnCode'] = 1;
		$ret['ReturnHTML'] = "<p>".mysqli_connect_error()."</p>";
		echo json_encode($ret);
		exit();
	}

	$mysqli->query("SET NAMES UTF8;");
	$mysqli->query("SET time_zone = '+08:00';");

	$query = "DELETE FROM TB_Snapshot_List WHERE SSID=".$mysqli->real_escape_string($DeleteID);
	if (!$mysqli->query($query))
	{
		$ret['ReturnCode'] = 1;
		$ret['ReturnHTML'] = "<p>".$mysqli->error."</p>";
	}
	$query = "DELETE FROM TB_Snapshot WHERE SSID=".$mysqli->real_escape_string($DeleteID);
	if (!$mysqli->query($query))
	{
		$ret['ReturnCode'] = 1;
		$ret['ReturnHTML'] = "<p>".$mysqli->error."</p>";
	}
	
	$ret['ReturnCode'] = 0;
	$ret['ReturnHTML'] = "<p>成功删除快照".$DeleteID.".</p>";

	$mysqli->close();

}
elseif ($_GET['op'] == "comp" && isset($_GET['id1']) && isset($_GET['id2']))
{
	$CompareID1 = $_GET['id1'];
	$CompareID2 = $_GET['id2'];
	
	if (isset($_GET['rth']))
	{
		$ratethreshold = $_GET['rth'];
	}
	else
	{
		$ratethreshold = 3;
	}
	$mysqli = new mysqli($mysql_server_name, $mysql_username, $mysql_password, $mysql_database);

	if (mysqli_connect_errno())
	{
		$ret['ReturnCode'] = 1;
		$ret['ReturnHTML'] = "<p>".mysqli_connect_error()."</p>";
		echo json_encode($ret);
		exit();
	}

	$mysqli->query("SET NAMES UTF8;");
	$mysqli->query("SET time_zone = '+08:00';");

	$query = "SELECT * FROM TB_Snapshot WHERE SSID=".$mysqli->real_escape_string($CompareID1)." OR SSID=".$mysqli->real_escape_string($CompareID2).";";
	$result = $mysqli->query($query);

	$Items1 = array();
	$Items2 = array();
	while ($row = $result->fetch_array(MYSQLI_ASSOC))
	{
		$Item = new stdClass();
		
		$LegoID = $row["LegoID"];
		$Item->{'TaobaoID'} = $row["TaobaoID"];
		$Item->{'Price'} = $row["Price"];
		$Item->{'Volume'} = $row["Volume"];
		$Item->{'Sellers'} = $row["Sellers"];

		if ($row['SSID'] == $CompareID1)
		{
			$Items1[$LegoID] = $Item;
		}
		else
		{
			$Items2[$LegoID] = $Item;

		}
	}
	$mysqli->close();
	
	$arrPriceRise = array();
	$arrPriceDrop = array();
	$arrVolumeIncrease = array();
	$count = 0;
	//var_dump($Items1,$Items2);
	foreach ($Items1 as $LegoID=>$Item)
	{
		if (isset($Items2[$LegoID]))
		{
			//LegoID在2次快照均存在
			if ($Item->{'Price'} < $Items2[$LegoID]->{'Price'})
			{
				//涨价
				$PriceRise = new stdClass();
				$PriceRise->{'Old'} = round($Item->{'Price'}, 2);
				$PriceRise->{'New'} = round($Items2[$LegoID]->{'Price'}, 2);
				$PriceRise->{'Delta'} = round($Items2[$LegoID]->{'Price'} - $Item->{'Price'}, 2);
				$PriceRise->{'Rate'} = round($PriceRise->{'Delta'} / $Item->{'Price'}*100, 2);
				if ($PriceRise->{'Rate'} > 3)
				{
					$arrPriceRise[$LegoID] = $PriceRise;
				}
			}
			elseif ($Item->{'Price'} > $Items2[$LegoID]->{'Price'})
			{
				//跌价
				$PriceDrop = new stdClass();
				$PriceDrop->{'Old'} = round($Item->{'Price'}, 2);
				$PriceDrop->{'New'} = round($Items2[$LegoID]->{'Price'}, 2);
				$PriceDrop->{'Delta'} = round($Items2[$LegoID]->{'Price'} - $Item->{'Price'}, 2);
				$PriceDrop->{'Rate'} = round($PriceDrop->{'Delta'} / $Item->{'Price'}*100, 2);
				if ($PriceDrop->{'Rate'} < -3)
				{
					$arrPriceDrop[$LegoID] = $PriceDrop;
				}
			}
			
			if ($Item->{'Volume'} < $Items2[$LegoID]->{'Volume'})
			{
				//出货量增加
				$VolumeIncrease = new stdClass();
				$VolumeIncrease->{'Old'} = round($Item->{'Volume'}, 2);
				$VolumeIncrease->{'New'} = round($Items2[$LegoID]->{'Volume'}, 2);
				$VolumeIncrease->{'Delta'} = round($Items2[$LegoID]->{'Volume'} - $Item->{'Volume'}, 2);
				if ($Item->{'Volume'} > 0)
				{
					$VolumeIncrease->{'Rate'} = round($VolumeIncrease->{'Delta'} / $Item->{'Volume'}*100, 2);
				}
				else
				{
					$VolumeIncrease->{'Rate'} = "N/A";
				}
				$arrVolumeIncrease[$LegoID] = $VolumeIncrease;
			}

			unset($Items1[$LegoID]);
			unset($Items2[$LegoID]);
			$count++;
		}
	}
	//$Items1中剩下的为退市商品，$Items2中剩下的为新上商品
	$ret['NochangeCount'] = $count;
	$ret['PriceRise'] = $arrPriceRise;
	$ret['PriceDrop'] = $arrPriceDrop;
	$ret['VolumeIncrease'] = $arrVolumeIncrease;
	$ret['OutMarket'] = $Items1;
	$ret['NewShow'] = $Items2;
}
echo json_encode($ret);
?>
