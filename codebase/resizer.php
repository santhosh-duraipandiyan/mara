<?php

// imgmaxsize('/mara/img/test.jpg',800,600);


function imgmaxsize($filename, $maxwidth=1024, $maxheight=768, $verbose=1){
 
 $image=new GDImage(); 
 if($image->load($filename)){
   if ($verbose) echo "Resizing $filename to fit: $maxwidth x $maxheight<br>";
   $resized=$image->setmaxsize($maxwidth,$maxheight);
   if ($resized) $image->save($filename); 
 } else {
   if ($verbose) echo "$filename: Not an image format, no resizing done.<br>";
   $resized=false;
 }
   unset($image);
   return $resized;
}


function getpicinfo($filename, $maxwidth=240, $maxheight=80){
 $image=new GDImage(); 
 if($image->load($filename)){
   $picinfo=$image->picinfo($maxwidth,$maxheight);
 } else {
   $picinfo[0]=0;
   // $picinfo[0] flags status. 
 }
   unset($image);
   return $picinfo;
}
 
 
/*
* File: SimpleImage.php
* Author: Simon Jarvis
* Copyright: 2006 Simon Jarvis
* Date: 08/11/06
* Link: http://www.white-hat-web-design.co.uk/articles/php-image-resizing.php
*
* This program is free software; you can redistribute it and/or
* modify it under the terms of the GNU General Public License
* as published by the Free Software Foundation; either version 2
* of the License, or (at your option) any later version.
*
* This program is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
* GNU General Public License for more details:
* http://www.gnu.org/licenses/gpl.html
*
*/
 
class GDImage {
 
   var $image;
   var $image_type;
 
   function load($filename) {
 
      $image_info = getimagesize($filename);
      $this->image_type = $image_info[2];
      if( $this->image_type == IMAGETYPE_JPEG ) {
 
         $this->image = imagecreatefromjpeg($filename);
      } elseif( $this->image_type == IMAGETYPE_GIF ) {
 
         $this->image = imagecreatefromgif($filename);
      } elseif( $this->image_type == IMAGETYPE_PNG ) {
 
         $this->image = imagecreatefrompng($filename);
      } elseif( $this->image_type == IMAGETYPE_BMP ) {
 
         $this->image = imagecreatefrombmp($filename);
      } else {
         return false; 
      }
      return true;
   }
   function save($filename, $image_type=IMAGETYPE_JPEG, $compression=75, $permissions=null) {
 
      if( $image_type == IMAGETYPE_JPEG ) {
         imagejpeg($this->image,$filename,$compression);
      } elseif( $image_type == IMAGETYPE_GIF ) {
 
         imagegif($this->image,$filename);
      } elseif( $image_type == IMAGETYPE_PNG ) {
 
         imagepng($this->image,$filename);
      }
      if( $permissions != null) {
 
         chmod($filename,$permissions);
      }
   }
   function output($image_type=IMAGETYPE_JPEG) {
 
      if( $image_type == IMAGETYPE_JPEG ) {
         imagejpeg($this->image);
      } elseif( $image_type == IMAGETYPE_GIF ) {
 
         imagegif($this->image);
      } elseif( $image_type == IMAGETYPE_PNG ) {
 
         imagepng($this->image);
      }
   }
   function getWidth() {
 
      return imagesx($this->image);
   }
   function getHeight() {
 
      return imagesy($this->image);
   }
   function resizeToHeight($height) {
 
      $ratio = $height / $this->getHeight();
      $width = $this->getWidth() * $ratio;
      $this->resize($width,$height);
   }
 
   function resizeToWidth($width) {
      $ratio = $width / $this->getWidth();
      $height = $this->getheight() * $ratio;
      $this->resize($width,$height);
   }
 
   function scale($scale) {
      $width = $this->getWidth() * $scale/100;
      $height = $this->getheight() * $scale/100;
      $this->resize($width,$height);
   }

   function setmaxsize($maxwidth=1600, $maxheight=1200) {
   // Added IWRC, Apr 2013. Sets pixel size limit on uploaded images. 
      $width = imagesx($this->image);
      $height = imagesy($this->image);
      // echo "Existing dimensions: $width x $height <br>";
      if ($maxheight<=0 || $maxwidth<=0) return false;
      $wscale=$width/$maxwidth;
      $hscale=$height/$maxheight;
      $scaling=1;
      if ($wscale>=1.01)$scaling=$wscale;
      if ($hscale>$scaling)$scaling=$hscale;
      if ($scaling>1.01) {
          $newWidth=round($width/$scaling);
          $newHeight=round($height/$scaling);
          $this->resize($newWidth,$newHeight);
          // echo "New Size: " . imagesx($this->image) . " x " . imagesy($this->image). "<br>";
      return true;
      }
   return false;   
   }

   function picinfo($maxwidth=240, $maxheight=80) {
   // Added IWRC, Oct 2014. Gets size and scaling data to fit image to rectangle. 
      $picinfo['scaling']=0;
      $width = imagesx($this->image);
      $height = imagesy($this->image);
      // echo "Existing dimensions: $width x $height <br>";
      $newWidth=$width;
      $newHeight=$height;
      if ($maxheight<=0 || $maxwidth<=0) return false;
      $wscale=$width/$maxwidth;
      $hscale=$height/$maxheight;
      $scaling=1;
      if ($wscale>=1.01)$scaling=$wscale;
      if ($hscale>$scaling)$scaling=$hscale;
      if ($scaling>1.01) {
          $newWidth=round($width/$scaling);
          $newHeight=round($height/$scaling);
          $picinfo['scaling']=1;
      }
     $picinfo['x']=$width;   
     $picinfo['y']=$height;   
     $picinfo['dx']=$newWidth;   
     $picinfo['dy']=$newHeight;   
     $picinfo[0]=1;   
     return $picinfo;   
   }

   function resize($width,$height) {
	$new_image = imagecreatetruecolor($width, $height);
	if( $this->image_type == IMAGETYPE_GIF || $this->image_type == IMAGETYPE_PNG ) {
		$current_transparent = imagecolortransparent($this->image);
		if($current_transparent != -1) {
			$transparent_color = imagecolorsforindex($this->image, $current_transparent);
			$current_transparent = imagecolorallocate($new_image, $transparent_color['red'], $transparent_color['green'], $transparent_color['blue']);
			imagefill($new_image, 0, 0, $current_transparent);
			imagecolortransparent($new_image, $current_transparent);
		} elseif( $this->image_type == IMAGETYPE_PNG) {
			imagealphablending($new_image, false);
			$color = imagecolorallocatealpha($new_image, 0, 0, 0, 127);
			imagefill($new_image, 0, 0, $color);
			imagesavealpha($new_image, true);
		}
	}
	imagecopyresampled($new_image, $this->image, 0, 0, 0, 0, $width, $height, $this->getWidth(), $this->getHeight());
	$this->image = $new_image;	
   }
 

   function oldresize($width,$height) {
      $new_image = imagecreatetruecolor($width, $height);
      imagecopyresampled($new_image, $this->image, 0, 0, 0, 0, $width, $height, $this->getWidth(), $this->getHeight());
      $this->image = $new_image;
   }      
 
}
?>
