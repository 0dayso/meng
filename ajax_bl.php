<?php

//
// 取Bricklink相关信息
//

require_once("simple_html_dom.php");
$legoid = $_GET["legoid"];

if (isset($legoid))
{
	$legoinfo = new stdClass();
	$legoinfo->{'LegoID'} = $legoid;
	$url = 'http://www.bricklink.com/catalogItem.asp?S='.$legoid.'-1';
	$ch = curl_init(); 
	$timeout = 5; 
	curl_setopt($ch, CURLOPT_URL, $url); 
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 
	curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout); 
	$curlResponse = curl_exec($ch); 	
    $curlErrno = curl_errno($ch);
    if ($curlErrno) {
        $curlError = curl_error($ch);
        throw new Exception($curlError);
    }
    curl_close($ch);
    
	$html = str_get_html($curlResponse);
	if (isset($html))
	{
		$legoinfo->{'ETitle'} = $html->find('/html/body/center/table[3]/tbody/tr/td/table/tbody/tr/td/table/tbody/tr/td/table[2]/tbody/tr/td/center/font/b', 0)->plaintext;
		$legoinfo->{'Weight'} = 0;
		$legoinfo->{'Length'} = 0;
		$weightstr = $html->find('table[class=fv]/tbody/tr/td[4]', 0)->plaintext;
		if (preg_match('/(\d+)/', $weightstr,  $m))
		{
			$legoinfo->{'Weight'} = (int)$m[1];
		}
		else
		{
			$legoinfo->{'Weight'} = null;
		}
		$sizestr = $html->find('table[class=fv]/tbody/tr/td[5]', 0)->plaintext;
		$sizestr = str_replace("&nbsp;", " ", $sizestr);
		if (preg_match('/([\d|\.]+)\sx\s([\d|\.]+)\sx\s([\d|\.]+)/', $sizestr,  $m))
		{
			$legoinfo->{'Length'} = (float)$m[1];
			$legoinfo->{'Width'} = (float)$m[2];
			$legoinfo->{'Height'} = (float)$m[3];
		}
		else
		{
			$legoinfo->{'Length'} = null;
			$legoinfo->{'Width'} = null;
			$legoinfo->{'Height'} = null;
		}
		if ($_GET["update"] == 1 && isset($legoinfo->{'Weight'}))
		{
			require("conn.php");
			$mysqli = new mysqli($mysql_server_name, $mysql_username, $mysql_password, $mysql_database);
			if (mysqli_connect_errno()) {
				printf("Database Connect failed: %s\n", mysqli_connect_error());
				exit();
			}
			date_default_timezone_set('Asia/Shanghai');
			$mysqli->query("SET NAMES UTF8;");
			$mysqli->query("SET time_zone = '+08:00';");
			$query = "UPDATE DB_Set SET Weight='".$mysqli->real_escape_string($legoinfo->{'Weight'})."'";
			if (isset($legoinfo->{'Length'}) && isset($legoinfo->{'Width'}) && isset($legoinfo->{'Height'}))
			{
				$query = $query.", Length='".$mysqli->real_escape_string($legoinfo->{'Length'})."', Width='".$mysqli->real_escape_string($legoinfo->{'Width'})."', Height='".$mysqli->real_escape_string($legoinfo->{'Height'})."'";
			}
			$query = $query." WHERE LegoID='".$mysqli->real_escape_string($legoid)."' LIMIT 1;";
			$result = $mysqli->query($query);
			
			$mysqli->close();

		}
	}
}

echo json_encode($legoinfo);
?>