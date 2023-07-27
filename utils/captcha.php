<?php

Class Captcha {
  
  private function _makeJigsaw(){
    $jigsawArray = array();
    $pic_w = 40;
    $pic_h = 40;
    $r = 8;
    $dr = $r * $r;

    $circular1_x = ($pic_w - $r)/2;
    $circular1_y = $r;

    $circular2_x = $pic_w - $r;
    $circular2_y = ($pic_h - $r)/2 + $r;

    $circular3_x = 0;
    $circular3_y = ($pic_h - $r)/2 + $r;

    for($i = 0; $i < $pic_h; $i++){
      for($j = 0; $j < $pic_w; $j++){
        $d1 = pow($j - $circular1_x, 2) + pow($i - $circular1_y, 2);
        $d2 = pow($j - $circular2_x, 2) + pow($i - $circular2_y, 2);
        $d3 = pow($j - $circular3_x, 2) + pow($i - $circular3_y, 2);

        if(($i >= $r && $j <= $pic_w - $r && $d3 >= $dr) || $d1 <= $dr || $d2 <= $dr){
          $jigsawArray[$i][$j] = 1;
        }else{
          $jigsawArray[$i][$j] = 0;
        }
      }
    }

    return [
      'pic_w' => $pic_w,
      'pic_h' => $pic_h,
      'jigsawArray' => $jigsawArray
    ];
  }

  public function makeCaptcha(){
    $jigsaw = $this -> _makeJigsaw();
    $jigsawArray = $jigsaw['jigsawArray'];
    $pic_w = $jigsaw['pic_w'];
    $pic_h = $jigsaw['pic_h'];

    $srcFile = './assets/images/captcha/bp' . mt_rand(1, 3) . '.jpeg';
    $src_w = 320;
    $src_h = 140;
    $src_im = imagecreatefromjpeg($srcFile);

    $dst_im = imagecreatetruecolor($src_w, $src_h);
    $black = imagecolorallocate($dst_im, 0, 0, 0);
    imagefill($dst_im, 0, 0, $black);
    imagecopymerge($dst_im, $src_im, 0, 0, 0, 0, $src_w, $src_h, 100);

    $jigsaw_im = imagecreatetruecolor($pic_w, $pic_h);
    imagealphablending($jigsaw_im, false);
    imagesavealpha($jigsaw_im, true);
    $jigsaw_bg = imagecolorallocatealpha($jigsaw_im, 0, 0, 0, 127);
    imagefill($jigsaw_im, 0, 0, $jigsaw_bg);

    $src_x = mt_rand($pic_w * 2, $src_w - $pic_w);
    $src_y = mt_rand(0, $src_h - $pic_h);

    $black_a = imagecolorallocatealpha($dst_im, 0, 0, 0, 60);
    for($i = 0; $i < $pic_h; $i++){
      for($j = 0; $j < $pic_w; $j++){
        if($jigsawArray[$i][$j] == 1){
          $rgb = imagecolorat($dst_im, $src_x + $j, $src_y + $i);
          imagesetpixel($jigsaw_im, $j, $i, $rgb);
          imagesetpixel($dst_im, $src_x + $j, $src_y + $i, $black_a);
        }
      }
    }

    if(!is_dir('./public/captcha')){
      mkdir('./public/captcha', 0777);
    }

    imagepng($dst_im, './public/captcha/dst.png');
    imagepng($jigsaw_im, './public/captcha/jigsaw.png');
    imagedestroy($src_im);
    imagedestroy($dst_im);
    imagedestroy($jigsaw_im);

    return [
      'y' => $src_y,
      'x' => $src_x,
      'dst_img' => '/restful/public/captcha/dst.png?' .  (int)(microtime(true)*1000),
      'jigsaw_img' => '/restful/public/captcha/jigsaw.png?' . (int)(microtime(true)*1000),
    ];
  }
}

