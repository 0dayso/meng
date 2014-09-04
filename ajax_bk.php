<?php
function getArray($node) { 
    $array = false; 

	//echo var_dump($node);
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
$legoid = $_GET["legoid"];

if (isset($legoid))
{
	$legoinfo = new stdClass();
	$legoinfo->{'LegoID'} = $legoid;
	$url = 'brickset.com/sets/'.$legoid.'-1';
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
			$legoinfo->{'ETitle'} = trim(str_replace($legoid."-1: ", "", $titleDom->plaintext));
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
		/*		
		foreach ($html->find('section[class=featurebox]/div[class=text]') as $list)
		{
			$listhtml = str_get_html($list->innertext);
			if ($listhtml->find('span', 0)->plaintext == "Theme")
			{
				$legoinfo->{'Theme'} = str_get_html(trim(str_replace($listhtml->find('span', 0)->outertext, "", $listhtml->innertext)))->plaintext;
			}
			elseif ($listhtml->find('span', 0)->plaintext == "Subtheme")
			{
				$legoinfo->{'Subtheme'} = str_get_html(trim(str_replace($listhtml->find('span', 0)->outertext, "", $listhtml->innertext)))->plaintext;
			}
			elseif ($listhtml->find('span', 0)->plaintext == "Year released")
			{
				$legoinfo->{'Year'} = (int)str_get_html(trim(str_replace($listhtml->find('span', 0)->outertext, "", $listhtml->innertext)))->plaintext;
			}
			elseif ($listhtml->find('span', 0)->plaintext == "Pieces")
			{
				$legoinfo->{'Pieces'} = (int)trim(str_replace($listhtml->find('span', 0)->outertext, "", $listhtml->innertext));
			}
			elseif ($listhtml->find('span', 0)->plaintext == "Age range")
			{
				$legoinfo->{'Age'} = str_replace(" ", "", trim(str_replace($listhtml->find('span', 0)->outertext, "", $listhtml->innertext)));
			}
			elseif ($listhtml->find('span', 0)->plaintext == "Minifigs")
			{
				$legoinfo->{'Minifigs'} = (int)trim(str_replace($listhtml->find('span', 0)->outertext, "", $listhtml->innertext));
			}
			elseif ($listhtml->find('span', 0)->plaintext == "RRP")
			{
				if (preg_match("/US.(\d+\.\d+)/", trim(str_replace($listhtml->find('span', 0)->outertext, "", $listhtml->innertext)), $m))
				{
					$legoinfo->{'USPrice'} = (float)$m[1];
				}
			}
			elseif ($listhtml->find('span', 0)->plaintext == "Barcodes")
			{
				$barcodes = trim(str_replace($listhtml->find('span', 0)->outertext, "", $listhtml->innertext));
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
			elseif ($listhtml->find('span', 0)->plaintext == "LEGO item numbers")
			{
				$legosnstr = trim(str_replace($listhtml->find('span', 0)->outertext, "", $listhtml->innertext));
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
		*/
	}
}

echo json_encode($legoinfo);


?>