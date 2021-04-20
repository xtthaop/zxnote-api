<?php
  class Note {

    private $_noteLib;

    public function __construct(NoteLib $noteLib){
      $this -> _noteLib = $noteLib;
    }

    public function handleNote(){
      $requestMethod = $_SERVER['REQUEST_METHOD'];
      $path = $_SERVER['PATH_INFO'];
      $params = explode('/', $path);
      switch($requestMethod){
        case 'POST':
          switch($params[2]){
            case 'create_note':
              return $this -> _handleCreateNote();
            case 'move_note':
              return $this -> _handleMoveNote();
            case 'save_note':
              return $this -> _handleSaveNote();
            default:
              throw new Exception('请求的资源不存在', 404); 
          }
        case 'GET':
          switch($params[2]){
            case 'get_all_note':
              return $this -> _getAllNote();
            case 'get_category_note':
              return $this -> _getCategoryNote();
            case 'get_note_content':
              return $this -> _getNoteContent();
            default:
              throw new Exception('请求的资源不存在', 404);
          }
        case 'DELETE':
          return $this -> _handleDeleteNote();
        default:
          throw new Exception('请求方法不被允许', 405);
      }
    }

    private function _handleCreateNote(){
      $raw = file_get_contents('php://input');
      $body = json_decode($raw, true);

      if(!$body['note_title']){
        throw new Exception('请输入笔记标题', ErrorCode::NO_NOTE_TITLE);
      }

      if(!$body['category_id']){
        throw new Exception('参数错误', ErrorCode::INVALID_PARAMS);
      }
      
      $id = $this -> _noteLib -> createNote($body['note_title'], $body['category_id']);
      return [
        'code' => 0,
        'message' => 'success',
        'data' => [
          'note_id' => intval($id),
        ]
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

    private function _getAllNote(){
      $allNote = $this -> _noteLib -> getAllNote();
      return [
        'code' => 0,
        'message' => 'success',
        'data' => [
          'all_note_list' => $allNote,
        ],
      ];
    }

    private function _getCategoryNote(){
      $params = $_GET;

      if(!$params['category_id']){
        throw new Exception('参数错误', ErrorCode::INVALID_PARAMS);
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

    private function _getNoteContent(){
      $params = $_GET;

      if(!$params['note_id']){
        throw new Exception('参数错误', ErrorCode::INVALID_PARAMS);
      }

      $content = $this -> _noteLib -> getNoteContent($params['note_id']);
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
  }

