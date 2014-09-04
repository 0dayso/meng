<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <link rel="stylesheet" href="table.css">
    <script src="sorttable.js"></script>
    <title>更新商品编码</title>
</head>
<body>
<form id="form" action="taobao_item.php?action=updatecode" method="post">
<?php

include "appconf.php";
include "TopSdk.php";	

require("conn.php");

date_default_timezone_set('Asia/Shanghai');
$mysqli = new mysqli($mysql_server_name, $mysql_username, $mysql_password, $mysql_database);

if (mysqli_connect_errno()) {
	printf("Database Connect failed: %s\n", mysqli_connect_error());
	exit();
}

$mysqli->query("SET NAMES UTF8;");
$mysqli->query("SET time_zone = '+08:00';");

$query = "SELECT ItemID,LegoID FROM TB_Item;";
$result = $mysqli->query($query);

$TBItems = array();
while ($row = $result->fetch_array(MYSQLI_ASSOC))
{
	$ItemID = $row["ItemID"];
	$LegoID = $row["LegoID"];
	$TBItems["$ItemID"] = $LegoID;
}
$result->free();
$mysqli->close();

echo "<table class=\"sortable\"><tr><th>ItemID</th><th>Title</th><th>当前头图</th><th>更新头图</th><th>当前编码</th><th>更新编码</th><th>Operations</th></tr>";


//实例化TopClient类
$c = new TopClient;
$c->appkey = $client_id;
$c->secretKey = $client_secret;
$sessionKey = $access_token;

$reqItems = new ItemsOnsaleGetRequest;
//$reqItems = new ItemsInventoryGetRequest;
//$reqItems->setBanner("for_shelved");
//$reqItems->setBanner("sold_out");

$reqItems->setFields("num_iid,title,outer_id,pic_url");
$reqItems->setOrderBy("delist_time:asc");
$page = 1;
$pages = 1;
$pagesize = 40;

while ($page <= $pages)
{
	$reqItems->setPageNo($page);
	$reqItems->setPageSize($pagesize);
	$respItems = $c->execute($reqItems, $sessionKey);
	$total = $respItems->total_results;
	$pages = ceil($total/$pagesize);

	foreach ($respItems->items->item as $item)
	{
		$ItemID = $item->num_iid;
		$LegoID = $TBItems["$ItemID"];
		if (isset($LegoID))
		{
			//preg_match('/(\d+)/', $item->title, $matches);
			//$legoMatchid = $matches[1];
			echo "<tr><td><a href=\"http://item.taobao.com/item.htm?id=".$item->num_iid."\">".$item->num_iid."</a></td><td>".$item->title."</td><td><img height=120px width=120px src=\"".$item->pic_url."\"></td><td></td><td>".$item->outer_id."</td><td><input type=\"text\" name=\"".$item->num_iid."\" size=\"8\"value=\"".$LegoID."\" /></td>";
			if (preg_match('/\d{3,}/', $LegoID) && $LegoID <> $item->outer_id)
			{
				echo "<td><input type=\"radio\" checked=\"checked\" name=\"rad_".$item->num_iid."\" value=\"import\" />更新<input type=\"radio\" name=\"rad_".$item->num_iid."\" value=\"ignore\" />暂不</td>";
			}
			else
			{
				echo "<td><input type=\"radio\" name=\"rad_".$item->num_iid."\" value=\"import\" />更新<input type=\"radio\" checked=\"checked\" name=\"rad_".$item->num_iid."\" value=\"ignore\" />暂不</td>";
			}
			echo "</tr>";
		}
	}
	$page++;
}

echo "</table>";
?>
<input type="submit" value="批量更新" />
</form>
</body>
</html>
