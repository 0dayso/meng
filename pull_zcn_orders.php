<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <link rel="stylesheet" href="table.css">
	<script type="text/javascript" src="jquery.js"></script>
    <script type="text/javascript" src="sorttable.js"></script>
	</script>
    <title>ZCN订单内容抓取!</title>
</head>
<body>
<?php
require_once("simple_html_dom.php");
require("conn.php");

$pg = 0;
if (isset($_GET["pg"]))
{
	if ($_GET["pg"] >= 1)
	{
		$pg = ($_GET["pg"]-1)*10;
	}
}

date_default_timezone_set('Asia/Shanghai');
$mysqli = new mysqli($mysql_server_name, $mysql_username, $mysql_password, $mysql_database);

if (mysqli_connect_errno()) {
	printf("Database Connect failed: %s\n", mysqli_connect_error());
	exit();
}

$mysqli->query("SET NAMES UTF8;");
$mysqli->query("SET time_zone = '+08:00';");

$query = "SELECT LastValue,ASIN,LegoID FROM PW_AmazonInfo WHERE Country='CN';";
$result = $mysqli->query($query);

$CNItems = array();
while ($row = $result->fetch_array(MYSQLI_ASSOC))
{
	$LegoID = $row["LegoID"];
	$ASIN = $row["ASIN"];
	$CNItems["$ASIN"] = $LegoID;
}

$url = "https://www.amazon.cn/gp/css/order-history/?ie=UTF8&orderFilter=months-6&search=&startIndex=".$pg;
$ch = curl_init();
$timeout = 5; 
$cookie = "x-wl-uid=1Ri3DZ9Um9zDk/2BWpVQHATrs5680Qf0byI7wYOWAgI1nKQYer9MDbreztJq3gHy/KW16gzD7ZaCAFUoTofwNr37u/q4vvRAw6Zg4AsfSq9QmyRMivobTM4N4dXvD7WwYoJ1coKYypkE=; s_ppv=74; 5SnMamzvowels.time.2=1408348670461; 5SnMamzvowels.time.3=1408432838171; session-token=W+ORStPXowdsrDF4PVZPPYUdqw/aDma7nfeBTeDoWEwBHT/54WJgI91SZ8hlcJL5z3AA8hQ4DuP/cUvQXzg5tCacl+DWTtXuOxr4jdX3cjVWYhtl0MyObUoVEosR/4amw9dOBq/7x6g7kTraw46VpY3G40vGKfHGvAZjiL4+5w3x90w4u0jaPfcJz+YM6JRq8lreKvXf0uvBjmdctLJNoXMukS8U+S5tTa7mNsmDslAbvABLA8/L3BGnc0djs+VB; x-acbcn=dBkU?xOiLQT6knvz3wFu?Uqu9J6GeUA3; at-main=5|fWIWUC7s1DKL/oj1OGets2lZE+hrgA/rv0JccXRERFdQrzMNEN//3YomO932sOYw2QPftXuSsStMsFe0fF14CNXH8uZxw5dpU0+rROHce2MAFOKGDzgl91Jg0OLo9EORRPGocbdfeTRX8ffohg+ATUycqU/7indpjTQ0rzqU+wq5vCt8viEWhMegqaiO4qi736KVmoKIr3PR0hMTyc1nh0g2OMMs0ULLqIEU/yazykI=; sess-at-main=uzcQNYRFBa0x2E6BMiHOKVSUAD2aJRieAzany52I1SU=; 5SnMamzvowels.time.4=1408955312664; s_cc=true; s_nr=1408980160366-Repeat; s_vnum=1832482447836%26vn%3D2; s_dslv=1408980160368; s_sq=%5B%5BB%5D%5D; 5SnMamzvowels.time.0=1409126296468; 5SnMamzvowels.pos=2; 5SnMamzvowels.time.1=1409561840737; csm-hit=s-1ZQ6J7NVV9RD9HYJJM3D|1409561856178; ubid-acbcn=477-8892292-0251251; session-id-time=2082729601l; session-id=475-6644749-1846550";

curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_COOKIE, $cookie);
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



$html = str_get_html($strResponse);

$orders = $html->find('div[class=action-box rounded]');

foreach ($orders as $order)
{
	$orderdom = str_get_html($order->innertext);
	$ordernum = trim($orderdom->find('span[class=info-data] a',0)->innertext);
	$ordertime = trim($orderdom->find('div[class=order-level] h2',0)->innertext);
	$orderprice = str_replace("￥ ", "", trim($orderdom->find('ul[class=order-details] li span[class=price]',0)->innertext));
	echo "下单: ".str_replace("年", "-", str_replace("月", "-", str_replace("日", "", $ordertime)))."<br/>";
	echo "订单: ".$ordernum."<br/>";
	echo "金额: ".$orderprice."<br/>";
	$url = "https://www.amazon.cn/gp/css/summary/edit.html/?ie=UTF8&orderID=".$ordernum;
	curl_setopt($ch, CURLOPT_URL, $url);
	$strResponse = curl_exec($ch);

	$html = str_get_html($strResponse);

	if (preg_match("/\/gp\/css\/shiptrack\/view\.html\/ref=od_track_pkg_btn_refresh_T1\?ie=UTF8&amp;orderID=$ordernum(.*)ref=/", $strResponse, $matches))
	{
		$url = "https://www.amazon.cn".str_replace("&amp;", "&", $matches[0]);
		curl_setopt($ch, CURLOPT_URL, $url);
		$strResponse = curl_exec($ch);
		$htmltrack = str_get_html($strResponse);
		
		$tracknum = trim($htmltrack->find('div[class=a-box] div[class=a-box-inner] span[4]',0)->plaintext);
		echo "运单: ".$tracknum."<br/>";
		
		$times = $htmltrack->find('table[class=a-normal] tbody tr td span[class=a-size-base]');
		$arrtime = array();
		foreach ($times as $time)
		{
			if (preg_match("/(\d+)年/", $time->plaintext))
			{
				$arr = explode(", ", $time->plaintext);
				$date = str_replace("年", "-", str_replace("月", "-", str_replace("日", "", $arr[0])))." ".$arr[1];
				array_push($arrtime, $date);
			}
		}
		echo "发货: ".$arrtime[0]."<br/>";
		echo "到货: ".array_pop($arrtime)."<br/>";
		
	}
	$items = $html->find('div[style=float:left; max-width:500px; margin:0 10px 0 10px;]');
	foreach ($items as $item)
	{
		$itemdom = str_get_html($item->innertext);
		$numstr = trim($itemdom->plaintext);
		if (preg_match("/(\d+)\s+件：/", $numstr, $matches))
		{
			$num = $matches[1];
		}
		else
		{
			$num = 1;
		}
		$url = $itemdom->find('b a', 0)->href;
		if (preg_match("/http:\/\/www\.amazon\.cn\/gp\/product\/([\w]+)\//", $url, $matches))
		{
			$ASIN = $matches[1];
		}
		echo $num."*".$CNItems["$ASIN"]." ";
	}
	echo "<br/><br/>";
}

curl_close($ch);
?>
</body>
</html>