<?php
  class NoteImg {
    private $_upload;

    public function __construct(Upload $upload){
      $this -> _upload = $upload;
    }

    public function handleNoteImg(){
      $requestMethod = $_SERVER['REQUEST_METHOD'];
      $path = $_SERVER['PATH_INFO'];
      $params = explode('/', $path);
      switch($requestMethod){
        case 'GET':
          switch($params[2]){
            case 'get_backup_imgs':
              return $this -> _handleGetBackupImgs();
            default:
              throw new Exception('请求的资源不存在', 404); 
          }
        case 'PUT':
          switch($params[2]){
            case 'restore_note_img':
              return $this -> _handleRestoreOrDeleteImg();
            case 'regenerate_img':
              return $this -> _handleRegenerateImg();
            default:
              throw new Exception('请求的资源不存在', 404); 
          }
        case 'DELETE':
          switch($params[2]){
            case 'completely_delete_img':
              return $this -> _handleRestoreOrDeleteImg(true);
            default:
              throw new Exception('请求的资源不存在', 404); 
          }
        default:
          throw new Exception('请求方法不被允许', 405);
      }
    }

    private function _handleGetBackupImgs(){
      $backupDir = "./uploads_clear_backup/images";
      $backupImgs = $this -> _getDirFileList($backupDir);
      arsort($backupImgs);
      $imgList = [];

      foreach($backupImgs as $img => $time){
        if(!strpos($img, 'low_ratio')){
          $value = '/restful' . substr($img, 1);
          $imgList[] = [
            'url' => $value,
            'update_time' => $time
          ];
        }
      }

      return [
        'code' => 0,
        'message' => 'success',
        'data' => $imgList
      ];
    }

    private function _getDirFileList($directory){
      static $array = [];
  
      $dir = dir($directory);
      while($dir && $file = $dir -> read()){
        if(is_dir("$directory/$file") && $file !== '.' && $file !== '..' && $file !== '.DS_Store'){
          $this -> _getDirFileList("$directory/$file");
        }else{
          if($file !== '.' && $file !== '..' && $file !== '.DS_Store'){
            $filePath = "$directory/$file";
            $array[$filePath] = filemtime($filePath);
          }
        }
      }
  
      return $array;
    }

    private function _handleRestoreOrDeleteImg($isDelete = false){
      $raw = file_get_contents('php://input');
      $body = json_decode($raw, true);

      if(!$body['img_path']){
        throw new Exception('参数错误', ErrorCode::INVALID_PARAMS);
      }

      $array = [];
      $imgDir = './uploads/images';

      $imgPath = str_replace("/restful", "", $body['img_path']);
      $array[] = '.' . $imgPath;

      $suffix = explode('.', $imPath)[1];
      $lowRatioImgPath = str_replace(".$suffix", "_low_ratio.$suffix", $imgPath);
      $array[] = '.' . $lowRatioImgPath;

      foreach($array as $value){
        if($isDelete){
          unlink($value);
          continue;
        }

        $tempArr = explode('/', $value);
        $fileName = $tempArr[count($tempArr) - 1];
        if(copy($value, "$imgDir/$fileName")){
          unlink($value);
        }
      }

      return [
        'code' => 0,
        'message' => 'success'
      ];
    }

    public function handleDeleteAllBackupImg(){
      $backupDir = "./uploads_clear_backup/images";
      $backupImgs = $this -> _getDirFileList($backupDir);
      $totalFileSize = 0;
      foreach($backupImgs as $img => $time){
        $totalFileSize += filesize($img);
        unlink($img);
      }
      return [
        'code' => 0,
        'message' => 'success',
        'data' => [
          'num' => count($backupImgs),
          'size' => $totalFileSize,
        ]
      ];
    }

    private function _handleRegenerateImg(){
      $existingDir = './uploads/images';
      $backupDir = "./uploads_clear_backup/images";
      $this -> _getDirFileList($existingDir);
      $allImgs = $this -> _getDirFileList($backupDir);
      foreach($allImgs as $img => $time){
        if(!preg_match('/_low_ratio\.\w+$/', $img)){
          $this -> _upload -> limitPictureSize($img, true);
          $this -> _upload -> generateLowRatioPicture($img);
        }
      }
      return [
        'code' => 0,
        'message' => 'success',
      ];
    }
  }