<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <link rel="stylesheet" href="table.css">
	<script type="text/javascript" src="jquery.js"></script>
    <script type="text/javascript" src="sorttable.js"></script>
	</script>
    <title>Tiantian内容抓取!</title>
</head>
<body>
<table>
<tr><th>时间</th><th>TT单号</th><th>状态</th><th>快递</th><th>快递单号</th><th>入库重量</th><th>出库重量</th><th>转运重量</th><th>转运费</th><th>转运快递</th><th>转运单号</th><th>名称*数量</th><th>明细</th><th>备注</th></tr>
<?php
	require_once("simple_html_dom.php");
	require("conn.php");

	date_default_timezone_set('Asia/Shanghai');
	$mysqli = new mysqli($mysql_server_name, $mysql_username, $mysql_password, $mysql_database);

	if (mysqli_connect_errno()) {
		printf("Database Connect failed: %s\n", mysqli_connect_error());
		exit();
	}

	$mysqli->query("SET NAMES UTF8;");
	$mysqli->query("SET time_zone = '+08:00';");

	for ($i=1; $i<=20; $i++)
	{
		$url = "http://www.tiantian8.com/user3.php?act=zigou_list&page=".$i;
		$ch = curl_init();
		$timeout = 5; 
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_COOKIE, $tiantian_cookie);
		//curl_setopt($ch, CURLOPT_POST, 1);
		//curl_setopt($ch, CURLOPT_POSTFIELDS, "ddzt=10");
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

		$ttlist = array();
		$html = str_get_html($strResponse);
		$table = $html->find('table[class=formTable] tr[!align]');
		foreach ($table as $line)
		{
			$linedom = str_get_html($line->innertext);
			$ttinfo = new stdClass();
			$ttinfo->{'id'} = trim($linedom->find('td a',0)->innertext);
			$ttinfo->{'time'} = trim($linedom->find('td[3]',0)->innertext);
			$ttinfo->{'cargo'} = trim($linedom->find('td[4]',0)->innertext);
			$ttinfo->{'num'} = trim($linedom->find('td[5]',0)->innertext);
			$ttinfo->{'status'} = trim($linedom->find('td[6]',0)->innertext);
			get_ttdetail($ttinfo);

			array_push($ttlist, $ttinfo);
			
			$ID = "TT000".$ttinfo->{'id'};
			
			$query = "INSERT INTO `Tiantian_Delivery`(`TTID`, `Time`, `Status`, `InVendor`, `InNumber`, `InWeight`, `OutWeight`, `TransitWeight`, `Fee`, `TrVendor`, `TrNumber`, `Detail`, `Title`, `StatusDetail`) VALUES ('".$ID."', '".$ttinfo->{'time'}."', '".$ttinfo->{'status'}."', '".$ttinfo->{'vendor'}."', '".$ttinfo->{'tracknum'}."', '".$ttinfo->{'inweight'}."', '".$ttinfo->{'outweight'}."', '".$ttinfo->{'transitweight'}."', '".$ttinfo->{'shippingfee'}."', '".$ttinfo->{'transitvendor'}."', '".$ttinfo->{'transittracnum'}."', '".$mysqli->real_escape_string($ttinfo->{'cargodetail'})."', '".$mysqli->real_escape_string($ttinfo->{'cargo'}."*".$ttinfo->{'num'})."', '".$mysqli->real_escape_string($ttinfo->{'statustext'})."') ON DUPLICATE KEY UPDATE `Status`='".$ttinfo->{'status'}."',`InVendor`='".$ttinfo->{'vendor'}."',`InNumber`='".$ttinfo->{'tracknum'}."',`InWeight`='".$ttinfo->{'inweight'}."',`OutWeight`='".$ttinfo->{'outweight'}."',`TransitWeight`='".$ttinfo->{'transitweith'}."',`Fee`='".$ttinfo->{'shippingfee'}."',`TrVendor`='".$ttinfo->{'transitvendor'}."',`TrNumber`='".$ttinfo->{'transittracknum'}."',`Detail`='".$mysqli->real_escape_string($ttinfo->{'cargodetail'})."',`Title`='".$mysqli->real_escape_string($ttinfo->{'cargo'}."*".$ttinfo->{'num'})."',`StatusDetail`='".$mysqli->real_escape_string($ttinfo->{'statustext'})."';";
			//echo $query;
			$mysqli->query($query);		
			
			$altstr = "明细:\r\n".str_replace("</br>","\r\n",$ttinfo->{'cargodetail'})."\r\n状态:\r\n".str_replace("</br>","\r\n",$ttinfo->{'statustext'})."备注:\r\n".str_replace("</br>","\r\n",$ttinfo->{'memo'});
			echo "<tr><td>".$ttinfo->{'time'}."</td><td><a href=\"http://www.tiantian8.com/user3.php?act=detail_zigou&zigou_id=".$ttinfo->{'id'}."\">".$ttinfo->{'id'}."</a></td><td>".$ttinfo->{'status'}."</td><td>".$ttinfo->{'vendor'}."</td><td>".$ttinfo->{'tracknum'}."</td><td>".$ttinfo->{'inweight'}."</td><td>".$ttinfo->{'outweight'}."</td><td>".$ttinfo->{'transitweight'}."</td><td>".$ttinfo->{'shippingfee'}."</td><td>".$ttinfo->{'transitvendor'}."</td><td>".$ttinfo->{'transittracknum'}."</td><td>".$ttinfo->{'cargo'}."*".$ttinfo->{'num'}."</td><td>".$ttinfo->{'cargodetail'}."</td><td>".$ttinfo->{'statustext'}."<a href=\"#\" title=\"".$altstr."\"><img src=\"images/edit_small.png\"></a></td></tr>\r\n";
		}
	}
	$mysqli->close();
	exit;

	function get_ttdetail($ttinfo)
	{
		require("conn.php");

		$url = "http://www.tiantian8.com/user3.php?act=detail_zigou&zigou_id=".$ttinfo->{'id'};
		$ch = curl_init();
		$timeout = 5; 
		curl_setopt($ch, CURLOPT_URL, $url); 
		curl_setopt($ch, CURLOPT_COOKIE, $tiantian_cookie);
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

		$html = str_get_html($strResponse);
		$ttinfo->{'cargodetail'} = trim($html->find('./textarea[@id="goods_desc2"]',0)->innertext);

		$table = $html->find('./form/table/tbody/tr[2]/td/table/tbody/tr');
		//./form/table/tbody/tr[4]/td/table/tbody/tr
		foreach ($table as $line)
		{
			$linedom = str_get_html($line->innertext);
			$td1 = trim($linedom->find('td[1]',0)->plaintext);
			if ($td1 == "物流公司：")
			{
				$str = trim($linedom->find('td[2] input',0)->value);
				if ($str == "天天自动入库货物")
				{
					$ttinfo->{'vendor'} = "N/A";
				}
				elseif ($str != "")
				{
					$ttinfo->{'vendor'} = $str;
				}
				$str = trim($linedom->find('td[2]',0)->innertext);
				if (preg_match("/input/i", $str) == 0)
				{
					$ttinfo->{'transitvendor'} = $str;
				}
			}
			elseif ($td1 == "快递单号：")
			{
				$str = trim($linedom->find('td[2] input',0)->value);
				if ($str != "")
				{
					$ttinfo->{'tracknum'} = $str;
				}
				$str = trim($linedom->find('td[2]',0)->innertext);
				if (preg_match("/input/i", $str) == 0)
				{
					$ttinfo->{'transittracknum'} = $str;
				}
			}
			elseif ($td1 == "备注信息")
			{
				$ttinfo->{'memo'} = trim($linedom->find('td[2]',0)->innertext);
			}

		}
		$table = $html->find('./form/table/tbody/tr[4]/td/table/tbody/tr');
		foreach ($table as $line)
		{
			$linedom = str_get_html($line->innertext);
			$td1 = trim($linedom->find('td[1]',0)->plaintext);

			if ($td1 == "货物状态：")
			{
				$ttinfo->{'statustext'} = trim($linedom->find('td[2]',0)->innertext);
			}
		}
		$ttinfo->{'inweight'} = preg_replace("/\(.+\)/u", "", trim($html->find('./form/table/tbody/tr[6]/td/table[1]/tbody/tr[3]/td[1]', 0)->plaintext));
		if ($ttinfo->{'inweight'} == "仓储费用：")
		{
			$ttinfo->{'inweight'} = "";
		}
		$ttinfo->{'transitweight'} = preg_replace("/\(.+\)/u", "", trim($html->find('./form/table/tbody/tr[6]/td/table[1]/tbody/tr[3]/td[4]', 0)->plaintext));
		if (preg_match_all("/(\d+\.\d+)lbs/u", $ttinfo->{'transitweight'}, $matches))
		{
			$ttinfo->{'inweight'} = array_pop(array_pop($matches));
			$ttinfo->{'transitweight'} = "";
		}
		
		$ttinfo->{'outweight'} = preg_replace("/\(.+\)/u", "", trim($html->find('./form/table/tbody/tr[6]/td/table[1]/tbody/tr[3]/td[5]', 0)->plaintext));

		$ttinfo->{'shippingfee'} = preg_replace("/元/u", "", trim($html->find('./form/table/tbody/tr[6]/td/table[2]/tbody/tr[4]/td[2]', 0)->plaintext));
		if ($ttinfo->{'outweight'} == "")
		{
			$ttinfo->{'shippingfee'} = "";
		}
		
		$ttinfo->{'transitvendor'} = trim($html->find('./form/table/tbody/tr[8]/td/table/tbody/tr[1]/td[2]', 0)->plaintext);
		$ttinfo->{'transittracknum'} = trim($html->find('./form/table/tbody/tr[8]/td/table/tbody/tr[2]/td[4]', 0)->plaintext);

	}
?>
</table>
</body>
</html>