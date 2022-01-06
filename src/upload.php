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
        $this -> _generateLowRatioPicture($path);
        list($width) = getimagesize($path);
        if($width < 720){
          $url = $url . "?w/$width";
        }
      }

      return [
        'code' => 0,
        'message' => 'success',
        'data' => [
          'url' => '/restful' . $url,
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
    
    $imageData = file_get_contents($file);
    $src = imagecreatefromstring($imageData);

    $dst = imagecreatetruecolor($newWidth, $newHeight);
    imagecopyresampled($dst, $src, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
    switch($suffix){
      case 'png':
        imagepng($dst, $file);
        break;
      case 'jpg':
      case 'jpeg':
        imagejpeg($dst, $file);
    }
  }

  private function _generateLowRatioPicture($file){
    list($width, $height) = getimagesize($file);
    $maxWidth = 12;
    if($width <= $maxWidth) return;
    $r = $height / $width;
    $newWidth = $maxWidth;
    $newHeight = $newWidth * $r;

    $src = null;
    $suffix = explode('.', $file)[2];

    $imageData = file_get_contents($file);
    $src = imagecreatefromstring($imageData);

    $dst = imagecreatetruecolor($newWidth, $newHeight);
    imagecopyresampled($dst, $src, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
    switch($suffix){
      case 'png':
        imagepng($dst, str_replace(".$suffix", "_low_ratio.$suffix", $file));
        break;
      case 'jpg':
      case 'jpeg':
        imagejpeg($dst, str_replace(".$suffix", "_low_ratio.$suffix", $file));
    }
  }

  private function _handleClearCache(){
    $backupDir = "./uploads_clear_backup";
    if(!is_dir($backupDir)){
      mkdir($backupDir, 0777, true);
    }

    $noteList = $this -> _noteLib -> getAllNoteContent();
    $re = '/\!\[.*\]\((.*)\)/';
    $matchesArr = [];

    foreach($noteList as $noteKey => $note){
      if(preg_match_all($re, $note['note_content'], $matches)){
        foreach($matches[1] as $matchKey => $matchValue){

          //自动修改图片路径前缀并将笔记更新状态记为已更新
          // if(substr($matchValue, 0, 8) === 'https://'){
          //   $noteList[$noteKey]['note_content'] = str_replace($matchValue, "/restful" . "/" . substr($matchValue, 25), $noteList[$noteKey]['note_content']);
          //   $matchValue = "/restful" . "/" . substr($matchValue, 25);
          // }
          // if(substr($matchValue, 0, 8) === '/uploads'){
          //   $noteList[$noteKey]['note_content'] = str_replace($matchValue, "/restful" . $matchValue, $noteList[$noteKey]['note_content']);
          //   $matchValue = "/restful" . $matchValue;
          // }
          // if(substr($matchValue, 0, 16) === '/restful/restful'){
          //   $noteList[$noteKey]['note_content'] = str_replace($matchValue, substr($matchValue, 8), $noteList[$noteKey]['note_content']);
          //   $matchValue = substr($matchValue, 8);
          // }
          // $this -> _noteLib -> updateNoteContentAndState($noteList[$noteKey]['note_id'], $noteList[$noteKey]['note_content']);

          // 生成笔记用到的所有文件的路径数组
          if(!in_array($matchValue, $matchesArr)){
            $suffix = explode('.', $matchValue)[1];
            $path = '.' . substr($matchValue, 8);
            $matchesArr[] = $path;
            $matchesArr[] = str_replace(".$suffix", "_low_ratio.$suffix", $path);
          }
        }
      }
    }

    // 获取服务器存储的所有已上传文件的列表
    $allFileList = $this -> _getDirFileList('./uploads');

    // 检查分辨率如果已上传文件列表中路径没有在笔记中用到则删除
    foreach($allFileList as $key => $value){
      $flag = true;
      foreach($matchesArr as $matcheKey => $matcheValue){
        if(substr_count($matcheValue, $value)){
          $flag = false;
          $this -> _limitPictureSize($value);
          $this -> _generateLowRatioPicture($value);
        }
      }
      if($flag){
        $tempArr = explode('/', $value);
        $fileName = $tempArr[count($tempArr) - 1];
        copy($value, "$backupDir/$fileName");
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
      if(is_dir("$directory/$file") && $file !== '.' && $file !== '..' && $file !== '.DS_Store'){
        $this -> _getDirFileList("$directory/$file");
      }else{
        if($file !== '.' && $file !== '..' && $file !== '.DS_Store'){
          $array[] = "$directory/$file";
        }
      }
    }

    return $array;
  }
}

