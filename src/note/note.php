<?php
  class Note {

    private $_noteLib;
    private $_categoryLib;
    private $_wxsdk;
    private $_upload;

    public function __construct(NoteLib $noteLib, CategoryLib $categoryLib, WXSDK $wxsdk, Upload $upload){
      $this -> _noteLib = $noteLib;
      $this -> _categoryLib = $categoryLib;
      $this -> _wxsdk = $wxsdk;
      $this -> _upload = $upload;
    }

    public function handleNote(){
      $requestMethod = $_SERVER['REQUEST_METHOD'];
      $path = $_SERVER['PATH_INFO'];
      $params = explode('/', $path);
      switch($requestMethod){
        case 'POST':
          switch($params[2]){
            // 后台
            case 'add_note':
              return $this -> _handleAddNote();
            case 'move_note':
              return $this -> _handleMoveNote();
            case 'save_note':
              return $this -> _handleSaveNote();
            case 'publish_note':
              return $this -> _handlePublishNote();
            default:
              throw new Exception('请求的资源不存在', 404); 
          }
        case 'PUT':
          switch($params[2]){
            // 后台
            case 'recovery_note':
              return $this -> _handleRecoveryNote();
            default:
              throw new Exception('请求的资源不存在', 404); 
          }
        case 'GET':
          switch($params[2]){
            // 后台
            case 'get_note_content':
              return $this -> _handleGetNoteContent();
            case 'get_note_history_list':
              return $this -> _handleGetNoteHistoryList();
            case 'get_note_history_version':
              return $this -> _handleGetNoteHistoryVersion();
            case 'get_category_note':
              return $this -> _handleGetCategoryNote();
            case 'get_files_info':
              return $this -> _handleNoteFiles(false);
            // 前台
            case 'get_published_note_list':
              return $this -> _handleGetPublishedNoteList();
            case 'get_note':
              return $this -> _handleGetNote();
            case 'get_wx_config':
              return $this -> _handleGetWxConfig();
            default:
              throw new Exception('请求的资源不存在', 404);
          }
        case 'DELETE':
          // 后台
          switch($params[2]){
            case 'delete_note':
              return $this -> _handleDeleteNote();
            case 'clear_files':
              return $this -> _handleNoteFiles(true);
            default:
              throw new Exception('请求的资源不存在', 404);
          }
        default:
          throw new Exception('请求方法不被允许', 405);
      }
    }

    // 后台
    private function _handlePublishNote(){
      $raw = file_get_contents('php://input');
      $body = json_decode($raw, true);

      if(!$body['status'] && $body['status'] !== 0){
        throw new Exception('参数错误', ErrorCode::INVALID_PARAMS);
      }

      if(!$body['note_id']){
        throw new Exception('参数错误', ErrorCode::INVALID_PARAMS);
      }
      
      $this -> _noteLib -> publishNote($body['note_id'], $body['status']);
      return [
        'code' => 0,
        'message' => 'success',
      ];  
    }

    private function _handleAddNote(){
      $raw = file_get_contents('php://input');
      $body = json_decode($raw, true);

      if(!$body['category_id']){
        throw new Exception('参数错误', ErrorCode::INVALID_PARAMS);
      }

      $isCategoryExist = $this -> _categoryLib -> getCategoryInfo($body['category_id']);

      if(!$isCategoryExist){
        throw new Exception('记录不存在', ErrorCode::RECORD_NOT_FOUND);
      }
      
      $noteTitle = date('Y-m-d');
      $res = $this -> _noteLib -> addNote($noteTitle, $body['category_id']);
      return [
        'code' => 0,
        'message' => 'success',
        'data' => $res
      ];
    }
     
    private function _handleMoveNote(){
      $raw = file_get_contents('php://input');
      $body = json_decode($raw, true);

      if(!$body['category_id']){
        throw new Exception('请选择分类', ErrorCode::NO_CATEGORY_ID);
      } 

      if(!$body['note_id']){
        throw new Exception('参数错误', ErrorCode::INVALID_PARAMS);
      }

      $this -> _noteLib -> moveNote($body['category_id'], $body['note_id']);
      return [
        'code' => 0,
        'message' => 'success'
      ];
    }

    private function _handleGetNoteHistoryList(){
      $params = $_GET;

      if(!$params['note_id']){
        throw new Exception('参数错误', ErrorCode::INVALID_PARAMS);
      }

      $note = $this -> _noteLib -> getNote($params['note_id'], false);

      if(!$note){
        throw new Exception('记录不存在', ErrorCode::RECORD_NOT_FOUND);
      }

      $res = $this -> _noteLib -> getNoteHistoryList($params['note_id']);
     
      return [
        'code' => 0,
        'message' => 'success',
        'data' => [
         'note_history_list' => $res,
        ],
      ];
    }

    private function _handleGetNoteHistoryVersion(){
      $params = $_GET;

      if(!$params['id']){
        throw new Exception('参数错误', ErrorCode::INVALID_PARAMS);
      }

      $res = $this -> _noteLib -> getNoteHistoryVersion($params['id']);

      if(!$res){
        throw new Exception('记录不存在', ErrorCode::RECORD_NOT_FOUND);
      }

      return [
        'code' => 0,
        'message' => 'success',
        'data' => $res
      ];
    }

    private function _handleRecoveryNote(){
      $raw = file_get_contents('php://input');
      $body = json_decode($raw, true);

      if(!$body['id']){
        throw new Exception('参数错误', ErrorCode::INVALID_PARAMS);
      }

      $res = $this -> _noteLib -> getNoteHistoryVersion($body['id']);

      if(!$res){
        throw new Exception('记录不存在', ErrorCode::RECORD_NOT_FOUND);
      }

      $note = $this -> _noteLib -> getNote($res['note_id'], false);

      if(!$note){
        throw new Exception('记录不存在', ErrorCode::RECORD_NOT_FOUND);
      }

      $this -> _noteLib -> saveNote($res['note_id'], $res['note_title'], $res['note_content'], false);
      return [
        'code' => 0,
        'message' => 'success',
        'data' => [
          'note_id' => $res['note_id']
        ]
      ];
    }

    private function _handleGetCategoryNote(){
      $params = $_GET;

      if(!$params['category_id']){
        throw new Exception('参数错误', ErrorCode::INVALID_PARAMS);
      }
      
      $isCategoryExist = $this -> _categoryLib -> getCategoryInfo($params['category_id']);

      if(!$isCategoryExist){
        throw new Exception('记录不存在', ErrorCode::RECORD_NOT_FOUND);
      }

      $categoryNote = $this -> _noteLib -> getCategoryNote($params['category_id']);
     
      return [
        'code' => 0,
        'message' => 'success',
        'data' => [
         'category_note_list' => $categoryNote,
        ],
      ];
    }

    private function _handleDeleteNote(){
      $raw = file_get_contents('php://input');
      $body = json_decode($raw, true);

      if(!$body['note_id']){
        throw new Exception('参数错误', ErrorCode::INVALID_PARAMS);
      }

      $this -> _noteLib -> deleteNote($body['note_id']);
      return [
        'code' => 0,
        'message' => 'success'
      ];
    }

    private function _handleGetNoteContent(){
      $params = $_GET;

      if(!$params['note_id']){
        throw new Exception('参数错误', ErrorCode::INVALID_PARAMS);
      }

      $content = $this -> _noteLib -> getNoteContent($params['note_id']);

      if(!$content){
        throw new Exception('记录不存在', ErrorCode::RECORD_NOT_FOUND);
      }

      return [
        'code' => 0,
        'message' => 'success',
        'data' => $content
      ];
    }

    private function _handleSaveNote(){
      $raw = file_get_contents('php://input');
      $body = json_decode($raw, true);

      if(!$body['note_id']){
        throw new Exception('参数错误', ErrorCode::INVALID_PARAMS);
      }

      $this -> _noteLib -> saveNote($body['note_id'], $body['note_title'], $body['note_content']);
      return [
        'code' => 0,
        'message' => 'success'
      ];
    }
    
    // 前台
    private function _handleGetPublishedNoteList(){
      $noteList = $this -> _noteLib -> getPublishedNoteList();
      return [
        'code' => 0,
        'message' => 'success',
        'data' => [
          'note_list' => $noteList,
        ],
      ];
    }

    private function _handleGetNote(){
      $params = $_GET;

      if(!$params['note_id']){
        throw new Exception('参数错误', ErrorCode::INVALID_PARAMS);
      }
      
      $res = $this -> _noteLib -> getNote($params['note_id']);

      if(!$res){
        throw new Exception('记录不存在', ErrorCode::RECORD_NOT_FOUND);
      }

      return [
        'code' => 0,
        'message' => 'success',
        'data' => $res
      ];
    }

    private function _handleGetWxConfig(){
      $params = $_GET;
      $signPackage = $this -> _wxsdk -> getSignPackage($params['url']);
      return [
        'code' => 0,
        'message' => 'success',
        'data' => $signPackage,
      ]; 
    }

    private function _handleNoteFiles($isDelete){
      $re = '/\!\[.*?\]\((\S*) ?\S*\)|\[.*?\]: *\n?(\S*) ?\S*/';
      $backupDir = "./uploads_clear_backup";
      $imgDir = '/uploads/images';
      $noteList = $this -> _noteLib -> getAllNoteContent();
  
      $referImgsNum = 0;
      $referImgsList = [];
  
      foreach($noteList as $noteKey => $note){
        if(preg_match_all($re, $note['note_content'], $matches)){
          $matchesArr = array_unique(array_merge($matches[1], $matches[2]));
  
          foreach($matchesArr as $matchKey => $matchValue){
            if($matchValue){
              $suffix = explode('.', $matchValue)[1];
              $path = preg_replace('/^https?:\/\/.*\/restful|^\/restful/', '', $matchValue);
              
              $position = strpos($matchValue, $imgDir);
              if($position !== false){
                if(!in_array($path, $referImgsList)){
                  $referImgsList[] = $path;
                  $referImgsList[] = str_replace(".$suffix", "_low_ratio.$suffix", $path);
                  $referImgsNum += 2;
                }
              }
            }
          }
        }
      }
  
      $existingImgsList = $this -> _getDirFileList(".$imgDir");
      $existingImgsNum = count($existingImgsList);
      $existingImgsSize = 0;
  
      $confirmedImgsNum = 0;
      $confirmedImgsList = [];
  
      $deletedImgsNum = 0;
      $deletedImgsList = [];
      $deletedImgsSize = 0;
  
      foreach($existingImgsList as $key => $value){
        $flag = true;
        $existingImgsSize += filesize($value);
  
        foreach($referImgsList as $matcheKey => $matcheValue){
          if(substr_count(".$matcheValue", $value)){
            $confirmedImgsNum++;
            $confirmedImgsList[] = $value;
            $flag = false;
            break;
          }
        }
  
        if($flag){
          $deletedImgsNum++;
          $deletedImgsList[] = $value;
          $deletedImgsSize += filesize($value);
  
          if($isDelete){
            $tempArr = explode('/', $value);
            $fileName = $tempArr[count($tempArr) - 1];
            copy($value, "$backupDir/$fileName");
            unlink($value);
          }
        }else{
          // 需要统一处理一下所有已上传的图片时将这里的注释打开
          // if(!preg_match('/_low_ratio.+$/', $value)){
          //   $this -> _upload -> limitPictureSize($value);
          //   $this -> _upload -> generateLowRatioPicture($value);
          // }
        }
      }
  
      return [
        'code' => 0,
        'message' => 'success',
        'data' => [
          'refer_imgs_num' => $referImgsNum,
          // 'refer_imgs_list' => $referImgsList,
          'existing_imgs_num' => $existingImgsNum,
          // 'existing_imgs_list' => $existingImgsList,
          'existing_imgs_size'=> $existingImgsSize,
          'confirmed_imgs_num' => $confirmedImgsNum,
          // 'confirmed_imgs_list' => $confirmedImgsList,
          'deleted_imgs_num' => $deletedImgsNum,
          // 'deleted_imgs_list' => $deletedImgsList,
          'deleted_imgs_size' => $deletedImgsSize,
        ],
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
