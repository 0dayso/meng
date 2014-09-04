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

if ($_POST["op"] == "forcefilter")
{
	$TaobaoID = $_POST["nid"];
	$query = "UPDATE TB_Item_Price SET `Force`=1, `Filter`=1 XOR `Filter` WHERE TaobaoID=".$mysqli->real_escape_string($TaobaoID).";";
	$result = $mysqli->query($query);
	echo "OK";
	exit();
}
else
{
	if (!isset($_GET["nooutput"]))
	{
?>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <link rel="stylesheet" href="table.css">
	<script type="text/javascript" src="jquery.js"></script>
    <script type="text/javascript" src="sorttable.js"></script>
    <script language="JavaScript">
	function change_filtered(nid)  
	{
		$("#btn_"+nid).attr("disabled","desabled");
		$.post("tb_price.php", { op: "forcefilter", nid: nid}, function(data) {update_filter(nid, data);} );
	}
	function update_filter(nid, data)
	{
		if (data == "OK")
		{
			var filter = parseInt($("#span_"+nid).html());
			$("#span_"+nid).html(1^filter);
			$("#btn_"+nid).val('->'+filter);
			$("#btn_"+nid).removeAttr("disabled");

		}
	}
	</script>
    <title>Taobao价格查询</title>
</head>
<body>
<table>
<?php
	}
require_once("simple_html_dom.php");

mb_internal_encoding('utf-8');

if ($_GET["legoid"])
{
	$legoid = $_GET["legoid"];
}
else
{
	$legoid = "9493";
}

if (isset($_GET["msrp"]))
{
	$LowPrice = $_GET["msrp"]*0.9;
	$HighPrice = $_GET["msrp"]*2;
}
else
{
	$query = "SELECT MIN(Price) AS LowPrice,TaobaoID FROM TB_Item_Price WHERE `Filter`=0 AND LegoID = '".$mysqli->real_escape_string($legoid)."';";
	$result = $mysqli->query($query);
	if ($row = $result->fetch_array(MYSQLI_ASSOC))
	{
		$LowPrice = $row['LowPrice']*0.9;
		$HighPrice = $row['LowPrice']*2;
		$TaobaoPrice = $row['LowPrice'];
		$LowTaobaoID = $row['TaobaoID'];
	}
	else
	{
		$query = "SELECT CNPrice,USPrice FROM DB_Set WHERE LegoID = '".$mysqli->real_escape_string($legoid)."';";
		$result = $mysqli->query($query);
		if ($row = $result->fetch_array(MYSQLI_ASSOC))
		{
			if ($row['CNPrice'] > 0)
			{
				$LowPrice = $row['CNPrice']*0.5;
				$HighPrice = $row['CNPrice']*2;
			}
			elseif ($row['USPrice'] > 0)
			{
				$LowPrice = $row['USPrice']*6.3*0.5;
				$HighPrice = $row['USPrice']*6.3*2;
			}
			else
			{
				$HighPrice = 0;
			}
		}
		else
		{
			$HighPrice = 0;
		}
	}
}

$query = "SELECT `TaobaoID`,`Price`,`Volume`,`Filter`,`Force`,`Title`,`Seller` FROM TB_Item_Price WHERE LegoID = '".$mysqli->real_escape_string($legoid)."';";
$result = $mysqli->query($query);
$dbitems = array();
while ($row = $result->fetch_array(MYSQLI_ASSOC))
{
	$iteminfo = new stdClass();

	$iteminfo->{'nid'} = $row['TaobaoID'];
	$iteminfo->{'price'} = round(floatval($row['Price']),2);
	$iteminfo->{'vol'} = intval($row['Volume']);
	$iteminfo->{'filter'} = intval($row['Filter']);
	$iteminfo->{'force'} = intval($row['Force']);
	$iteminfo->{'title'} = $row['Title'];
	$iteminfo->{'seller'} = $row['Seller'];
	$iteminfo->{'status'} = "notalive";

	$dbitems[$iteminfo->{'nid'}] = $iteminfo;
}

$keywords = array("\d{4}+", "[A-Za-z]{2,3}\d{3}", "代购", "二手", "租赁", "租金", "预定", "图纸", "貼紙", "说明书", "搭建图", "无盒", "微瑕", "瑕疵", "不含", "杀肉", "净场景", "载具", "配件", "零件", "散件", "单出", "国产", "乐高式", "乐高类", "邦宝", "博乐", "鲁班", "开智", "兼容", "DECOOL");

$pagecount = 1;
$items = array();
$totalvol = 0;
$totalseller = 0;
for ($i = 1; $i <= $pagecount; $i++)
{
	$url = "http://s.taobao.com/search?q=lego+".$legoid."&commend=all&sort=price-asc&s=".(($i-1)*40);
	$ch = curl_init(); 
	$timeout = 5; 
	curl_setopt($ch, CURLOPT_URL, $url); 
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 
	curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout); 
	$contents = curl_exec($ch); 
	curl_close($ch);
	$html = str_get_html($contents);

	$pagehtml = iconv('GBK', 'UTF-8', trim($html->find('div[class="pagination"]', 0)->plaintext));
	preg_match('/(\d+)\/(\d+)/', $pagehtml, $matches);
	$pagecount = $matches[2];

	$table = str_get_html($html->find('div[class="tb-content"]', 0)->innertext);
	$itemlist = $table->find('div[class="col item st-item icon-datalink"]');
	foreach ($itemlist as $item)
	{
		$itemhtml = str_get_html($item);
		
		$iteminfo = new stdClass();
		
		$idstr = iconv('GBK', 'UTF-8', trim($item->find('h3 a',0)->href)); 
		preg_match_all("/\?id=(\d+)\&/u", html_entity_decode($idstr, ENT_NOQUOTES, 'UTF-8'), $matchPrice);

		$iteminfo->{'nid'} = trim(array_pop(array_pop($matchPrice)));
		
		$iteminfo->{'title'} = iconv('GBK', 'UTF-8', trim($itemhtml->find('div[class="item-box"]/h3[class="summary"]/a', 0)->title));
		
		$filter = 0;
		$reason = "";

		$strPrice = iconv('GBK', 'UTF-8', trim($itemhtml->find('div[class="col price g_price g_price-highlight"]',0)->innertext));
		preg_match_all("/\d+\.\d{2}/u", html_entity_decode($strPrice, ENT_NOQUOTES, 'UTF-8'), $matchPrice);
		$iteminfo->{'price'} = round(floatval(array_pop(array_pop($matchPrice))),2);

		if ($HighPrice > 0)
		{
			if ($iteminfo->{'price'} > $HighPrice)
			{
				$filter = 1;
				$reason = "Price over than $HighPrice.";
			}
			elseif ($iteminfo->{'price'} < $LowPrice)
			{
				$filter = 1;
				$reason = "Price less than $LowPrice.";
			}
		}
		
		if (!$filter)
		{
			$replacedtitle = preg_replace("/lego/iu", "", preg_replace("/19|20\d{2}/u", "", str_replace($legoid, "", html_entity_decode($iteminfo->{'title'}, ENT_NOQUOTES, 'UTF-8'))));
			foreach ($keywords as $keyword)
			{
				if (preg_match("/".$keyword."/u", $replacedtitle))
				{
					$filter = 1;
					$reason = $keyword." matched.";
					break;
				}
			}
		
			if (preg_match("/".$legoid."-/u", html_entity_decode($iteminfo->{'title'}, ENT_NOQUOTES, 'UTF-8')))
			{
				$filter = 1;
				$reason = "Subsets.";
			}
		}
		$iteminfo->{'filter'} = $filter;
		$iteminfo->{'reason'} = $reason;
		
		$strSold = iconv('GBK', 'UTF-8', trim($itemhtml->find('div[class="col end dealing"]',0)->innertext));
		preg_match_all("/\d+/u", html_entity_decode($strSold, ENT_NOQUOTES, 'UTF-8'), $matchSold);
		$iteminfo->{'vol'} = intval(array_pop(array_pop($matchSold)));

		$iteminfo->{'seller'} = iconv('GBK', 'UTF-8', trim($itemhtml->find('div[class="row"] div a',0)->innertext));
		$iteminfo->{'loc'} = iconv('GBK', 'UTF-8', trim($itemhtml->find('div[class="col end loc"]',0)->innertext));

		if (isset($dbitems[$iteminfo->{'nid'}]))
		{
			$dbitem = $dbitems[$iteminfo->{'nid'}];
			
			//var_dump($dbitem, $item);

			if ($dbitem->{'force'})
			{
				$iteminfo->{'filter'} = $dbitem->{'filter'};
				$iteminfo->{'reason'} = "force set filter=".$dbitem->{'filter'};
			}

			if ($iteminfo->{'filter'} <> $dbitem->{'filter'})
			{
				$iteminfo->{'status'} = "newfilter";
				$iteminfo->{'reason'} = "filter changed:".$dbitem->{'filter'}."->".$iteminfo->{'filter'};
			}
			elseif ($iteminfo->{'filter'})
			{
				$iteminfo->{'status'} = "filtered";
			}
			elseif ($dbitem->{'price'} <> $iteminfo->{'price'} || $dbitem->{'vol'} <> $iteminfo->{'vol'})
			{
				$iteminfo->{'status'} = "updated";
			}
			else
			{
				$iteminfo->{'status'} = "noupdate";
			}
		}
		else
		{
			$iteminfo->{'status'} = "new";
		}

		array_push($items, $iteminfo);
		if (!$iteminfo->{'filter'})
		{
			$totalvol += $iteminfo->{'vol'};
			$totalseller++;
		}
	}

}

if (isset($TaobaoPrice))
{
	$LowPriceStr = "\t\t淘宝最低价：<a href='http://item.taobao.com/item.htm?id=".$LowTaobaoID."'>".$TaobaoPrice."</a>";
}

echo "<p>编号：".$legoid.$LowPriceStr."\t\t建议价格：".$LowPrice."~".$HighPrice."\t\t商家数量：".$totalseller."\t\t30天总销量：".$totalvol."</p>\r\n";
if (!isset($_GET["nooutput"]))
{
echo "<table class=\"sortable\"><tr><th>Link</th><th>Title</th><th>Price</th><th>Vol</th><th class=\"sorttable_nosort\">Seller</th><th>Location</th><th>Fil</th><th>Reason</th><th>Status</th></tr>";
}
foreach ($items as $item)
{
	if (isset($_GET["updatedb"]))
	{
		if ($item->{'status'}=="new")
		{
			$query = "INSERT INTO TB_Item_Price(`TaobaoID`, `LegoID`, `Price`, `Seller`, `Location`, `Title`, `Volume`, `Filter`) VALUES (".$mysqli->real_escape_string($item->{'nid'}).",'".$mysqli->real_escape_string($legoid)."','".$mysqli->real_escape_string($item->{'price'})."','".$mysqli->real_escape_string($item->{'seller'})."','".$mysqli->real_escape_string($item->{'loc'})."','".$mysqli->real_escape_string($item->{'title'})."','".$mysqli->real_escape_string($item->{'vol'})."',".$mysqli->real_escape_string($item->{'filter'}).");";
			$result = $mysqli->query($query);
		}
		elseif ($item->{'status'}=="newfilter")
		{
			$query = "UPDATE TB_Item_Price SET `Filter`=".$mysqli->real_escape_string($item->{'filter'})." WHERE TaobaoID=".$mysqli->real_escape_string($item->{'nid'}).";";
			$result = $mysqli->query($query);
		}
		elseif ($item->{'status'}=="updated")
		{
			$query = "UPDATE TB_Item_Price SET `Price`=".$mysqli->real_escape_string($item->{'price'}).",`Volume`=".$mysqli->real_escape_string($item->{'vol'})." WHERE TaobaoID=".$mysqli->real_escape_string($item->{'nid'}).";";
			$result = $mysqli->query($query);
			$change = "Price:".$dbitems[$item->{'nid'}]->{'price'}."->".$item->{'price'}." Vol:".$dbitems[$item->{'nid'}]->{'vol'}."->".$item->{'vol'};
			$query = "INSERT INTO TB_Item_Changes(`TaobaoID`, `LegoID`, `Seller`, `Change`) VALUES (".$mysqli->real_escape_string($item->{'nid'}).",'".$mysqli->real_escape_string($legoid)."','".$mysqli->real_escape_string($item->{'seller'})."','".$mysqli->real_escape_string($change)."');";
			$result = $mysqli->query($query);
		}
	}
	if (!isset($_GET["nooutput"]))
	{
		echo "<tr><td><a href='http://item.taobao.com/item.htm?id=".$item->{'nid'}."'>".$item->{'nid'}."</a></td><td>".$item->{'title'}."</td><td>".$item->{'price'}."</td><td>".$item->{'vol'}."</td><td>".$item->{'seller'}."</td><td>".$item->{'loc'}."</td><td><span id=\"span_".$item->{'nid'}."\">".$item->{'filter'}."</span><input id=\"btn_".$item->{'nid'}."\" type=\"button\" value=\"->".(1^$item->{'filter'})."\" onclick=\"change_filtered(".$item->{'nid'}.");\"/></td><td>".$item->{'reason'}."</td><td>".$item->{'status'}."</td></tr>";
	}
	unset($dbitems[$item->{'nid'}]);
}
foreach ($dbitems as $item)
{
	if (isset($_GET["updatedb"]))
	{
		if ($item->{'status'}=="notalive")
		{
			$query = "UPDATE TB_Item_Price SET `Filter`=1 WHERE TaobaoID=".$mysqli->real_escape_string($item->{'nid'}).";";
			$result = $mysqli->query($query);
		}
	}
	if (!isset($_GET["nooutput"]))
	{
	echo "<tr><td><a href='http://item.taobao.com/item.htm?id=".$item->{'nid'}."'>".$item->{'nid'}."</a></td><td>".$item->{'title'}."</td><td>".$item->{'price'}."</td><td>".$item->{'vol'}."</td><td>".$item->{'seller'}."</td><td>".$item->{'loc'}."</td><td><span id=\"span_".$item->{'nid'}."\">".$item->{'filter'}."</span><input id=\"btn_".$item->{'nid'}."\" type=\"button\" value=\"->".(1^$item->{'filter'})."\" onclick=\"change_filtered(".$item->{'nid'}.");\"/></td><td>".$item->{'reason'}."</td><td>".$item->{'status'}."</td></tr>";
	}
}

/*
Low price & Sold vol
SELECT LegoID,MIN(Price) AS LowPrice, SUM(Volume) AS TotalVolume FROM `TB_Item_Price` WHERE Filter=0 GROUP BY LegoID ORDER By LowPrice;
*/

if (!isset($_GET["nooutput"]))
{
?>
</table>
</body>
</html>
<?php
}
}
?>