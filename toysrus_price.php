<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <link rel="stylesheet" href="table.css">
	<script type="text/javascript" src="jquery.js"></script>
	<script type="text/javascript" src="scripts.js"></script>
	<script type="text/javascript" src="sorttable.js"></script>
	<script language="JavaScript">
	</script>
    <title>Toysrus信息</title>
</head>
<body>
<div id="info"></div>
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

if ($_GET["action"]=="update")
{
	foreach($_POST as $k=>$v)
	{
		if (preg_match("/^rad_(\w+)/",$k,$match))
		{
			$ToysrusID = $match[1];
			if ($v == "import")
			{
				$LegoID = $_POST["$ToysrusID"];
				$query = "INSERT INTO Toysrus_Item (LegoID, ToysrusID) VALUES ('".$mysqli->real_escape_string($LegoID)."', '".$mysqli->real_escape_string($ToysrusID)."');";
				$result = $mysqli->query($query);
				echo $query." ".$result."<br/>";
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
	$mysqli->close();
	exit;
}
else
{
	$query = "SELECT * FROM DB_Set;";
	$result = $mysqli->query($query);

	$Sets = array();
	while ($row = $result->fetch_array(MYSQLI_ASSOC))
	{
		$setinfo = new stdClass();
		$LegoID = $row["LegoID"];
		$setinfo->{'ETitle'} = $row["ETitle"];
		$setinfo->{'CTitle'} = $row["CTitle"];
		$setinfo->{'Year'} = $row["Year"];
		$setinfo->{'Pieces'} = $row["Pieces"];
		$setinfo->{'Weight'} = $row["Weight"];
		$setinfo->{'Length'} = $row["Length"];
		$setinfo->{'Width'} = $row["Width"];
		$setinfo->{'Height'} = $row["Height"];
		$setinfo->{'ETheme'} = $row["ETheme"];
		$setinfo->{'CTheme'} = $row["CTheme"];
		$setinfo->{'USPrice'} = $row["USPrice"];
		$setinfo->{'CNPrice'} = $row["CNPrice"];
		$Sets["$LegoID"] = $setinfo;
	}
	
	$query = "SELECT LegoID,LowPrice FROM V_TB_Price;";
	$result = $mysqli->query($query);

	$TaobaoPrices = array();
	while ($row = $result->fetch_array(MYSQLI_ASSOC))
	{
		$taobaoinfo = new stdClass();

    	$taobaoinfo->{'Price'} = $row['LowPrice'];
    	//$taobaoinfo->{'Mins'} = round((time()-strtotime($row['UpdateTime']))/60);
    	$TaobaoPrices[$row['LegoID']] = $taobaoinfo;

	}
	
	$query = "SELECT * FROM Toysrus_Item;";
	$result = $mysqli->query($query);

	$ToysrusItems = array();
	while ($row = $result->fetch_array(MYSQLI_ASSOC))
	{
    	$ToysrusItems[$row['ToysrusID']] = $row['LegoID'];
	}

	echo "<table class=\"sortable\"><tr><th>Lego ID</th><th>US Price</th><th>Toysrus Price</th><th>Discount</th><th>Shipping Fee</th><th>CN MSRP</th><th>到手价</th><th class=\"sorttable_nosort\">Taobao Price</th><th>Rev</th><th>Rev Rate</th></tr>";
	
	require_once("simple_html_dom.php");
	mb_internal_encoding('utf-8');
	if ($_GET["page"])
	{
		$page = $_GET["page"];
	}
	else
	{
		$page = 4;
	}
	$show = 1;
			
	$showed = array();

	for ($i = 1; $i <= $page; $i++)
	{
		$url = 'http://www.toysrus.com/family/index.jsp?categoryId=31820206&view=all&page='.$i;
		$ch = curl_init(); 
		$timeout = 5; 
		curl_setopt($ch, CURLOPT_URL, $url); 
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout); 
		$contents = curl_exec($ch); 
		curl_close($ch);
		$html = str_get_html($contents);
		/*
		if ($html->find('span[class="pagnDisabled"]',0))
		{
			$page = trim($html->find('span[class="pagnDisabled"]',0)->innertext);
		}
		*/
		if ($page > 1 && $show == 1)
		{
			echo "共".$page."页，正在读取：";
			$show = 0;
		}
		echo $i."..";
		$items = $html->find('//*[@id="familyProducts"]/div/div/div[class="prodloop_cont"]');
		foreach ($items as $item)
		{
			$itemhtml = str_get_html($item);
			if ($objdoc = $itemhtml->find('/div[class="varHeightTop"]/div[class="prodloop-thumbnail"]/div[class="expressShopButtonGlobal button"]',0))
			{
				if (isset($objdoc->innertext))
				{
					$toysrusID = $objdoc->getAttribute('data-productid');
				}
			}
			if (isset($ToysrusItems[$toysrusID]) && !isset($showed[$toysrusID]))
			{
				if ($itemhtml->find('/div[class="varHeightTop"]/div/span[class="adjusted ourPrice2"]',0))
				{
					$price = str_replace("$", "", trim($itemhtml->find('/div[class="varHeightTop"]/div/span[class="adjusted ourPrice2"]',0)->innertext));
					$onsale = true;
				}
				elseif ($itemhtml->find('/div[class="varHeightTop"]/div/span[class="ourPrice2"]',0))
				{
					$price = str_replace("$", " ", trim($itemhtml->find('/div[class="varHeightTop"]/div/span[class="ourPrice2"]',0)->innertext));
					$onsale = false;
				}
				
				preg_match_all("/(\d+\.\d+)/u", html_entity_decode($price, ENT_NOQUOTES, 'UTF-8'), $matches);
				$price = floatval(array_pop(array_pop($matches)));
				
				$promotext = $itemhtml->find('/span/font',0)->plaintext;
				if ($promotext == "Buy 1 Get 1 40% off!**")
				{
					$price = round($price * 0.80, 2);
				}
				$shiptohomedoc = $itemhtml->find('/div[class="varHeightTop"]/ul/li[1]',0);
				if (($shiptohomedoc->innertext == "Ship-To-Home") && ($shiptohomedoc->getAttribute('class') != "unavail"))
				{
					$legoid = $ToysrusItems[$toysrusID];
					$msrp = $Sets[$legoid]->{'USPrice'};
					$discount = 0;
					if ($msrp > 0)
					{
						$discount = round($price/$msrp*100,2);
					}
					else
					{
						$disconut = 100;
					}
					$shipping = round($Sets[$legoid]->{'Weight'}*40/453.59237, 2);
					$totalprice = round($price*6.2+$shipping, 2);
					$cnprice = $Sets[$legoid]->{'CNPrice'};
					$klprice = round($cnprice*0.6, 2);
					$taobaolink = "tb_price.php?legoid=".$legoid;

					if (isset($TaobaoPrices[$legoid]))
					{
						$taobaoprice = $TaobaoPrices[$legoid]->{'Price'};
						$taobaotext = "<a href=\"$taobaolink\">¥".$taobaoprice."</a>";
					}
					elseif ($cnprice > 0)
					{
						$taobaoprice = round($cnprice*0.6, 2);
						$taobaotext = "<a href=\"$taobaolink\">~¥".$taobaoprice."</a>";
					}
					else
					{
						$taobaoprice = 0;
						$taobaotext = "<a href=\"$taobaolink\">N/A</a>";
					}
					if (($taobaoprice > 0) && ($shipping > 0))
					{
						$rev = round($taobaoprice-$totalprice, 2);
						$revrate = round($rev/$totalprice*100, 2);
					}
					else
					{
						$rev = 0;
						$revrate = 0;
					}

					echo "<tr><td><a href=\"http://www.toysrus.com/product/index.jsp?productId=$toysrusID\"><img src=\"pic.php?thumb150=".$legoid."\"><br/>$legoid</a></td><td>$msrp</td><td>$price</td><td>$discount</td><td>$shipping</td><td>$klprice/$cnprice</td><td>$totalprice</td><td>$taobaotext</td><td>$rev</td><td>$revrate</td></tr>";
					$showed[$toysrusID] = 1;
				}
			}
		}
	}
}
?>
</table>
<input type="submit" value="批量导入" />
</body>
</html>
