<?php

class Upload {

  public function handleUpload(){
    $requestMethod = $_SERVER['REQUEST_METHOD'];
    switch ($requestMethod){
      case 'POST':
        return $this -> _handleUploadFile();
      default:
        throw new Exception("请求方法不被允许", 405);
    }
  }

  private function _handleUploadFile(){
    $key = $_POST['key'];

    if(!$key){
      throw new Exception('参数错误', ErrorCode::INVALID_PARAMS);
    }

    $params = explode('/', $key);
    $uploadDir = $params[0];
    $fileName = $params[1];

    if(!$uploadDir || !$fileName){
      throw new Exception('参数错误', ErrorCode::INVALID_PARAMS);
    }

    if($uploadDir === 'images'){
      if(!in_array(explode('.', $fileName)[1], ['jpg', 'jpeg', 'png'])){
        throw new Exception('不支持的图片格式，请选择jpg、jpeg或png文件', ErrorCode::INVALID_PICTURE_FORMAT);
      }
    }

    $rootDir = "./uploads/" . $uploadDir;
    if(!is_dir($rootDir)){
      mkdir($rootDir, 0777, true);
    }

    $path = $rootDir . '/' . $fileName;
    $url = '/restful' . substr($path, 1);

    if(move_uploaded_file($_FILES["file"]["tmp_name"], $path)){

      if($uploadDir == 'images'){
        $this -> _limitPictureSize($path);
        $this -> _generateLowRatioPicture($path);
        list($width) = getimagesize($path);
        $url = $url . "?w/$width";
      }

      return [
        'code' => 0,
        'message' => 'success',
        'data' => [
          'url' => $url,
        ],
      ];
    }else{
      throw new Exception('上传文件失败', ErrorCode::UPLOAD_FILE_FAILED);
    }
  }

  private function _limitPictureSize($file) {
    list($width, $height) = getimagesize($file);
    $newWidth = 0;
    $newHeight = 0;
    $maxWidth = 1440;
    if($width <= $maxWidth) return;
    $r = $height / $width;

    if($width > $maxWidth){
      $newWidth = $maxWidth;
      $newHeight = $newWidth * $r;
    }

    if($newWidth == 0){
      return;
    }

    $src = null;
    $suffix = explode('.', $file)[2];

    switch($suffix){
      case 'png':
        $imageData = file_get_contents($file);
        $src = imagecreatefromstring($imageData);
        $dst = imagecreatetruecolor($newWidth, $newHeight);
        $transparent = imagecolorallocatealpha($dst, 0, 0, 0, 127);
        imagefill($dst, 0, 0, $transparent);
        imagesavealpha($dst, true);
        imagealphablending($dst, false);
        imagecopyresampled($dst, $src, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
        imagepng($dst, $file);
        break;
      case 'jpg':
      case 'jpeg':
        $src = imagecreatefromjpeg($file);
        $dst = imagecreatetruecolor($newWidth, $newHeight);
        imagecopyresampled($dst, $src, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
        imagejpeg($dst, $file);
    }
  }

  private function _generateLowRatioPicture($file){
    list($width, $height) = getimagesize($file);
    $maxWidth = 12;
    if($width <= $maxWidth) return;

    $suffix = explode('.', $file)[2];
    $lowRatioPic = str_replace(".$suffix", "_low_ratio.$suffix", $file);
    if(file_exists($lowRatioPic)) return;
    
    $r = $height / $width;
    $newWidth = $maxWidth;
    $newHeight = $newWidth * $r;

    if($newHeight < 1){
      $newHeight = 1;
    }

    switch($suffix){
      case 'png':
        $imageData = file_get_contents($file);
        $src = imagecreatefromstring($imageData);
        $dst = imagecreatetruecolor($newWidth, $newHeight);
        $transparent = imagecolorallocatealpha($dst, 0, 0, 0, 127);
        imagefill($dst, 0, 0, $transparent);
        imagesavealpha($dst, true);
        imagealphablending($dst, false);
        imagecopyresampled($dst, $src, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
        imagepng($dst, $lowRatioPic);
        break;
      case 'jpg':
      case 'jpeg':
        $src = imagecreatefromjpeg($file);
        $dst = imagecreatetruecolor($newWidth, $newHeight);
        imagecopyresampled($dst, $src, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
        imagejpeg($dst, $lowRatioPic);
    }
  }
}
