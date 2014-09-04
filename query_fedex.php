<?php
  require_once("simple_html_dom.php");
  date_default_timezone_set("Asia/Shanghai");
  header('Content-Type: text/html; charset=utf-8');
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
  $fedexid = strtoupper($_GET["fedexid"]);
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
  $url = "http://www.fedex.com/trackingCal/track";
  $ch = curl_init();
  $timeout = 5; 
  curl_setopt($ch, CURLOPT_URL, $url);
  curl_setopt($ch, CURLOPT_POST, 1);
  curl_setopt($ch, CURLOPT_POSTFIELDS, "data=%7B%22TrackPackagesRequest%22%3A%7B%22appType%22%3A%22wtrk%22%2C%22uniqueKey%22%3A%22%22%2C%22processingParameters%22%3A%7B%22anonymousTransaction%22%3Atrue%2C%22clientId%22%3A%22WTRK%22%2C%22returnDetailedErrors%22%3Atrue%2C%22returnLocalizedDateTime%22%3Afalse%7D%2C%22trackingInfoList%22%3A%5B%7B%22trackNumberInfo%22%3A%7B%22trackingNumber%22%3A%22".$fedexid."%22%2C%22trackingQualifier%22%3A%22%22%2C%22trackingCarrier%22%3A%22%22%7D%7D%5D%7D%7D&action=trackpackages&locale=".$locale."&format=json&version=99");

  curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
  curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
  $result = curl_exec($ch); 
  curl_close($ch);

  $retobj = json_decode($result);
  $delivered = false;
  foreach ($retobj->TrackPackagesResponse->packageList[0]->scanEventList as $event)
  {
	$loc = $event->scanLocation;
	$status = $event->status;
	if ($event->isDelivered)
	{
		$delivered = true;
	}
  	$arrline = array('time' => $datetime, 'loc' => $loc, 'desc' => $status);
	$arrTrans = array_rpush($arrTrans, $arrline);
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