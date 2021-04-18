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
          return $this -> _handleCreateNote();
        case 'GET':
          switch($params[2]){
            case 'get_all_note':
              return $this -> _getAllNote();
            case 'get_category_note':
              return $this -> _getCategoryNote();
            default:
              throw new Exception('请求资源不存在', 404);
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
      $data = $_GET;

      if(!$data['category_id']){
        throw new Exception('参数错误', ErrorCode::INVALID_PARAMS);
      }

      $categoryNote = $this -> _noteLib -> getCategoryNote($data['category_id']);
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
  }
