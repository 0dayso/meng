<?php
$id = $_GET["id"];
$site = $_GET["site"];

if (isset($id) && isset($site))
{
	if (strtolower($site) == "cn")
	{
		$url = "http://www.amazon.cn/dp/".$id."/ref=as_li_ss_tl?ie=UTF8&camp=1789&creative=390957&creativeASIN=".$id."&linkCode=as2&tag=brickcn-20";
	}
	else
	{
		$url = "http://www.amazon.com/dp/".$id."/ref=as_li_ss_tl?ie=UTF8&camp=1789&creative=390957&creativeASIN=".$id."&linkCode=as2&tag=brickus-20";
	}
}
else
{
	$url = "http://www.weibo.com/legosales";
}

header('Location: '.$url);
?>