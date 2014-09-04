#!/usr/local/bin/php
<?php
function getArray($node) { 
    $array = false; 

    if ($node->hasAttributes()) { 
        foreach ($node->attributes as $attr) { 
            $array[$attr->nodeName] = $attr->nodeValue; 
        } 
    } 

    if ($node->hasChildNodes()) { 
        if ($node->childNodes->length == 1) { 
            $array[$node->firstChild->nodeName] = $node->firstChild->nodeValue; 
        } else { 
            foreach ($node->childNodes as $childNode) {
            	if ($childNode->nodeName == "br") {
            		$array["#text"] = $node->nodeValue;
            	}
                if ($childNode->nodeType != XML_TEXT_NODE) { 
                    $array[$childNode->nodeName][] = getArray($childNode); 
                } 
            } 
        } 
    } 
    return $array; 
} 

require_once("simple_html_dom.php");


$mysql_server_name="lelemeng.gotoftp4.com";
$mysql_username="lelemeng";
$mysql_password="mmx-B16";
$mysql_database=$mysql_username;

date_default_timezone_set('Asia/Shanghai');
$mysqli = new mysqli($mysql_server_name, $mysql_username, $mysql_password, $mysql_database);

if (mysqli_connect_errno()) {
	printf("Database Connect failed: %s\n", mysqli_connect_error());
	exit();
}

$mysqli->query("SET NAMES UTF8;");
$mysqli->query("SET time_zone = '+08:00';");

$query = "SELECT ThemeID,ETheme FROM DB_Theme";
$result = $mysqli->query($query);

$arrTheme = array();
while ($row = $result->fetch_array(MYSQLI_ASSOC))
{
	$arrTheme[$row['ETheme']] = $row['ThemeID'];
}

if (isset($argv[1]))
{
	$ids = explode(",",$argv[1]);
	$query = "SELECT LegoID,ThemeID,ETitle,Age,Minifigs,Year,Pieces,USPrice,Weight,Length,Width,Height,UPC,EAN,ItemSN,BK_Subset FROM DB_Set WHERE LegoID IN(".$argv[1].");";
	$result = $mysqli->query($query);
	if ($result->num_rows < count($ids) )
	{
		foreach ($ids as $legoid)
		{
			$query = "INSERT INTO DB_Set SET LegoID='".$mysqli->real_escape_string($legoid)."';";
			$mysqli->query($query);
		}
		$query = "SELECT LegoID,ThemeID,ETitle,Age,Minifigs,Year,Pieces,USPrice,Weight,Length,Width,Height,UPC,EAN,ItemSN,BK_Subset FROM DB_Set WHERE LegoID IN(".$argv[1].");";
		$result = $mysqli->query($query);
	}

}
else
{
	$query = "SELECT LegoID,ThemeID,ETitle,Age,Minifigs,Year,Pieces,USPrice,Weight,Length,Width,Height,UPC,EAN,ItemSN,BK_Subset FROM DB_Set WHERE (NOW() - INTERVAL 3 DAY) > LastSync Limit 0,30;";
	$result = $mysqli->query($query);

}

if ($result->num_rows)
{
	while ($row = $result->fetch_array(MYSQLI_ASSOC))
	{
		$legoid = $row["LegoID"];
		$legoinfo = new stdClass();
		$legoinfo->{'LegoID'} = $legoid;
		if ($row["BK_Subset"] != "")
		{
			$setid = $legoid.$row["BK_Subset"];
		}
		else
		{
			$setid = $legoid."-1";
		}
		$url = 'brickset.com/sets/'.$setid;
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
			$legoinfo->{'Pieces'} = 0;
			$legoinfo->{'Minifigs'} = 0;
			$titleDom = $html->find('section[class=main]/header/h1', 0);
			if (isset($titleDom))
			{
				$legoinfo->{'ETitle'} = trim(str_replace($setid.": ", "", $titleDom->plaintext));
				$legoinfo->{'ETitle'} = trim(str_replace("{Random bag}", "", $legoinfo->{'ETitle'}));
			}
		
			$dom = new DOMDocument();
			$dom->loadHTML($html->find('section[class=featurebox]/div[class=text]', 0)->innertext);
			$nodes = $dom->getElementsByTagName('dl');
			foreach ($nodes as $node)
			{
				$dl = getArray($node);
			}
			$dt = $dl["dt"];
			$dd = $dl["dd"];
			for ($i = 0; $i <= count($dt); $i++)
			{
				if ($dt[$i]["#text"] == "Theme")
				{
					$legoinfo->{'Theme'} = trim($dd[$i]["a"]);
				
					if (isset($arrTheme[$legoinfo->{'Theme'}]))
					{
						$legoinfo->{'ThemeID'} = $arrTheme[$legoinfo->{'Theme'}];
					}
					else
					{
						$querytheme = "INSERT INTO DB_Theme SET ETheme='".$mysqli->real_escape_string($legoinfo->{'Theme'})."';";
						$mysqli->query($querytheme);
						$arrTheme[$legoinfo->{'Theme'}] = $mysqli->insert_id;
						$legoinfo->{'ThemeID'} = $arrTheme[$legoinfo->{'Theme'}];
					}

				}
				elseif ($dt[$i]["#text"] == "Subtheme")
				{
					$legoinfo->{'Subtheme'} = trim($dd[$i]["a"]);
				}
				elseif ($dt[$i]["#text"] == "Year released")
				{
					$legoinfo->{'Year'} = trim($dd[$i]["a"]);
				}
				elseif ($dt[$i]["#text"] == "Pieces")
				{
					$legoinfo->{'Pieces'} = trim($dd[$i]["a"]);
				}
				elseif ($dt[$i]["#text"] == "Age range")
				{
					$legoinfo->{'Age'} = str_replace(" ", "", trim($dd[$i]["#text"]));
				}
				elseif ($dt[$i]["#text"] == "Minifigs")
				{
					$legoinfo->{'Minifigs'} = trim($dd[$i]["a"]);
				}
				elseif ($dt[$i]["#text"] == "RRP")
				{
					if (preg_match("/US.(\d+\.\d+)/", trim($dd[$i]["#text"]), $m))
					{
						$legoinfo->{'USPrice'} = (float)$m[1];
					}
				}
				elseif ($dt[$i]["#text"] == "Barcodes")
				{
					$barcodes = trim($dd[$i]["#text"]);
					//echo var_dump($dd[$i]);
					if (preg_match('/UPC: (\d{12})/', $barcodes,  $matches))
					{
						$legoinfo->{'UPC'} = (int)$matches[1];
					}
					else
					{
						$legoinfo->{'UPC'} = null;
					}
					if (preg_match('/EAN: (\d{13})/', $barcodes,  $matches))
					{
						$legoinfo->{'EAN'} = (int)$matches[1];
					}
					else
					{
						$legoinfo->{'EAN'} = null;
					}
				}
				elseif ($dt[$i]["#text"] == "LEGO item numbers")
				{
					$legosnstr = trim($dd[$i]["#text"]);
					if (preg_match('/NA: (\d{7})/', $legosnstr,  $m))
					{
						$legoinfo->{'ItemSN'} = (int)$m[1];
					}
					else
					{
						$legoinfo->{'ItemSN'} = null;
					}
				}											
			}
		}

		if ($row['ThemeID'] == "17") //Gears
		{
			$url = 'http://www.bricklink.com/catalogItem.asp?G='.$legoid;

		}
		else
		{
			$url = 'http://www.bricklink.com/catalogItem.asp?S='.$setid;
		}
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
			$legoinfo->{'Weight'} = 0;
			$legoinfo->{'Length'} = 0;
			$weightstr = $html->find('/html/body/center/table[3]/tbody/tr/td/table/tbody/tr/td/table/tbody/tr/td/table[3]/tbody/tr[1]/td/table/tbody/tr/td[4]', 0)->plaintext;
			if (preg_match('/(\d+)/', $weightstr,  $m))
			{
				$legoinfo->{'Weight'} = (int)$m[1];
			}
			else
			{
				$legoinfo->{'Weight'} = null;
			}
			$sizestr = $html->find('/html/body/center/table[3]/tbody/tr/td/table/tbody/tr/td/table/tbody/tr/td/table[3]/tbody/tr[1]/td/table/tbody/tr/td[5]', 0)->plaintext;
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
		}
		$changelog = "";
		$updatestr = "";
		if ($row["ETitle"] != $legoinfo->{'ETitle'})
		{
			$changelog .= "ETitle: ".$row["ETitle"]." -> ".$legoinfo->{'ETitle'}.", ";
			$updatestr .= "ETitle='".$mysqli->real_escape_string($legoinfo->{'ETitle'})."', ";
		}
		if ($row["ThemeID"] != $legoinfo->{'ThemeID'} && isset($legoinfo->{'ThemeID'}) )
		{
			$changelog .= "ThemeID: ".$row["ThemeID"]." -> ".$legoinfo->{'ThemeID'}.", ";
			$updatestr .= "ThemeID='".$mysqli->real_escape_string($legoinfo->{'ThemeID'})."', ";
		}
		if ($row["Age"] != $legoinfo->{'Age'})
		{
			$changelog .= "Age: ".$row["Age"]." -> ".$legoinfo->{'Age'}.", ";
			$updatestr .= "Age='".$mysqli->real_escape_string($legoinfo->{'Age'})."', ";
		}
		if ($row["Minifigs"] != $legoinfo->{'Minifigs'} && $legoinfo->{'Minifigs'} > 0)
		{
			$changelog .= "Minifigs: ".$row["Minifigs"]." -> ".$legoinfo->{'Minifigs'}.", ";
			$updatestr .= "Minifigs='".$mysqli->real_escape_string($legoinfo->{'Minifigs'})."', ";
		}
		if ($row["Year"] != $legoinfo->{'Year'})
		{
			$changelog .= "Year: ".$row["Year"]." -> ".$legoinfo->{'Year'}.", ";
			$updatestr .= "Year='".$mysqli->real_escape_string($legoinfo->{'Year'})."', ";
		}
		if ($row["Pieces"] != $legoinfo->{'Pieces'} && $legoinfo->{'Pieces'} > 0)
		{
			$changelog .= "Pieces: ".$row["Pieces"]." -> ".$legoinfo->{'Pieces'}.", ";
			$updatestr .= "Pieces='".$mysqli->real_escape_string($legoinfo->{'Pieces'})."', ";
		}
		if ($row["USPrice"] != $legoinfo->{'USPrice'} && $legoinfo->{'USPrice'} > 0)
		{
			$changelog .= "USPrice: ".$row["USPrice"]." -> ".$legoinfo->{'USPrice'}.", ";
			$updatestr .= "USPrice='".$mysqli->real_escape_string($legoinfo->{'USPrice'})."', ";
		}
		if ($row["UPC"] != $legoinfo->{'UPC'})
		{
			$changelog .= "UPC: ".$row["UPC"]." -> ".$legoinfo->{'UPC'}.", ";
			$updatestr .= "UPC='".$mysqli->real_escape_string($legoinfo->{'UPC'})."', ";
		}
		if ($row["EAN"] != $legoinfo->{'EAN'})
		{
			$changelog .= "EAN: ".$row["EAN"]." -> ".$legoinfo->{'EAN'}.", ";
			$updatestr .= "EAN='".$mysqli->real_escape_string($legoinfo->{'EAN'})."', ";
		}
		if ($row["ItemSN"] != $legoinfo->{'ItemSN'})
		{
			$changelog .= "ItemSN: ".$row["ItemSN"]." -> ".$legoinfo->{'ItemSN'}.", ";
			$updatestr .= "ItemSN='".$mysqli->real_escape_string($legoinfo->{'ItemSN'})."', ";
		}
		if ($row["Weight"] != $legoinfo->{'Weight'} && isset($legoinfo->{'Weight'}))
		{
			$changelog .= "Weight: ".$row["Weight"]." -> ".$legoinfo->{'Weight'}.", ";
			$updatestr .= "Weight='".$mysqli->real_escape_string($legoinfo->{'Weight'})."', ";
		}
		if ($row["Length"] != $legoinfo->{'Length'} && isset($legoinfo->{'Length'}))
		{
			$changelog .= "Length: ".$row["ItemSN"]." -> ".$legoinfo->{'Length'}.", ";
			$updatestr .= "Length='".$mysqli->real_escape_string($legoinfo->{'Length'})."', ";
		}
		if ($row["Width"] != $legoinfo->{'Width'} && isset($legoinfo->{'Width'}))
		{
			$changelog .= "Width: ".$row["Width"]." -> ".$legoinfo->{'Width'}.", ";
			$updatestr .= "Width='".$mysqli->real_escape_string($legoinfo->{'Width'})."', ";
		}
		if ($row["Height"] != $legoinfo->{'Height'} && isset($legoinfo->{'Height'}))
		{
			$changelog .= "Height: ".$row["Height"]." -> ".$legoinfo->{'Height'}.", ";
			$updatestr .= "Height='".$mysqli->real_escape_string($legoinfo->{'Height'})."', ";
		}
		$updatequery = "UPDATE DB_Set SET ".$updatestr."LastSync=NOW() WHERE LegoID = ".$mysqli->real_escape_string($legoinfo->{'LegoID'}).";";
		if ($changelog != "")
		{
			echo trim($row["LegoID"].": ".$changelog, ", ")."\r\n";
		}
		$mysqli->query($updatequery);
	}
}
$mysqli->close();


?>