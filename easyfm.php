<?php
header("Content-type: text/html; charset=utf-8"); 

require_once("simple_html_dom.php");
mb_internal_encoding('utf-8');
$url = "http://english.cri.cn/4926/more/10679/more10679.htm";
$ch = curl_init(); 
$timeout = 5; 
curl_setopt($ch, CURLOPT_URL, $url); 
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 
curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout); 
$contents = curl_exec($ch); 
curl_close($ch);
$html = str_get_html($contents);
echo "<?xml version=\"1.0\" encoding=\"UTF-8\"?>";
date_default_timezone_set('Asia/Shanghai');

$objDateTime = new DateTime('NOW');
$objDateTime->setTimeZone(new DateTimeZone("Asia/Shanghai"));
?>
	<rss xmlns:itunes="http://www.itunes.com/dtds/podcast-1.0.dtd" version="2.0">
	<channel>
		<title>Easy Morning - 飞鱼秀</title>
		<link>http://english.cri.cn/easyfm/easymorning.html</link>
		<description>“飞鱼秀”诞生于2004年一个偶然的机会，就是这个偶然在此后的8年里不断地影响着北京的广播环境。在这7000多个小时的直播中，主持人小飞和喻舟与大家一同见证了彼此的成长。很难描述这档节目属于什么类型。它的资讯是解析独到的，音乐是水准很高的，幽默是歪打正着的，话题是你意想不到的，笑声是能让你发毛的。。。欢迎继续支持永远全新的“飞鱼秀”，我们从不改版但一直在变。</description>
		<pubDate><?php echo $objDateTime->format(DateTime::RFC2822); ?></pubDate>
		<language>zh-cn</language>
		<ttl>10</ttl>
		<itunes:image href="http://english.cri.cn/mmsource/images/2012/03/16/yz.jpg" />
<?
$itemlist = $html->find('div[class="more"] table tbody tr');
foreach ($itemlist as $item)
{
	$itemhtml = str_get_html($item);
	
	$strTitle = iconv('GBK', 'UTF-8', trim($itemhtml->find('td a',0)->innertext));
	$strDate = explode(" ", $strTitle);
	$date = explode("-", substr($strDate[0], -10));
	$year = substr($date[0], -2);
	$url = "http://mod.cri.cn/eng/ez/morning/".$date[0]."/ezm".$year.$date[1].$date[2].".mp3";
	

 	$objDateTime = new DateTime($date[0]."-".$date[1]."-".$date[2]."T00:00:00Z");
 	$objDateTime->setTimeZone(new DateTimeZone("Asia/Shanghai"));
?>

		<item>
			<title><?php echo $strTitle; ?></title>
			<description>飞鱼秀</description>
			<pubDate><?php echo $objDateTime->format(DateTime::RFC2822); ?></pubDate>
			<guid><?php echo $url; ?></guid>
			<enclosure url="<?php echo $url; ?>" length="7200" type="audio/mpeg" />
		</item>
<?php	
}
?>
	</channel>
	</rss>