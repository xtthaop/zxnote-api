<?php
  class NoteImg {
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
          $imgList[] = $value;
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
      while($file = $dir -> read()){
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
  }