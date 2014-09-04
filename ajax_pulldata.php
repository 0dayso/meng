<?php
require_once("simple_html_dom.php");
$legoid = $_GET["legoid"];

if (isset($legoid))
{
	$legoinfo->{'LegoID'} = $legoid;
	$url = 'www.brickset.com/detail/?Set='.$legoid.'-1';
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
		
		foreach ($html->find('div[id=menuPanel]/div/ul/li') as $list)
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
				$legoinfo->{'Year'} = str_get_html(trim(str_replace($listhtml->find('span', 0)->outertext, "", $listhtml->innertext)))->plaintext;
			}
			elseif ($listhtml->find('span', 0)->plaintext == "Pieces")
			{
				$legoinfo->{'Pieces'} = trim(str_replace($listhtml->find('span', 0)->outertext, "", $listhtml->innertext));
			}
			elseif ($listhtml->find('span', 0)->plaintext == "Age range")
			{
				$legoinfo->{'Age'} = trim(str_replace($listhtml->find('span', 0)->outertext, "", $listhtml->innertext));
			}
			elseif ($listhtml->find('span', 0)->plaintext == "Minifigs")
			{
				$legoinfo->{'Minifigs'} = trim(str_replace($listhtml->find('span', 0)->outertext, "", $listhtml->innertext));
			}
			elseif ($listhtml->find('span', 0)->plaintext == "Barcodes")
			{
				$barcodes = trim(str_replace($listhtml->find('span', 0)->outertext, "", $listhtml->innertext));
				if (preg_match('/UPC: (\d{12})/', $barcodes,  $matches))
				{
					$legoinfo->{'UPC'} =  $matches[1];
				}
				else
				{
					$legoinfo->{'UPC'} = null;
				}
				if (preg_match('/EAN: (\d{13})/', $barcodes,  $matches))
				{
					$legoinfo->{'EAN'} =  $matches[1];
				}
				else
				{
					$legoinfo->{'EAN'} = null;
				}
			}
			elseif ($listhtml->find('span', 0)->plaintext == "LEGO item numbers")
			{
				$legosnstr = trim(str_replace($listhtml->find('span', 0)->outertext, "", $listhtml->innertext));
				if (preg_match('/NA: (\d{7})/', $legosnstr,  $matches))
				{
					$legoinfo->{'ItemSN'} =  $matches[1];
				}
				else
				{
					$legoinfo->{'ItemSN'} = null;
				}
			}
		}
	}
}

echo json_encode($legoinfo);
?>