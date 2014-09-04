<?php
if ($_GET["filename"])
{
  $filename = $_GET["filename"];
  $image_name = "./my/".$filename.".jpg";  
}
else
{
  print "filename={filename}&margin=5&size=470&nomark=0&output={jpg|png}";
  exit;
}

list($width,$height) = getimagesize($image_name);  
$im = ImageCreateFromjpeg($image_name);
ImageSaveAlpha($im, true);

//if (($image_name != $filepath) or ($_GET["force"]))
//{
//  ImagePng($im, $filepath);
//}

if ($_GET["nocrop"])
{
$dest = ImageCreateTruecolor($width, $height);
$white = ImageColorAllocate($dest, 255, 255, 255);
ImageFill($dest, 0, 0, $white);
ImageColorTransparent($dest, $white);
ImageCopy($dest, $im, 0, 0, 0, 0, $width, $height);
ImageDestroy($im);
$nwidth = $width;
$nheight = $height;
}
else
{
$mwidth = $width /2;
$mheight = $height /2;

$top = $mheight;
$right = $mwidth;
$left = $mwidth;
$bottom = $mheight;
for ($x = $mwidth; $x < $width; $x++)
{
  for ($y = 0; $y < $top; $y++)
  {
    $rgb = imagecolorat($im, $x, $y);
    $colors = imagecolorsforindex($im, $rgb);
    if (($colors['red'] < 128 || $colors['green'] < 128 || $colors['blue'] < 128) && ($colors['alpha'] < 64))
    {
    //有色点
      if ($y < $top)
      {
        $top = $y;
      }
    }
  }
}


for ($x = 0; $x < $mwidth; $x++)
{
  for ($y = $height-1; $y > $bottom; $y--)
  {
    $rgb = imagecolorat($im, $x, $y);
    $colors = imagecolorsforindex($im, $rgb);
    if (($colors['red'] < 128 || $colors['green'] < 128 || $colors['blue'] < 128) && ($colors['alpha'] < 64))
    {
      if ($y > $bottom)
      {
        $bottom = $y;
      }
    }
  }
}


for ($y = $mheight; $y > 0; $y--)
{
  for ($x = $width-1; $x > $right; $x--)
  {
    $rgb = imagecolorat($im, $x, $y);
    $colors = imagecolorsforindex($im, $rgb);
    if (($colors['red'] < 128 || $colors['green'] < 128 || $colors['blue'] < 128) && ($colors['alpha'] < 64))
    {
      if ($x > $right)
      {
        $right = $x;
      }
    }
  }
}

for ($y = 0; $y < $height; $y++)
{
  for ($x = 0; $x < $left; $x++)
  {
    $rgb = imagecolorat($im, $x, $y);
    $colors = imagecolorsforindex($im, $rgb);
    if (($colors['red'] < 128 || $colors['green'] < 128 || $colors['blue'] < 128) && ($colors['alpha'] < 64))
    {
      if ($x < $left)
      {
        $left = $x;
      }
    }
  }
}




$nheight = $bottom - $top + 2;
$nwidth = $right - $left + 2;
$dest = ImageCreateTruecolor($nwidth, $nheight);

$white = ImageColorAllocate($dest, 255, 255, 255);
ImageFill($dest, 0, 0, $white);
ImageColorTransparent($dest, $white);

ImageCopy($dest, $im, 0, 0, $left - 1, $top - 1, $nwidth, $nheight);
ImageDestroy($im);
}

if ($_GET["margin"])
{
  $margin = $_GET["margin"];
}
elseif ($_GET["nocrop"])
{
  $margin = 0;
}
else
{
  $margin = 5;
}
if ($_GET["size"])
{
  $size = $_GET["size"]-2*$margin;
}
else
{
  $size = 790;
}

if ($nheight > $nwidth)
{
  $theight = $size;
  $twidth = $size * $nwidth / $nheight;
  $left = ($size - $twidth)/2;
  $top = $margin;
}
else
{
  $twidth = $size;
  $theight = $size * $nheight / $nwidth;
  $top = ($size - $theight)/2;
  $left = $margin;
}



if ($_GET["nocrop"])
{
  $fwidth = $twidth;
  $fheight = $theight;
  $thumb = ImageCreateTrueColor($twidth, $theight);
  ImageFill($thumb, 0, 0, $white);
  ImageColorTransparent($thumb, $white);
  ImageCopyReSampled($thumb, $dest, 0, 0, 0, 0, $twidth, $theight, $nwidth, $nheight);
}
else
{
  $fwidth = $size+2*$margin;
  $fheight = $size+2*$margin;
  $thumb = ImageCreateTrueColor($fwidth, $fheight);
  ImageFill($thumb, 0, 0, $white);
  ImageColorTransparent($thumb, $white);
  ImageCopyReSampled($thumb, $dest, $left, $top, 0, 0, $twidth, $theight, $nwidth, $nheight);
} 
ImageDestroy($dest);

if (!($_GET["nomark"]))
{

    $logo = ImageCreateFrompng("./my/logo.png");
    $legoheight = 43;
    $legowidth = 94;

  ImageSaveAlpha($logo, true);

  ImageCopy($thumb, $logo, 15, 15, 0, 0, $legowidth, $legoheight);
  ImageDestroy($logo);
}

if ($_GET["output"])
{
  Header("Content-type: image/png");
  Imagepng($thumb);
}
else
{
  if ($_GET["quality"])
  {
    $quality = $_GET["quality"];
  }
  else
  {
    $quality = 100;
  }
  Header("Content-type: image/jpeg");
  Imagejpeg($thumb, NULL, $quality);
}

ImageDestroy($thumb);

?>

