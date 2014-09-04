<?php
Header("Content-type: image/jpeg");

$oldimage_name = "./ID.jpg";
list($width,$height) = getimagesize($oldimage_name);  

$im = ImageCreateTrueColor($width , $height);
//$white = ImageColorAllocate($im, 255, 255, 255);
//ImageFill($im, 0, 0, $white);
//ImageColorTransparent($im, $white);
ImageSaveAlpha($im, true);


$image_src = ImageCreateFromjpeg($oldimage_name);
  
imagecopyresampled($im, $image_src, 0, 0, 0, 0, $width, $height, $width, $height);

$gray = ImageColoralLocateAlpha($im , 0 , 0 , 0, 95);
$fontfile = "./font.ttf";

if ($_GET["text"])
{
  $str = $_GET["text"];
}
else
{
  $str = "仅供EMS海关报关使用"; //iconv('GB2312','UTF-8','中文'); /*将 gb2312 的字符集转换成 UTF-8 的字符*/
}


ImageTTFText($im, 18, 330, 150, 150, $gray , $fontfile , $str);
ImageTTFText($im, 18, 330, 450, 150, $gray , $fontfile , $str);

ImageTTFText($im, 18, 330, 0, 350, $gray , $fontfile , $str);
ImageTTFText($im, 18, 330, 300, 350, $gray , $fontfile , $str);
ImageTTFText($im, 18, 330, 600, 350, $gray , $fontfile , $str);




/* 加入中文水印 */

Imagejpeg($im);
ImageDestroy($im);
?>

