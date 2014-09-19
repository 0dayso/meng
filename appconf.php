<?php
require("conn.php");

$taobao_userid = '12352442';
$client_id = '21532512';//自己的APPKEY
$client_secret = '165fb84848c9eca8517531d89d408eea';//自己的appsecret
$access_token = "";
$refresh_token = "";
$conn=mysql_connect($mysql_server_name, $mysql_username, $mysql_password) or die ("数据库错误：".mysql_error());
mysql_query("SET NAMES UTF8;", $conn);
mysql_query("SET time_zone = '+08:00';", $conn);

$strsql="SELECT AccessToken,RefreshToken,ExpireTime FROM APP_UserConf WHERE UserID = ".$taobao_userid.";";
$result=mysql_db_query($mysql_database, $strsql, $conn);
if ($line = mysql_fetch_array($result))
{
  $access_token = $line['AccessToken'];
  $refresh_token = $line['RefreshToken'];
  $ExpireTime = $line['ExpireTime'];
}

if (strtotime("+20 hours") >= strtotime($ExpireTime))
{
	header("Location: http://$_SERVER[HTTP_HOST]/taobao_auth.php?r=$_SERVER[REQUEST_URI]");

}
?>