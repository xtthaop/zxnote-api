<?php

class Upload {
  private $_noteLib;

  public function __construct(NoteLib $noteLib){
    $this -> _noteLib = $noteLib;
  }

  public function handleUpload(){
    $requestMethod = $_SERVER['REQUEST_METHOD'];
    switch ($requestMethod){
      case 'POST':
        return $this -> _handleUploadFile();
      case 'DELETE':
        return $this -> _handleClearCache();
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

    if(!in_array(explode('.', $fileName)[1], ['jpg', 'jpeg', 'png'])){
      throw new Exception('不支持的图片格式', ErrorCode::INVALID_PICTURE_FORMAT);
    }

    $rootDir = "./uploads/" . $uploadDir;
    if(!is_dir($rootDir)){
      mkdir($rootDir, 0777, true);
    }

    $path = $rootDir . '/' . $fileName;
    $url = substr($path, 1);
    
    if(move_uploaded_file($_FILES["file"]["tmp_name"], $path)){

      if($uploadDir == 'images'){
        $this -> _limitPictureSize($path);
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
    $maxWidth = 720;
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
        $src = imagecreatefrompng($file);
        break;
      case 'jpg':
      case 'jpeg':
        $src = imagecreatefromjpeg($file);
    }

    $dst = imagecreatetruecolor($newWidth, $newHeight);
    imagecopyresampled($dst, $src, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
    imagepng($dst, $file);
  }

  private function _handleClearCache(){
    $content = $this -> _noteLib -> getAllNoteContent();
    $re = '/\!\[.*\]\((.*)\)/';
    $matchesArr = [];
    foreach($content as $key => $value){
      if(preg_match_all($re, $value['note_content'], $matches)){
        foreach($matches[1] as $key => $value){
          if(!in_array($value, $matchesArr)){
            $matchesArr[] = '.' . $value;
          }
        }
      }
    }

    $allFileList = $this -> _getDirFileList('./uploads');

    foreach($allFileList as $key => $value){
      if(in_array($value, $matchesArr)){
        $this -> _limitPictureSize($value);
      }else{
        unlink($value);
      }
    }

    return [
      'code' => 0,
      'message' => 'success',
    ];
  }

  private function _getDirFileList($directory){
    static $array = [];

    $dir = dir($directory);
    while($file = $dir -> read()){
      if(is_dir("$directory/$file") && $file !== '.' && $file !== '..'){
        $this -> _getDirFileList("$directory/$file");
      }else{
        if($file !== '.' && $file !== '..'){
          $array[] = "$directory/$file";
        }
      }
    }

    return $array;
  }
}

