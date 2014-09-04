<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
</head>
<body>
<?php
//get the q parameter from URL
$q = $_GET["q"];
if (!isset($q))
{
	$q = "lego 乐高";
}
date_default_timezone_set('Asia/Shanghai');

$rsslist = array(
	"smzdm" => "http://www.smzdm.com/feed",
	"smzdm.ht" => "http://haitao.smzdm.com/feed",
	"smzdm.fx" => "http://fx.smzdm.com/feed",
	);

foreach ($rsslist as $xml)
{
	$xmlDoc = new DOMDocument();
	$xmlDoc->load($xml);

	$x=$xmlDoc->getElementsByTagName('item');

	foreach ($x as $item)
	{
		$title = $item->getElementsByTagName('title')->item(0)->childNodes->item(0)->nodeValue;
		$link = $item->getElementsByTagName('link')->item(0)->childNodes->item(0)->nodeValue;
		$desc = $item->getElementsByTagName('description')->item(0)->childNodes->item(0)->nodeValue;
		$pubdate = $item->getElementsByTagName('pubDate')->item(0)->childNodes->item(0)->nodeValue;
		$intval = intval(round((strtotime('now') - strtotime($pubdate))/60));
		if ($intval <= 10)
		{
			$keywords = explode(" ", $q);
			$expr = implode("|", $keywords);
			if (preg_match("/$expr/u", $title) || preg_match("/$expr/u", $desc))
			{
				var_dump($title);
				$url = "https://www.appnotifications.com/account/notifications.json";

				$ch = curl_init(); 
				$timeout = 5; 
				curl_setopt($ch, CURLOPT_URL, $url); 
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 
				curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
				curl_setopt($ch, CURLOPT_POST, 1);
				curl_setopt($ch, CURLOPT_POSTFIELDS, "user_credentials=hVMLo2kJtyQeMS5ONiEJ&notification[message]=$title&notification[title]=$title&notification[subtitle]=$xml&notification[long_message_preview]=$link&notification[long_message]=$desc$link&notification[icon_url]=http://faast.io/img/icon.png&notification[sound]=50");

				$contents = curl_exec($ch); 
				curl_close($ch);
				echo $contents;
			}
		}
	}
}
?>
</body>
</html>