<?php
function object2array($object) { return @json_decode(@json_encode($object),1); } 



if ($_GET["act"] == "pullart")
{
	$id = $_GET["id"];
	$url = "http://www.funko.com/admin/xml_item_runtime.php?artist_id=".$id;
	$ch = curl_init();
	$timeout = 5; 
	curl_setopt($ch, CURLOPT_URL, $url); 
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
	$result = curl_exec($ch);
	curl_close($ch);

	$result = str_replace("&", "&amp;", $result); 
	$xml = simplexml_load_string($result); 

	$xml_array=object2array($xml); 

}

else
{
	/*
	$url = "http://www.funko.com/admin/xml_item_runtime.php?category_id=10";
	$ch = curl_init();
	$timeout = 5; 
	curl_setopt($ch, CURLOPT_URL, $url); 
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
	$result = curl_exec($ch);
	curl_close($ch);
	*/

	$filename = "pop_vinyl.xml";
	$handle = fopen($filename, "r");
	$result = fread($handle, filesize($filename));
	fclose($handle);

	$result = str_replace("&", "&amp;", $result); 
	
	$xml = simplexml_load_string($result); 
	
	//$xml = simplexml_load_file("pop_vinyl.xml");
	
	$xml_array=object2array($xml); 

	foreach($xml_array["item"] as $item)
	{
		$inventory = $item["@attributes"]["inventory"];
		$title = $item["@attributes"]["title"];

		$img_med =  $item["@attributes"]["img_med"];
		echo "$inventory - $title<br/>\r\n";
		echo "<img src=\"http://www.funko.com/".$img_med."\" /><br/>\r\n";
	}

}
?>