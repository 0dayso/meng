<?php
header("Content-type: text/html; charset=utf-8");

$LegoID = $_GET["id"];

if(isset($LegoID))
{
	require("conn.php");

	date_default_timezone_set('Asia/Shanghai');
	$mysqli = new mysqli($mysql_server_name, $mysql_username, $mysql_password, $mysql_database);

	if (mysqli_connect_errno()) {
		printf("Database Connect failed: %s\n", mysqli_connect_error());
		exit();
	}

	$mysqli->query("SET NAMES UTF8;");
	$mysqli->query("SET time_zone = '+08:00';");

	$query = "SELECT LegoID,ItemID FROM TB_Item WHERE LegoID=".$mysqli->real_escape_string($LegoID)." LIMIT 1;";
	$result = $mysqli->query($query);

	$TaobaoItems = array();
	if ($row = $result->fetch_array(MYSQLI_ASSOC))
	{
		$TaobaoID = $row["ItemID"];
		$LegoID = $row["LegoID"];
		
		$file = "setimg/tb_main/".$LegoID."_800.jpg";

		if (file_exists($file))
		{
			include "appconf.php";
			include "TopSdk.php";

			//实例化TopClient类
			$c = new TopClient;
			$c->appkey = $client_id;
			$c->secretKey = $client_secret;
			$sessionKey = $access_token;
			//echo $client_id."</br>";
			//echo $client_secret."</br>";
			//echo $sessionKey."</br>";

			$req = new ItemImgUploadRequest;
			$req->setNumIid($TaobaoID);
			//附件上传的机制参见PHP CURL文档，在文件路径前加@符号即可
			$req->setImage("@".$file);
			$req->setIsMajor("true");
			$resp = $c->execute($req, $sessionKey);

			$ret = new stdClass();
			$ret->{'id'} = $LegoID;

			if (isset($resp->item_img->url))
			{
				$ret->{'status'} = "OK";
				$ret->{'imgurl'} = (string)$resp->item_img->url;
			}
			else
			{
				$ret->{'status'} = "ERROR";
				$ret->{'error'} = "error message";
			}
		}
		
	}
	$mysqli->close();
}
echo json_encode($ret);

?>
