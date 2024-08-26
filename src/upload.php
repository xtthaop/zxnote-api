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
        $this -> limitPictureSize($path);
        $this -> generateLowRatioPicture($path);
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

  public function limitPictureSize($file, $regenerate = false) {
    list($width, $height) = getimagesize($file);

    $maxWidth = 720;
    $newWidth = $width;

    if(!$regenerate && $width <= $maxWidth){
      return;
    }else{
      if($width > $maxWidth) $newWidth = $maxWidth;
    }

    $r = $height / $width;
    $newHeight = (int)round($newWidth * $r);

    if($newHeight < 1){
      $newHeight = 1;
    }

    $suffix = explode('.', $file)[2];

    switch($suffix){
      case 'png':
        $this -> _generatePNG($file, $file, $newWidth, $newHeight, $width, $height);
        break;
      case 'jpg':
      case 'jpeg':
        $this -> _generateJPG($file, $file, $newWidth, $newHeight, $width, $height);
    }
  }

  private function _generatePNG($srcFilePath, $dstFilePath, $newWidth, $newHeight, $width, $height){
    $imageData = file_get_contents($srcFilePath);
    $src = imagecreatefromstring($imageData);
    $dst = imagecreatetruecolor($newWidth, $newHeight);
    $transparent = imagecolorallocatealpha($dst, 0, 0, 0, 127);
    imagefill($dst, 0, 0, $transparent);
    imagesavealpha($dst, true);
    imagealphablending($dst, false);
    imagecopyresampled($dst, $src, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
    imagepng($dst, $dstFilePath);
  }

  private function _generateJPG($srcFilePath, $dstFilePath, $newWidth, $newHeight, $width, $height){
    $imageData = file_get_contents($srcFilePath);
    $src = imagecreatefromstring($imageData);
    $dst = imagecreatetruecolor($newWidth, $newHeight);
    imagecopyresampled($dst, $src, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
    imagejpeg($dst, $dstFilePath);
  }

  public function generateLowRatioPicture($file){
    list($width, $height) = getimagesize($file);
    $maxWidth = 12;

    if($width <= $maxWidth) return;

    $suffix = explode('.', $file)[2];
    $lowRatioPic = str_replace(".$suffix", "_low_ratio.$suffix", $file);
    
    $r = $height / $width;
    $newWidth = $maxWidth;
    $newHeight = (int)round($newWidth * $r);

    if($newHeight < 1){
      $newHeight = 1;
    }

    switch($suffix){
      case 'png':
        $this -> _generatePNG($file, $lowRatioPic, $newWidth, $newHeight, $width, $height);
        break;
      case 'jpg':
      case 'jpeg':
        $this -> _generateJPG($file, $lowRatioPic, $newWidth, $newHeight, $width, $height);
    }
  }
}
