<?php
$legoid = $_GET["legoid"];
if (isset($legoid))
{
	$url = 'http://cache.lego.com/e/dynamic/is/image/LEGO/'.$legoid.'_is?req=imageset';
	$ch = curl_init(); 
	$timeout = 5; 
	curl_setopt($ch, CURLOPT_URL, $url); 
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 
	curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout); 
	$curlResponse = curl_exec($ch); 	
    $curlErrno = curl_errno($ch);
    if ($curlErrno) {
        $curlError = curl_error($ch);
        throw new Exception($curlError);
    }
    curl_close($ch);
    
	$result = explode(";", $curlResponse);
	$ret = array();
	foreach ($result as $line)
	{
		if (strpos($line, ",") > 0)
		{
			$start = strpos($line, ",") + 1;
		}
		else
		{
			$start = 0;
		}
		$line = substr($line, $start);
		$line = trim(substr($line, 5));
		if (!in_array($line, $ret))
		{
			array_push($ret, $line);
		}
	}

	foreach ($ret as $line)
	{
		echo $line."\n";
	}
}
?>