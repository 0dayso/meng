<?php
  require_once("simple_html_dom.php");
  date_default_timezone_set("Asia/Shanghai");

define('WORD_WIDTH',9);
define('WORD_HIGHT',13);
define('OFFSET_X',7);
define('OFFSET_Y',3);
define('WORD_SPACING',4);

class valite
{
	public function setImage($Imagestring)
	{
		$this->ImageString = $Imagestring;
	}
	public function getData()
	{
		return $data;
	}
	public function getResult()
	{
		return $DataArray;
	}
	public function getHec()
	{
		$res = imagecreatefromstring($this->ImageString);
		//$size = getimagesizefromstring($this->ImageString);
		//var_dump($size);
		$size = array(86,20);
		$data = array();
		for($i=0; $i < $size[1]; ++$i)
		{
			for($j=0; $j < $size[0]; ++$j)
			{
				$rgb = imagecolorat($res,$j,$i);
				$rgbarray = imagecolorsforindex($res, $rgb);
				if($rgbarray['red'] < 125 || $rgbarray['green']<125
				|| $rgbarray['blue'] < 125)
				{
					$data[$i][$j]=1;
				}else{
					$data[$i][$j]=0;
				}
			}
		}
		$this->DataArray = $data;
		$this->ImageSize = $size;
	}
	public function run()
	{
		$result="";
		// ≤È’“4∏ˆ ˝◊÷
		$data = array("","","","","","");
		for($i=0;$i<6;++$i)
		{
			$x = ($i*(WORD_WIDTH+WORD_SPACING))+OFFSET_X;
			$y = OFFSET_Y;
			for($h = $y; $h < (OFFSET_Y+WORD_HIGHT); ++ $h)
			{
				for($w = $x; $w < ($x+WORD_WIDTH); ++$w)
				{
					$data[$i].=$this->DataArray[$h][$w];
				}
			}
			
		}

		// Ω¯––πÿº¸◊÷∆•≈‰
		foreach($data as $numKey => $numString)
		{
			$max=0.0;
			$num = 0;
			foreach($this->Keys as $key => $value)
			{
				$percent=0.0;
				similar_text($value, $numString,$percent);
				if(intval($percent) > $max)
				{
					$max = $percent;
					$num = $key;
					if(intval($percent) > 95)
						break;
				}
			}
			$result.=$num;
		}
		$this->data = $result;
		// ≤È’“◊Óº—∆•≈‰ ˝◊÷
		return $result;
	}

	public function Draw()
	{
		for($i=0; $i<$this->ImageSize[1]; ++$i)
		{
	        for($j=0; $j<$this->ImageSize[0]; ++$j)
		    {
			    echo $this->DataArray[$i][$j];
	        }
		    echo "\n";
		}
	}
	public function __construct()
	{
		$this->Keys = array(
		'0'=>'000111000011111110011000110110000011110000011110000011110000011110000011110000011110000011011000110011111110000111000',
		'1'=>'000111000011111000011111000000011000000011000000011000000011000000011000000011000000011000000011000011111111011111111',
		'2'=>'011111000111111100100000110000000111000000110000001100000011000000110000001100000011000000110000000011111110111111110',
		'3'=>'011111000111111110100000110000000110000001100011111000011111100000001110000000111000000110100001110111111100011111000',
		'4'=>'000001100000011100000011100000111100001101100001101100011001100011001100111111111111111111000001100000001100000001100',
		'5'=>'111111110111111110110000000110000000110000000111110000111111100000001110000000111000000110100001110111111100011111000',
		'6'=>'000111100001111110011000010011000000110000000110111100111111110111000111110000011110000011011000111011111110000111100',
		'7'=>'011111111011111111000000011000000010000000110000001100000001000000011000000010000000110000000110000001100000001100000',
		'8'=>'001111100011111110011000110011000110011101110001111100001111100011101110110000011110000011111000111011111110001111100',
		'9'=>'001111000011111110111000111110000011110000011111000111011111111001111011000000011000000110010000110011111100001111000',
	);
	}
	protected $ImagePath;
	protected $DataArray;
	protected $ImageSize;
	protected $data;
	protected $Keys;
	protected $NumStringArray;

}

function get_htmlresponse($query_type, $emsid)
{
  if ($query_type == "chs")
  {
    $queryurl = "http://www.ems.com.cn/ems/order/singleQuery_t";
  }
  else
  {
    $queryurl = "http://www.ems.com.cn/ems/order/singleQuery_e";
  }

  //Get the validation code and cookie;
  $url = "http://www.ems.com.cn/ems/rand";
  $ch = curl_init();
  $timeout = 5; 
  curl_setopt($ch, CURLOPT_URL, $url); 
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
  curl_setopt($ch, CURLOPT_BINARYTRANSFER, 1);
  curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
  curl_setopt($ch, CURLOPT_HEADER, 1);
  $result = curl_exec($ch); 
  curl_close($ch);
  
  list($header, $body) = explode("\r\n\r\n", $result);
  
  $cookie = "";

  foreach (explode("\r\n", $header) as $line)
  {
  	if (preg_match('/^Set-Cookie: (.*?);/m', $line, $m))
  	{
  		$cookie .= $m[1]."; ";
  	}
  }

  $valite = new Valite();
  $valite->setImage($body);
  $valite->getHec();
  $code = $valite->run();

  $postData = "mailNum=".$emsid."&checkCode=".$code;
  
  $ch = curl_init();
  $timeout = 5; 
  curl_setopt($ch, CURLOPT_URL, $queryurl); 
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
  curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
  curl_setopt($ch, CURLOPT_COOKIE, $cookie);
  curl_setopt($ch, CURLOPT_POST, 1);
  curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
  $result = curl_exec($ch); 
  curl_close($ch);
  
  return $result;

}


	
function array_rpush($tarArray, $value)
{
	$retArray = array();
	array_push($retArray, $value);
	foreach($tarArray as $row)
	{
		array_push($retArray, $row);
	}
	$tarArray = $retArray;
	return $tarArray;
}

  $emsid = strtoupper($_GET["emsid"]);
  $return_type = strtolower($_GET["r"]);
  
  $arrTrans = array();

  /*
  $url = "http://weixin.ems.com.cn/mpa/collectOrder/search.do?mailNo=".$emsid;
  $ch = curl_init();
  $timeout = 5; 
  curl_setopt($ch, CURLOPT_URL, $url);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
  curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
  $result = curl_exec($ch); 

  $html = str_get_html($result);
	$deliverstatus = str_get_html($html->find('table/tbody', 1)->innertext)->find('tr[2]/td', 0)->plaintext;
	$content = str_get_html($html->find('table/tbody', 2)->innertext);
	$yearstr = str_get_html($html->find('table/tbody', 1)->innertext)->find('tr[3]/td', 0)->plaintext;
	preg_match('/(\d{4})-\d{2}-\d{2}/', $yearstr, $match);
	$year = $match[1];
	
	if ($deliverstatus == "邮件状态：妥投")
	{
		$delivered = true;
	}
	else
	{
		$delivered = false;
	}

	
	if ($content <> false)
	{
		$i = 0;
		foreach($content->find('tr') as $row)
		{
			if ($i++ > 0)
			{
				$line = str_get_html($row->innertext)->find('td');
				$status = explode("：", str_replace("&nbsp;&nbsp;&nbsp;", " ", trim($line[2]->plaintext)));
				//var_dump(trim(str_replace("&nbsp;", " ", $line[0]->plaintext)));

				$dateTime = new DateTime(trim(str_replace("&nbsp;", " ", $line[0]->plaintext)), new DateTimeZOne('Asia/Shanghai'));
				
				$arrline = array('time' => $dateTime, 'loc' => $status[1], 'desc' => $status[0]);
				$arrTrans = array_rpush($arrTrans, $arrline);
			}
		}
	}
  */
  
  $html = str_get_html(get_htmlresponse("chs", $emsid));
  $trackdiv = str_get_html($html->find('div[class=mailnum_result_box]', 0)->innertext);
  $title = trim($html->find('div[class=mailnum_result_box]/p', 0)->innertext);
  if ($trackdiv && $title != "")
  {
    //echo "try the chs version";
    $content = str_get_html($trackdiv->innertext);
    $i = 0;
    foreach($content->find('table/tbody/tr') as $row)
    {
        if ($i++ > 0)
        {
            $line = str_get_html($row->innertext)->find('td');
            $time = str_replace("&nbsp;&nbsp;&nbsp;", " ", trim($line[0]->plaintext));
            $datetime = new DateTime($time);
  			$datetime->setTimezone(new DateTimeZone('Asia/Shanghai'));

            $loc = str_replace("&nbsp;&nbsp;&nbsp;", " ", trim($line[1]->plaintext));
            $desc = str_replace("&nbsp;&nbsp;&nbsp;", " ", trim($line[2]->plaintext));
            $arrline = array('time' => $datetime, 'loc' => $loc, 'desc' => $desc);
            array_push($arrTrans, $arrline);
        }
    }
  }
  else
  {
    //no record in chinese version, try the english version
  
    $html = str_get_html(get_htmlresponse("eng", $emsid));
    if ($html->find('div[id=singleErrors]',0))
    {
      echo "查无此单!";
      return;
    }
    else
    {
      $trackdiv = str_get_html($html->find('div[class=mailnum_result_box]', 0)->innertext);
      $content = str_get_html($trackdiv->innertext);
      $i = 0;
      foreach($content->find('table/tr') as $row)
      {
        if ($i++ > 0)
        {
            $line = str_get_html($row->innertext)->find('td');
            $time = str_replace("&nbsp;&nbsp;&nbsp;", " ", trim($line[0]->plaintext));
            
            $datetime = new DateTime($time);
  			$datetime->setTimezone(new DateTimeZone('Asia/Shanghai'));

            $loc = str_replace("&nbsp;&nbsp;&nbsp;", " ", trim($line[1]->plaintext));
            $desc = str_replace("&nbsp;&nbsp;&nbsp;", " ", trim($line[2]->plaintext));
            $arrline = array('time' => $datetime, 'loc' => $loc, 'desc' => $desc);
            array_push($arrTrans, $arrline);
        }
      }
    }
  }
  

  if ($return_type == "oneline")
  {
	$result = array_pop($arrTrans);
	$resultstr = $result['time']." ".$result['loc']." ".$result['desc'];
	echo $resultstr;
  }
  elseif ($return_type == "time")
  {
  	//$DeliveryTime = array_pop($arrTrans);
  	//var_dump($DeliveryTime);
  	$ret = array ('ShippingTime'=>$arrTrans[0]['time'], 'DeliveryTime'=> array_pop($arrTrans)['time']);
  	echo json_encode($ret);
  }
  elseif ($return_type == "dtime")
  {
  	//$DeliveryTime = array_pop($arrTrans);
  	//var_dump($DeliveryTime);
  	$ret = array ('DeliveryTime'=> array_pop($arrTrans)['time']);
  	echo json_encode($ret);
  }
  else
  {
    echo json_encode($arrTrans);
  }
?>
