<?php
	require_once("simple_html_dom.php");
	date_default_timezone_set("Asia/Shanghai");
	function array_rpush($tarArray, $value)
	{
		$retArray = array();
		array_push($retArray, $value);
		foreach($tarArray as $row)
		{
			array_push($retArray, $row);
		}
		$tarArray = $retArray;
		return $tarArray;
	}
	$uspsid = strtoupper($_GET["uspsid"]);
	$return_type = strtolower($_GET["r"]);
	if (isset($_GET["l"]))
	{
		$locale = $_GET["l"];
	}
	else
	{
		$locale = "en_US";
	}
	$arrTrans = array();

	//$url = "http://wwwapps.ups.com/etracking/tracking.cgi?tracknum=".$upsid;
	if ($locale == "zh_CN")
	{
		$url = "https://zh-tools.usps.com/go/TrackConfirmAction?qtc_tLabels1=".$uspsid;
	}
	else
	{
		$url = "https://tools.usps.com/go/TrackConfirmAction?qtc_tLabels1=".$uspsid;
	}
	$ch = curl_init();
	$timeout = 5; 
	curl_setopt($ch, CURLOPT_URL, $url); 
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
	$result = curl_exec($ch); 
	curl_close($ch);

	$html = str_get_html($result);

	$content = str_get_html($html->find('//*[@id="tc-hits"]', 0)->innertext);
	
	$deliverstatus = str_get_html($html->find('[@id="results-multi"]/div[1]/div/div[1]/div[2]/div[1]/h2', 0)->innertext);
	if ($deliverstatus == "delivered" || $deliverstatus == "已投递")
	{
		$delivered = true;
	}
	else
	{
		$delivered = false;
	}
	if ($content <> false)
	{
		$i = 0;
		foreach($content->find('tr') as $row)
		{
			if ($row->class != "status-summary-panel open-panel" && $i++ > 0)
			{

				$line = str_get_html($row->innertext)->find('td');
				if ($locale == "zh_CN")
				{
					$arrDateTime = explode(" ",trim($line[0]->plaintext));
					if ($arrDateTime[0] == "上午")
					{
						$apm = "am";
						$strDateTime = $arrDateTime[2]."-".$arrDateTime[4]."-".$arrDateTime[6]." ".$arrDateTime[1].$apm;

					}
					elseif ($arrDateTime[0] == "下午")
					{
						$apm = "pm";
						$strDateTime = $arrDateTime[2]."-".$arrDateTime[4]."-".$arrDateTime[6]." ".$arrDateTime[1].$apm;
					}
					else
					{
						$strDateTime = $arrDateTime[0]."-".$arrDateTime[2]."-".$arrDateTime[4];
					}
				}
				else
				{
					$strDateTime = trim($line[0]->plaintext);
				}
				$desc = trim($line[1]->plaintext);
				$loc = trim($line[2]->plaintext);
			
				if (!preg_match("/Your item was delivered/", $strDateTime))
				{
					$dateTime = new DateTime($strDateTime, new DateTimeZone('America/New_York'));
					$dateTime->setTimezone(new DateTimeZone('Asia/Shanghai'));

					$arrline = array('time' => $dateTime, 'loc' => $loc, 'desc' => $desc);
					$arrTrans = array_rpush($arrTrans, $arrline);
				}
			}
		}
	}
	if ($return_type == "time")
	{
		if ($delivered)
		{
			$ret = array ('ShippingTime'=>$arrTrans[0]['time'], 'DeliveryTime'=>$arrTrans[count($arrTrans)-1]['time']);
		}
		else
		{
			$ret = array ('ShippingTime'=>$arrTrans[0]['time'], 'DeliveryTime'=>null);
		}
	}
	else
	{
  		$ret = $arrTrans;
  	}
  	echo json_encode($ret);
?>