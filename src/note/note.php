<?php
  class Note {

    private $_noteLib;
    private $_categoryLib;
    private $_wxsdk;
    private $_upload;
    private $_noteImg;

    public function __construct(NoteLib $noteLib, CategoryLib $categoryLib, WXSDK $wxsdk, Upload $upload, NoteImg $noteImg){
      $this -> _noteLib = $noteLib;
      $this -> _categoryLib = $categoryLib;
      $this -> _wxsdk = $wxsdk;
      $this -> _upload = $upload;
      $this -> _noteImg = $noteImg;
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
            case 'restore_note':
              return $this -> _handleRestoreNote();
            default:
              throw new Exception('请求的资源不存在', 404); 
          }
        case 'GET':
          switch($params[2]){
            // 后台
            case 'get_category_note':
              return $this -> _handleGetCategoryNote();
            case 'get_note':
              return $this -> _handleGetNote();
            case 'get_note_history_list':
              return $this -> _handleGetNoteHistoryList();
            case 'get_note_history_version':
              return $this -> _handleGetNoteHistoryVersion();
            case 'get_note_files_info':
              return $this -> _handleNoteFiles(false);
            case 'get_deleted_note_list':
              return $this -> _handleGetDeletedNoteList();
            case 'get_deleted_note_content':
              return $this -> _handleGetDeletedNoteContent();
            // 前台
            case 'get_published_note_list':
              return $this -> _handleGetPublishedNoteList();
            // TODO: 接口名字修改
            case 'get_note':
              return $this -> _handleGetPublishNote();
            case 'get_wx_config':
              return $this -> _handleGetWxConfig();
            default:
              throw new Exception('请求的资源不存在', 404);
          }
        case 'DELETE':
          // 后台
          switch($params[2]){
            case 'soft_delete_note':
              return $this -> _handleSoftDeleteNote();
            case 'clear_space':
              return $this -> _handleClearSpace();
            case 'completely_delete_note':
              return $this -> _handleCompletelyDeleteNote();
            default:
              throw new Exception('请求的资源不存在', 404);
          }
        default:
          throw new Exception('请求方法不被允许', 405);
      }
    }

    // 后台
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

    private function _handleSoftDeleteNote(){
      $raw = file_get_contents('php://input');
      $body = json_decode($raw, true);

      if(!$body['note_id']){
        throw new Exception('参数错误', ErrorCode::INVALID_PARAMS);
      }

      $this -> _noteLib -> softDeleteNote($body['note_id']);
      return [
        'code' => 0,
        'message' => 'success'
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

    private function _handleGetNote(){
      $params = $_GET;

      if(!$params['note_id']){
        throw new Exception('参数错误', ErrorCode::INVALID_PARAMS);
      }

      $content = $this -> _noteLib -> getNote($params['note_id']);

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

    private function _handleGetNoteHistoryList(){
      $params = $_GET;

      if(!$params['note_id']){
        throw new Exception('参数错误', ErrorCode::INVALID_PARAMS);
      }

      $note = $this -> _noteLib -> getNoteBasicInfo($params['note_id']);

      if(!$note){
        throw new Exception('记录不存在', ErrorCode::RECORD_NOT_FOUND);
      }

      $res = $this -> _noteLib -> getNoteHistoryList($params['note_id']);
     
      return [
        'code' => 0,
        'message' => 'success',
        'data' => [
         'note_history_list' => $res,
         'category_id' => $note['category_id']
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

      $note = $this -> _noteLib -> getNoteBasicInfo($res['note_id']);

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

    private function _handleNoteFiles($isDelete){
      $re = '/\!\[.*\]\((\S+) *\n? *\S* *\n? *\)|\[.+\]: *\n? *(\S+) *\n? *\S*/';
      $backupDir = "./uploads_clear_backup/images";
      if(!is_dir($backupDir)){
        mkdir($backupDir, 0777, true);
      }
      $imgPath = '/uploads/images';
      $imgDir = ".$imgPath";
      if(!is_dir($imgDir)){
        mkdir($imgDir, 0777, true);
      }
      $noteList = $this -> _noteLib -> getAllNoteContent();
  
      $referImgsNum = 0;
      $referImgsList = [];
  
      foreach($noteList as $noteKey => $note){
        if(preg_match_all($re, $note['note_content'], $matches)){
          $matchesArr = array_unique(array_merge($matches[1], $matches[2]));
  
          foreach($matchesArr as $matchKey => $matchValue){
            if($matchValue){
              $position = strpos($matchValue, $imgPath);
              if($position !== false){
                $suffix = explode('.', $matchValue)[1];
                $path = preg_replace('/^https?:\/\/.*\/restful|^\/restful/', '', $matchValue);
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
  
      $existingImgsList = $this -> _getDirFileList($imgDir);
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
            if(copy($value, "$backupDir/$fileName")){
              unlink($value);
            }
          }
        }
      }

      $data;
      if($isDelete){
        $data = [
          'num' => $deletedImgsNum,
          'size' => $deletedImgsSize,
        ];
      }else{
        $data = [
          'refer' => [
            'num' => $referImgsNum,
            'list' => $referImgsList,
            'desc' => '笔记已引用的图片',
          ],
          'existing' => [
            'num' => $existingImgsNum,
            'list' => $existingImgsList,
            'size' => $existingImgsSize,
            'desc' => '现存所有的图片',
          ],
          'confirmed' => [
            'num' => $confirmedImgsNum,
            'list' => $confirmedImgsList,
            'desc' => '现存被笔记引用的图片',
          ],
          'deleted' => [
            'num' => $deletedImgsNum,
            'list' => $deletedImgsList,
            'size' => $deletedImgsSize,
            'desc' => '需要删除的图片',
          ],
        ];
      }
  
      return [
        'code' => 0,
        'message' => 'success',
        'data' => $data,
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

    private function _handleClearSpace(){
      $raw = file_get_contents('php://input');
      $body = json_decode($raw, true);

      if(!$body['checked']){
        throw new Exception('参数错误', ErrorCode::INVALID_PARAMS);
      }

      $deletedHistoryNotesNum = 0;
      $softDeletedImgsNum = 0;
      $softDeletedImgsSize = 0;
      $deletedNotesNum = 0;
      $deletedImgsNum = 0;
      $deletedImgsSize = 0;

      if(in_array('history', $body['checked'])){
        if(!$body['time']){
          throw new Exception('参数错误', ErrorCode::INVALID_PARAMS);
        }

        $deletedHistoryNotesNum = $this -> _noteLib -> completelyDeleteNoteHistory($body['time']);
      }

      if(in_array('img', $body['checked'])){
        $res = $this -> _handleNoteFiles(true);
        $softDeletedImgsNum = $res['data']['num'];
        $softDeletedImgsSize = $res['data']['size'];
      }

      if(in_array('recycle', $body['checked'])){
        $deletedNotesNum = $this -> _noteLib -> completelyDeleteNote();
        $res = $this -> _noteImg -> handleDeleteAllBackupImg();
        $deletedImgsNum = $res['data']['num'];
        $deletedImgsSize = $res['data']['size'];
      }

      return [
        'code' => 0,
        'message' => 'success',
        'data' => [
          'deleted_history_notes_num' => $deletedHistoryNotesNum,
          'soft_deleted_imgs_num' => $softDeletedImgsNum,
          'soft_deleted_imgs_size' => $softDeletedImgsSize,
          'deleted_notes_num' => $deletedNotesNum,
          'deleted_imgs_num' => $deletedImgsNum,
          'deleted_imgs_size' => $deletedImgsSize,
        ],
      ];
    }

    private function _handleGetDeletedNoteList(){
      $noteList = $this -> _noteLib -> getDeletedNoteList();
      return [
        'code' => 0,
        'message' => 'success',
        'data' => [
          'note_list' => $noteList,
        ],
      ];
    }

    private function _handleGetDeletedNoteContent(){
      $params = $_GET;

      if(!$params['note_id']){
        throw new Exception('参数错误', ErrorCode::INVALID_PARAMS);
      }

      $res = $this -> _noteLib -> getNote($params['note_id'], true);

      if(!$res){
        throw new Exception('记录不存在', ErrorCode::RECORD_NOT_FOUND);
      }

      return [
        'code' => 0,
        'message' => 'success',
        'data' => $res
      ];
    }

    private function _handleRestoreNote(){
      $raw = file_get_contents('php://input');
      $body = json_decode($raw, true);

      if(!$body['note_id']){
        throw new Exception('参数错误', ErrorCode::INVALID_PARAMS);
      }

      $note = $this -> _noteLib -> getNoteBasicInfo($body['note_id'], true);

      if(!$note){
        throw new Exception('记录不存在', ErrorCode::RECORD_NOT_FOUND);
      }

      $category = $this -> _categoryLib -> getCategoryInfo($note['category_id'], true);

      if(!$category){
        throw new Exception('记录不存在', ErrorCode::RECORD_NOT_FOUND);
      }

      $restoreCategory = null;
      if($category['deleted_at']){
        $this -> _categoryLib -> restoreCategory($note['category_id']);
        $category['deleted_at'] = null;
        $restoreCategory = $category;
      }

      $this -> _noteLib -> restoreNote($body['note_id']);
      return [
        'code' => 0,
        'message' => 'success',
        'data' => [
          'restore_category' => $restoreCategory
        ]
      ];
    }

    function _handleCompletelyDeleteNote(){
      $raw = file_get_contents('php://input');
      $body = json_decode($raw, true);

      if(!$body['note_id']){
        throw new Exception('参数错误', ErrorCode::INVALID_PARAMS);
      }

      $note = $this -> _noteLib -> getNoteBasicInfo($body['note_id'], true);

      if(!$note){
        throw new Exception('记录不存在', ErrorCode::RECORD_NOT_FOUND);
      }

      $this -> _noteLib -> completelyDeleteNote($body['note_id']);

      $category = $this -> _categoryLib -> getCategoryInfo($note['category_id'], true);

      if($category){
        $categoryNote = $this -> _noteLib -> getCategoryNote($note['category_id']);
        if(empty($categoryNote) && $category['deleted_at'] !== null){
          $this -> _categoryLib -> completelyDeleteCategory($note['category_id']);
        }
      }

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

    private function _handleGetPublishNote(){
      $params = $_GET;

      if(!$params['note_id']){
        throw new Exception('参数错误', ErrorCode::INVALID_PARAMS);
      }
      
      $res = $this -> _noteLib -> getPublishNote($params['note_id']);

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
  }
