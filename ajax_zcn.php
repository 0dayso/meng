<?php

//
// 取amazon.cn相关信息
//
//

require_once("simple_html_dom.php");
$legoid = $_GET["legoid"];

if (isset($legoid))
{
	$legoinfo = new stdClass();
	$legoinfo->{'LegoID'} = $legoid;
	$url = 'http://www.amazon.cn/s/?_encoding=UTF8&camp=536&creative=3132&linkCode=ur2&tag=brickcn-23&url=search-alias%3Daps&field-keywords=lego%20'.$legoid;
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
		$legoinfo->{'CNASIN'} = $html->find('//*[@id="result_0"]', 0)->name;
		$legoinfo->{'CTitle'} = trim(preg_replace("/.+(组|系列)/u", "", preg_replace("/LEGO 乐高 /u", "", html_entity_decode(str_replace("$legoid", "", str_replace("$type", "", $title)), ENT_NOQUOTES, 'UTF-8'))));
		$price = trim($html->find('//*[@id="result_0"]/ul/li[1]/a/del', 0)->plaintext);		
		preg_match_all("/(\d+\.\d{2})/u", html_entity_decode($price, ENT_NOQUOTES, 'UTF-8'), $matches);
		$legoinfo->{'CNPrice'} = array_pop(array_pop($matches));
	}
}
if ($legoinfo->{'CNASIN'} != "B00AHTXFN8")
{
	echo json_encode($legoinfo);
}
else
{
	echo json_encode(null);
}
?>