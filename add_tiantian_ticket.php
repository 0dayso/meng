<?php
$TTID = $_GET["ttid"];

if (isset($TTID))
{
	require("conn.php");
	date_default_timezone_set('Asia/Shanghai');
	$mysqli = new mysqli($mysql_server_name, $mysql_username, $mysql_password, $mysql_database);
	
	$shippingdate = $_GET["sd"];
	$deliverydate = $_GET["dd"];

	$days = $_GET["d"];
	$businessdays = intval($_GET["bd"]);
	$fee = round($_GET["f"], 2);
	
	if ($businessdays > 25)
	{
		$rate = 15;
		$ret = round($fee*$rate/100, 2);
	}
	elseif ($businessdays > 15)
	{
		$rate = 5;
		$ret = round($fee*$rate/100, 2);
	}
	$data = array(
		'ticket_title' => htmlentities($TTID."清关时效理赔", ENT_QUOTES, "UTF-8"),
		'category_id' => '4',
		'ticket_zigou_id' => $TTID,
		'ticket_memo' => htmlentities("出库时间:".$shippingdate."; 清关完毕时间:".$deliverydate."; 共计: ".$days."日历日，".$businessdays."工作日; 运单金额: CNY".$fee."; 申请".$rate."%运费返还: CNY".$ret, ENT_QUOTES, "UTF-8"),
	);

	
	$url = "http://www.tiantian8.com/user3.php?act=new_ticket";
	$ch = curl_init();
	$timeout = 5; 
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_COOKIE, $tiantian_cookie);
	curl_setopt($ch, CURLOPT_POST, 1);
	curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);

	//Execute the  cURL session.
	//var_dump(http_build_query($data));
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
/*ticket_title:61244清关时效理赔
category_id:4
ticket_zigou_id:61244
ticket_memo:出库时间：2014-01-27 19:14:37

到货时间：2014-03-12 17:58:00

共计：44日历日，17工作日

运单金额：CNY390.00



申请5%运费返还：CNY19.50
*/


?>