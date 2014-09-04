<?php

//
// 取amazon.com相关信息
//
//

require_once("simple_html_dom.php");
$legoid = $_GET["legoid"];

if (isset($legoid))
{
	$legoinfo = new stdClass();
	$legoinfo->{'LegoID'} = $legoid;
	$url = 'http://www.amazon.com/s/?_encoding=UTF8&camp=1789&creative=390957&linkCode=ur2&tag=brickus-20&url=search-alias%3Daps&field-keywords=lego%20'.$legoid;
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
    
	$html = str_get_html($curlResponse);
	if (isset($html))
	{
		$title = trim($html->find('//*[@id="result_0"]/h3/a/span', 0)->plaintext);
		$theme = preg_replace("/by\s+/u", "", $html->find('//*[@id="result_0"]/h3/span[class="med reg"]', 0)->plaintext);
		$legoinfo->{'ETitle'} = trim(str_replace("$legoid", "", str_replace("$theme", "", $title)));
		if ($legoinfo->{'ETitle'} != "")
		{
			$legoinfo->{'USASIN'} = $html->find('//*[@id="result_0"]', 0)->name;
		}
		$price = trim($html->find('//*[@id="result_0"]/ul[1]/li[1]/a/del', 0)->plaintext);		
		preg_match_all("/(\d+\.\d{2})/u", html_entity_decode($price, ENT_NOQUOTES, 'UTF-8'), $matches);
		$legoinfo->{'USPrice'} = array_pop(array_pop($matches));
	}
}

echo json_encode($legoinfo);
?>