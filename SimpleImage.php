<?php
    class SimpleImage {
       
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
               imagealphablending($this->image, false);
               imagesavealpha($this->image, true);               
            } else {
               $this->image_type = false;
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
         
         function resize($width,$height) {
            $new_image = imagecreatetruecolor($width, $height);
            imagealphablending($new_image, false);
            imagesavealpha($new_image, true);
            imagecopyresampled($new_image, $this->image, 0, 0, 0, 0, $width, $height, $this->getWidth(), $this->getHeight());
            $this->image = $new_image;
         }
         function cover ($width,$height) {
            /* Заполнить область */
            $w = $this->getWidth();
            if ($width != $w) {
              $this->resizeToWidth($width);
            }
            $h = $this->getHeight();
            if ($height > $h) {
              $this->resizeToHeight($height);
            }
            $this->wrapInTo ($width,$height);
         }
         
         function wrapInTo ($width,$height) {
            /* Обрезает все что не вмещается в область */
            $new_image = imagecreatetruecolor($width, $height);
            $w = $this->getWidth();
            $h = $this->getHeight();
            if ($width > $w) {
              $dst_x = round(($width - $w) / 2);
              $src_x = 0;
              $dst_w = $w;
              $src_w = $w;
            } else {
              $dst_x = 0;
              $src_x = round(($w - $width) / 2);
              $dst_w = $width;
              $src_w = $width;
            }
            if ($height > $h) {
              $dst_y = round(($height - $h) / 2);
              $src_y = 0;
              $dst_h = $h;
              $src_h = $h;
            } else {
              $dst_y = 0;
              $src_y = round(($h - $height) / 2);
              $dst_h = $height;
              $src_h = $height;
            }
            imagealphablending($new_image, false);
            imagesavealpha($new_image, true);
            $transparentindex = imagecolorallocatealpha($new_image, 255, 255, 255, 127);
            imagefill($new_image, 0, 0, $transparentindex);
            imagecopyresampled($new_image, $this->image, $dst_x, $dst_y, $src_x, $src_y, $dst_w, $dst_h, $src_w, $src_h);
            $this->image = $new_image;
         }
         
         function resizeInTo($width,$height) {
            /* Масштабюировать чтобы изображение влезло в рамки */
            $ratiow = $width / $this->getWidth()*100;
            $ratioh = $height / $this->getHeight()*100;
            $ratio = min($ratiow, $ratioh);
            $this->scale($ratio);
         }
         
         function smallTo($width,$height) {
            if (($this->getWidth() > $width) or ($this->getHeight() > $height)) {
              $this->resizeInTo($width,$height)
            }
         }
         
         function crop($x1,$y1,$x2,$y2) {
            /* Вырезать кусок */
            $w = abs($x2 - $x1);
            $h = abs($y2 - $y1);
            $x = min($x1,$x2);
            $y = min($y1,$y2);
           	$new_image = imagecreatetruecolor($w, $h);
            imagealphablending($new_image, false);
            imagesavealpha($new_image, true);
            imagecopy($new_image, $this->image, 0, 0, $x, $y, $w, $h);
            $this->image = $new_image;
         }
      }
?>