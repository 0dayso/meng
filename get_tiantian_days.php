<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <link rel="stylesheet" href="table.css">
	<script type="text/javascript" src="jquery.js"></script>
    <script type="text/javascript" src="sorttable.js"></script>
	<script language="JavaScript">
	function claim(id)
	{
		var f = $("#"+id).find("td").eq(4).html();
		var sd = $("#"+id).find("td").eq(7).html();
		var dd = $("#"+id).find("td").eq(9).html();
		var d = $("#"+id).find("td").eq(10).html();
		var bd = $("#"+id).find("td").eq(11).find("font").html();

		$.get("add_tiantian_ticket.php", { ttid: id, sd: sd, dd: dd, d: d, bd: bd, f: f} , function(data) {alert(data)} );

	}
	</script>
    <title>Tiantian转运时效统计</title>
</head>
<body>
<table>
<tr><th>时间</th><th>TT单号</th><th>出库重量</th><th>转运重量</th><th>转运费</th><th>转运快递</th><th>转运单号</th><th>出库日期</th><th>转运日期</th><th>EMS发货日期</th><th>实际天数</th><th>工作日</th><th>索赔</th></tr>
<?php
	require_once("simple_html_dom.php");
	date_default_timezone_set("Asia/Shanghai");
	require("conn.php");

	date_default_timezone_set('Asia/Shanghai');
	$mysqli = new mysqli($mysql_server_name, $mysql_username, $mysql_password, $mysql_database);

	if (mysqli_connect_errno()) {
		printf("Database Connect failed: %s\n", mysqli_connect_error());
		exit();
	}

	$mysqli->query("SET NAMES UTF8;");
	$mysqli->query("SET time_zone = '+08:00';");

	for ($i=1; $i<=4; $i++)
	{
		$url = "http://www.tiantian8.com/user3.php?act=zigou_list&page=".$i;
		$ch = curl_init();
		$timeout = 5; 
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_COOKIE, $tiantian_cookie);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, "ddzt=10");
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
			
			preg_match('/(\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}) 已出库/', $ttinfo->{'statustext'}, $matches);
			$outtime = $matches[1];

			preg_match('/(\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}) 转运中/', $ttinfo->{'statustext'}, $matches);
			$transittime = $matches[1];

			$url = "http://localhost/query_ems.php?r=time&emsid=".$ttinfo->{'transittracknum'};
			$ch = curl_init();
			$timeout = 5; 
			curl_setopt($ch, CURLOPT_URL, $url);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
			$obj = json_decode(curl_exec($ch));
			$emstime = $obj->{'ShippingTime'}->{'date'};
			curl_close($ch);

			$endtime = $transittime;
			if ($emstime <> "" ) //&& $emstime < $transittime)
			{
				$endtime = $emstime;
			}

			$days = round((strtotime(strval($endtime))-strtotime(strval($outtime)))/3600/24);
			$workdays = count_business_days($outtime, $endtime);
			if ($workdays > 15)
			{
				$workdays = "<font color=red>".$workdays."</font>";
				$claim = "<a href=\"javascript:claim('".$ttinfo->{'id'}."');\">索赔</a>";
			}
			else
			{
				$claim = "";
			}
			//$altstr = "明细:\r\n".str_replace("</br>","\r\n",$ttinfo->{'cargodetail'})."\r\n状态:\r\n".str_replace("</br>","\r\n",$ttinfo->{'statustext'})."备注:\r\n".str_replace("</br>","\r\n",$ttinfo->{'memo'});
			echo "<tr id=\"".$ttinfo->{'id'}."\"><td>".$ttinfo->{'time'}."</td><td><a href=\"http://tiantian8.com/user3.php?act=detail_zigou&zigou_id=".$ttinfo->{'id'}."\">".$ttinfo->{'id'}."</a></td><td>".$ttinfo->{'outweight'}."</td><td>".$ttinfo->{'transitweight'}."</td><td>".$ttinfo->{'shippingfee'}."</td><td>".$ttinfo->{'transitvendor'}."</td><td>".$ttinfo->{'transittracknum'}."</td><td>".$outtime."</td><td>".$transittime."</td><td>".$emstime."</td><td>".$days."</td><td>".$workdays."</td><td>".$claim."</td></tr>\r\n";
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
		/*
		foreach ($table as $line)
		{
			$linedom = str_get_html($line->innertext);
			$td1 = trim($linedom->find('td[1]',0)->plaintext);

			if ($td1 == "合箱/分箱自购ID:")
			{
				$ttinfo->{'relatedid'} = trim($linedom->find('td[2]',0)->innertext);
			}
			elseif ($td1 == "备注信息:")
			{
				$ttinfo->{'memo'} = trim($linedom->find('td[2] textarea',0)->innertext);
			}

			elseif ($td1 == "入库实际重量(磅):")
			{
				$ttinfo->{'inweight'} = trim($linedom->find('td[2]',0)->innertext);
			}
			elseif ($td1 == "出库收费重量(磅):")
			{
				$ttinfo->{'outweight'} = trim($linedom->find('td[2]',0)->innertext);
			}
			elseif ($td1 == "免税州重量:")
			{
				$ttinfo->{'transitweight'} = trim($linedom->find('td[2]',0)->innertext);
			}
			elseif ($td1 == "运费总计(RMB):")
			{
				$ttinfo->{'shippingfee'} = trim($linedom->find('td[2] font[1]',0)->innertext);
				if ($ttinfo->{'shippingfee'} == "=运费0+仓储费0")
				{
					$ttinfo->{'shippingfee'} = 0;
				}
			}
			elseif ($td1 == "打折后运费(RMB):")
			{
				$ttinfo->{'shippingfee'} = trim($linedom->find('td[2]',0)->innertext);
			}
		}
		*/

	}
	

function count_business_days($a, $b)
{
/*
***      1月1日                        星期三                        元旦
*          1月20日                      星期一                        马丁路德金诞辰日
**        1月31-2月4日           星期五至星期二               春节
*          2月17日                      星期一                        华盛顿诞辰日
**        4月5-7日                     星期六至星期一            清明节
**        5月1日                        星期四                        国际劳动节
*          5月26日                      星期一                        阵亡烈士纪念日
**        6月2日                        星期一                        端午节
*          7月4日                        星期五                        独立日即美国国庆节
*          9月1日                        星期一                        劳工节
**        9月8日                        星期一                        中秋节
**        10月1-3日                   星期三至星期五            中国国庆节
*          10月13日                    星期一                        哥伦布日
*          11月11日                    星期二                        退伍军人节
*          11月27日                    星期四                        感恩节
*          12月25日                    星期四                        圣诞节
*/
	$holidays = array ("2013-10-14", "2013-11-11", "2013-11-28", "2013-12-25", "2014-01-01", "2014-01-20", "2014-01-22", "2014-01-23", "2014-01-24", "2014-01-25", "2014-01-26", "2014-01-27", "2014-01-28", "2014-01-29", "2014-01-30", "2014-01-31", "2014-02-01", "2014-02-02", "2014-02-03", "2014-02-04", "2014-02-05", "2014-02-06", "2014-02-07", "2014-02-08", "2014-02-09", "2014-02-10", "2014-02-11", "2014-02-12", "2014-02-13", "2014-02-14", "2014-02-15", "2014-02-16", "2014-02-17", "2014-04-05", "2014-04-06", "2014-04-07", "2014-05-01", "2014-05-26", "2014-06-02", "2014-07-04", "2014-09-01", "2014-09-08", "2014-10-01", "2014-10-02", "2014-10-03", "2014-10-13", "2014-11-11", "2014-11-27", "2014-12-25");
	// First, sort these. We need to know which one comes first
	if ($a < $b)
	{
		$first = $a;
		$second = $b;
	}
	else
	{
		$first = $b;
		$second = $a;
	}
	
	$skip = 0;
	foreach ($holidays as $holiday)
	{
		$wday = getdate(is_string($holiday) ? strtotime(strval($holiday)) : $holiday)['wday'];
		if ($first < $holiday && $holiday < $second && $wday <> 0 && $wday <> 6)
		{
			$skip++;
		}
	}

	// Break these timestamps up into their constituent parts:
	$f = getdate(is_string($first) ? strtotime(strval($first)) : $first);
	$s = getdate(is_string($second) ? strtotime(strval($second)) : $second);
	// Calculate the number of business days in the first week left.
	// Do this by subtracting the number of the day of the week from Friday
	$f_days = 5 - $f['wday'];
	// If it was Saturday or Sunday you will get a -1 or 5 but we want 0
	if (($f_days == 5) || ($f_days < 0))
	{
		$f_days = 0;
	}

	// Do the same for the second week except count to the beginning of
	// the week. However, make sure that Saturday only counts as 5
	$s_days = ($s['wday'] > 5) ? 5 : $s['wday'];

	// Calculate the timestamp of midday, the Sunday after the first date:
	$f_sunday = mktime(12, 0, 0, $f['mon'], $f['mday'] + ((7 - $f['wday']) % 7), $f['year']);

	// And the timestamp of midday, the Sunday before the second date:
	$s_sunday = mktime(12, 0, 0, $s['mon'],	$s['mday'] - $s['wday'], $s['year']);

	// Calculate the full weeks between these two dates by subtracting
	// them, then dividing by the seconds in a week. You need to round
	// this afterwards to always ensure an even number. Otherwise
	// daylight savings time can offset the calculation.
	$weeks = round(($s_sunday - $f_sunday) / (3600*24*7));

	// Return the number of days by multiplying weeks by 5 and adding
	// the extra days:
	return ($weeks * 5) + $f_days + $s_days - $skip;
}
?>
</body>
</html>