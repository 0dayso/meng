<?php
//
// Barcode -> LegoID
//
	require("conn.php");
	$UPCCode = $_GET["upc"];
	$EANCode = $_GET["ean"];
	$ThirdCode = $_GET["third"];
	$BarCode = $_GET["bc"];
	$Action = $_GET["a"];
	$Output = $_GET["o"];
	
	date_default_timezone_set('Asia/Shanghai');
	$conn=mysql_connect($mysql_server_name, $mysql_username, $mysql_password) or die ("数据库错误：".mysql_error());
	mysql_query("SET NAMES UTF8;", $conn);
	mysql_query("SET time_zone = '+08:00';", $conn);

	if ($Action == "query")
	{
		$strCond = "";
		if (isset($UPCCode))
		{
			$strCond = " UPC = '".$UPCCode."'; ";
		}
		elseif (isset($EANCode))
		{
			$strCond = " EAN = '".$EANCode."'; ";
		}
		elseif (isset($ThirdCode))
		{
			$strCond = " 3rdCode = '".$ThirdCode."'; ";
		}
		elseif (isset($BarCode))
		{
			if (preg_match('/\d{13}/', $BarCode))
			{
				$strCond = " EAN = '".$BarCode."'; ";
			}
			elseif (preg_match('/\d{12}/', $BarCode))
			{
				$strCond = " UPC = '".$BarCode."'; ";
			}
			else
			{
				$strCond = " 3rdCode = '".$BarCode."'; ";
			}
		}
		else
		{
			die ("No input parameter.");
		}
		$strsql="SELECT LegoID FROM DB_Set WHERE".$strCond;
		$result=mysql_db_query($mysql_database, $strsql, $conn);
		if ($row=mysql_fetch_array($result))
		{
			echo $row['LegoID'];
		}
		else
		{
			echo "Unknown barcode";
		}
	}
	elseif ($Action == "get")
	{
	}
	elseif ($Action == "update")
	{
		echo "Update";
	}
	else
	{
		echo "No Action";
	}

?>