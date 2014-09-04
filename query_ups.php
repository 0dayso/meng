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
	$upsid = strtoupper($_GET["upsid"]);
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
	$url = "http://wwwapps.ups.com/WebTracking/track?trackNums=".$upsid."&loc=".$locale."&track.x=追踪";
	$ch = curl_init();
	$timeout = 5; 
	curl_setopt($ch, CURLOPT_URL, $url); 
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
	$result = curl_exec($ch); 
	curl_close($ch);

	$html = str_get_html($result);
	$status = $html->find('a[id=tt_spStatus]', 0)->plaintext;
	$content = str_get_html($html->find('table[class=dataTable]', 0)->innertext);
	
	$deliverstatus = str_get_html($html->find('[@id="tt_spStatus"]/text', 0)->innertext);
	if ($deliverstatus == "Delivered" || $deliverstatus == "已递送")
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
			if ($i++ > 0)
			{
				$line = str_get_html($row->innertext)->find('td');
		
				$loc = str_replace("&nbsp;&nbsp;&nbsp;", " ", trim(str_replace("\t", "", $line[0]->plaintext)));
				$date = str_replace("&nbsp;&nbsp;&nbsp;", " ", trim($line[1]->plaintext));
				$time = str_replace("&nbsp;&nbsp;&nbsp;", " ", trim($line[2]->plaintext));
				$desc = str_replace("&nbsp;&nbsp;&nbsp;", " ", trim($line[3]->plaintext));
				$strDateTime = $date." ".strtolower(str_replace(".","",$time));
				$dateTime = new DateTime($strDateTime, new DateTimeZone('America/New_York'));
				$dateTime->setTimezone(new DateTimeZone('Asia/Shanghai'));

				$arrline = array('time' => $dateTime, 'loc' => $loc, 'desc' => $desc);
				$arrTrans = array_rpush($arrTrans, $arrline);
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
	
	/*
	if ($return_type == "oneline")
	{
	  $dditem = $html->find('div[class=secBody]/dl[class=outHozFixed1 clearfix]/dd');
	  foreach ($dditem as $item)
	  {
		$itemstr = $item->plaintext;
		if (preg_match("/\d\d\/\d\d\/\d\d\d\d/", $itemstr))
		{
		  $dateTime = new DateTime($itemstr, new DateTimeZone('America/New_York'));
		  $shipon = $dateTime->format("Y-m-d");
		}
		if (preg_match("/lbs/", $itemstr))
		{
		  $weight = $itemstr;
		}
		//中文版
		if (preg_match("/\d\d\d\d\/\d\d\/\d\d/", $itemstr))
		{
		  $dateTime = new DateTime($itemstr, new DateTimeZone('America/New_York'));
		  $shipon = $dateTime->format("Y-m-d");
		}
		if (preg_match("/磅/", $itemstr))
		{
		  $weight = $itemstr;
		}
	  }

	  if (count($arrTrans))
	  {
		$result = array_pop($arrTrans);
	  }
	  else
	  {
		$result = null;
	  }
	  if ($status == "On Vehicle for Delivery Today")
	  {
		$strStatus = "今日送达: ";
	  }
	  elseif (($status == "In Transit") or ($status == "In Transit: On Time"))
	  {
		$strStatus = "运输中: ";
	  }
	  else
	  {
		$strStatus = str_replace("： ",",",$status).": ";
	  }

	  if (isset($result))
	  {
		$resultstr = $strStatus.$weight." 发货于".$shipon."。<br>".$result['time']->format("Y-m-d H:i")."于".$result['loc']." ".$result['desc'];
	  }
	  else
	  {
		$resultstr = $strStatus.$weight." 发货于".$shipon."。";
	  }
	  echo $resultstr;
	}
	else
	{
	  echo var_dump($arrTrans);
	  //echo json_encode($arrTrans);
	}
	*/
?>