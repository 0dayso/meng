<?php 
require("conn.php");

require("appconf.php");
echo date('Y-m-d H:i:s')." Refreshing token.</br>\r\n";
echo "Original  Access_Token: ".$access_token."</br>\r\n";
echo "Original Refresh_Token: ".$refresh_token."</br>\r\n";

$postfields= array( 'grant_type'    => 'refresh_token',
				    'client_id'     => $client_id,
				    'client_secret' => $client_secret,
				    'refresh_token' => $refresh_token
);

$url = 'https://oauth.taobao.com/token';

$token = json_decode(curl($url,$postfields));
$new_access_token = $token->access_token;
$new_refresh_token = $token->refresh_token;
echo date('Y-m-d H:i:s')." Token refreshed.</br>\r\n";
echo "New       Access_Token: ".$new_access_token."</br>\r\n";
echo "New      Refresh_Token: ".$new_refresh_token."</br>\r\n";

$conn=mysql_connect($mysql_server_name, $mysql_username, $mysql_password) or die ("数据库错误：".mysql_error());
mysql_query("SET NAMES UTF8;", $conn);
mysql_query("SET time_zone = '+08:00';", $conn);

$strsql="UPDATE APP_UserConf SET `AccessToken` = '".$new_access_token."', `RefreshToken` = '".$new_refresh_token."' WHERE UserID = ".$taobao_userid.";";
$r=mysql_db_query($mysql_database, $strsql, $conn);

mysql_close($conn);

echo date('Y-m-d H:i:s')." Token updated into database.</br>\r\n";

 //POST请求函数
 function curl($url, $postFields = null)
	{
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_FAILONERROR, false);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

		if (is_array($postFields) && 0 < count($postFields))
		{
			$postBodyString = "";
			foreach ($postFields as $k => $v)
			{
				$postBodyString .= "$k=" . urlencode($v) . "&"; 
			}
			unset($k, $v);
			curl_setopt ($ch, CURLOPT_SSL_VERIFYPEER, 0);  
 			curl_setopt ($ch, CURLOPT_SSL_VERIFYHOST, 0); 
			curl_setopt($ch, CURLOPT_POST, true);
			curl_setopt($ch, CURLOPT_POSTFIELDS, substr($postBodyString,0,-1));
		}
		$reponse = curl_exec($ch);
		if (curl_errno($ch)){
			throw new Exception(curl_error($ch),0);
		}
		else{
			$httpStatusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
			if (200 !== $httpStatusCode){
				throw new Exception($reponse,$httpStatusCode);
			}
		}
		curl_close($ch);
		return $reponse;
	}
 
?>
