<?php
header("Content-type: text/html; charset=utf-8");

include "appconf.php";
include "TopSdk.php";

//实例化TopClient类
$c = new TopClient;
$c->appkey = $client_id;
$c->secretKey = $client_secret;
$sessionKey = $access_token;


$reqShowcase = new ShopRemainshowcaseGetRequest;
$respShowcase = $c->execute($reqShowcase, $sessionKey);

//取剩余推荐数
$op_count = intval($respShowcase->shop->remain_count);

$reqItems = new ItemsOnsaleGetRequest;
$reqItems->setFields("num_iid,title,delist_time,has_showcase,outer_id");
$reqItems->setOrderBy("delist_time:desc");
$reqItems->setPageSize(100);
$respItems = $c->execute($reqItems, $sessionKey);

//移除最近10个已经过期的推荐
foreach ($respItems->items->item as $item)
{
  if ((string)$item->has_showcase == 'true')
  {
    $reqRecommend = new ItemRecommendDeleteRequest;
    $NumIid = floatval($item->num_iid);
    $reqRecommend->setNumIid($NumIid);
    $respRecommend = $c->execute($reqRecommend, $sessionKey);
    echo date('Y-m-d H:i:s')." Remove recommended on $NumIid</br>\r\n";
    $op_count++;
  }
  if ($op_count >= 10)
  {
  	break;
  }
}

//为即将到期的商品进行推荐
$reqItems->setOrderBy("delist_time:asc");

$reqShowcase = new ShopRemainshowcaseGetRequest;
$respShowcase = $c->execute($reqShowcase, $sessionKey);
$respItems = $c->execute($reqItems, $sessionKey);
foreach ($respItems->items->item as $item)
{
  if ((string)$item->has_showcase == 'false')
  {
    $reqRecommend = new ItemRecommendAddRequest;
    $NumIid = floatval($item->num_iid);
    $reqRecommend->setNumIid($NumIid);
    $respRecommend = $c->execute($reqRecommend, $sessionKey);
    echo date('Y-m-d H:i:s')." Add recommended on $NumIid</br>\r\n";
	$op_count--;
  }
  if ($op_count == 0)
  {
  	break;
  }
}
?>