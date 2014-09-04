<?php
//*[@id=""]/div[1]/div[1]
//*[@id="main-stage"]/div[1]/div[1]/a/img
require_once("simple_html_dom.php");

$urls = array("http://shop.lego.com/en-US/", "http://shop.lego.com/en-GB/");

foreach ($urls as $url)
{
	$ch = curl_init(); 
	$timeout = 5; 
	curl_setopt($ch, CURLOPT_URL, $url); 
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 
	curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout); 
	$contents = curl_exec($ch); 
	curl_close($ch);

	$html = str_get_html($contents);

	$piclist = $html->find('div[id="main-stage"] div[1] div');
	foreach ($piclist as $pic)
	{
		$picdoc = str_get_html($pic);
		echo $picdoc->innertext;
		$url = $picdoc->find('div a img',0)->src;
		$title = $picdoc->find('div a img',0)->title;
	
		$filename = "setimg/site_poster/".basename($url);
		
		$savefile = 1;
		if (file_exists($filename))
  		{
			ob_start(); 
			readfile($url); 
			$img = ob_get_contents(); 
			ob_end_clean();
			$httpmd5 = md5($img);
			$filemd5 = md5_file($filename);
			if ($httpmd5 == $filemd5)
			{
				$savefile = 0;
			}
		}
		if ($savefile)
		{
			$fp2 = fopen($filename , "w"); 
			fwrite($fp2, $img); 
			fclose($fp2); 
		}
		//echo $title;
	}
}

?>


