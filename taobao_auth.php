<?php
if (isset($_GET["code"]))
{
	include "appconf.php";
	$code = $_GET["code"];

	$ch = curl_init();

	curl_setopt($ch, CURLOPT_URL,"https://oauth.taobao.com/token");
	curl_setopt($ch, CURLOPT_POST, 1);
	curl_setopt($ch, CURLOPT_POSTFIELDS,
				"code=".$code."&grant_type=authorization_code&client_id=$client_id&client_secret=$client_secret&redirect_uri=http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]");
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);    

	$strResponse = curl_exec($ch); 	
    $curlErrno = curl_errno($ch);
    if ($curlErrno) {
        $curlError = curl_error($ch);
        throw new Exception($curlError);
    }
    curl_close($ch);

	$objResponse = json_decode($strResponse);
	
	if ($objResponse->error)
	{
		throw new Exception($objResponse->error_description);
	}

	$access_token = strtolower($objResponse->{'access_token'});
	$refresh_token = strtolower($objResponse->{'refresh_token'});
	$taobao_user_id = floatval($objResponse->{'taobao_user_id'});

	/*
	echo date('Y-m-d H:i:s')." Token updated.</br>\r\n";
	echo "Access_Token: ".$access_token."</br>\r\n";
	echo "Refresh_Token: ".$refresh_token."</br>\r\n";
	*/

	$conn=mysql_connect($mysql_server_name, $mysql_username, $mysql_password) or die ("数据库错误：".mysql_error());
	mysql_query("SET NAMES UTF8;", $conn);
	mysql_query("SET time_zone = '+08:00';", $conn);

	$strsql="UPDATE APP_UserConf SET `AccessToken` = '".$access_token."', `RefreshToken` = '".$refresh_token."', `LastRefreshTime` = NOW(), `ExpireTime` = NOW() + INTERVAL 1 DAY WHERE UserID = ".$taobao_user_id.";";
	$r=mysql_db_query($mysql_database, $strsql, $conn);

	mysql_close($conn);

	$url = $_GET["r"];
	header("Location: http://$_SERVER[HTTP_HOST]$url");
	/*
	echo "<script language=\"javascript\" type=\"text/javascript\">";
	echo "window.location.href=\"http://$_SERVER[HTTP_HOST]/$url\"";
	echo "</script>";
	*/
}
else
{
	include "appconf.php";
	header("Location: https://oauth.taobao.com/authorize?response_type=code&client_id=$client_id&redirect_uri=http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]");
}
?>