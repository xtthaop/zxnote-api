<?php
  session_start();
  require './src/ErrorCode.php';
  $pdo = require './lib/db.php';

  require './src/note/category.php';
  require './src/note/note.php';
  require './src/upload.php';
  require './src/permission.php';

  require './lib/note/Category.php';
  require './lib/note/Note.php';
  require './lib/Permission.php';
  
  require './utils/captcha.php';
  require './utils/jwt.php';

  class Restful{
    private $_category;
    private $_note;
    private $_upload;
    private $_permission;

    private $_requestMethod;

    private $_resourceName;

    private $_allowResource = ['category', 'note', 'upload', 'permission'];

    private $_allowRequestMethod = ['GET', 'PUT', 'POST', 'DELETE', 'OPTIONS'];

    private $_statusCode = [
      200 => 'OK',
      204 => 'No Content',
      400 => 'Bad Request',
      401 => 'Unauthorized',
      403 => 'Forbidden',
      404 => 'Not Found',
      405 => 'Method Not Allowed',
      500 => 'Server Internal Error'
    ];

    public function __construct(Category $category, Note $note, Upload $upload, Permission $permission){
      $this -> _category = $category;
      $this -> _note = $note;
      $this -> _upload = $upload;
      $this -> _permission = $permission;
    }

    private function _setupRequestMethod(){
      $this -> _requestMethod = $_SERVER['REQUEST_METHOD'];
      if(!in_array($this -> _requestMethod, $this -> _allowRequestMethod)){
        throw new Exception('请求方法不允许', '405');
      }
    }

    private function _setupResource(){
      $path = $_SERVER['PATH_INFO'];
      $params = explode('/', $path);
      $this -> _resourceName = $params[1];
      if(!in_array($this -> _resourceName, $this -> _allowResource)){
        throw new Exception('请求资源不允许', '400');
      }
    }

    private function _json($array){
      $code = $array['code'];
      if($code > 0 && $code < 2000 && $code != 200 && $code != 204){
        header('HTTP/1.1 ' . $code . ' ' . $this -> _statusCode[$code]);
      }
      header('Content-Type:application/json;charset=utf-8');
      echo json_encode($array, JSON_UNESCAPED_UNICODE);
      exit();
    }

    public function run(){
      try{
        $this -> _setupRequestMethod();
        $this -> _setupResource();
        if($this -> _resourceName == 'category'){
          $this -> _json($this -> _category -> handleCategory());
        }
        if($this -> _resourceName == 'note'){
          $this -> _json($this -> _note -> handleNote());
        }
        if($this -> _resourceName == 'upload'){
          $this -> _json($this -> _upload -> handleUpload());
        }
        if($this -> _resourceName === 'permission'){
          $this -> _json($this -> _permission -> handlePermission());
        }
      }catch(Exception $e){
        $this -> _json(['message' => $e -> getMessage(), 'code' => $e -> getCode()]);
      }
    } 
  }

  $captcha = new Captcha();
  $jwt = new JwtAuth();

  $categoryLib = new CategoryLib($pdo);
  $noteLib = new NoteLib($pdo);
  $permissionLib = new PermissionLib($pdo);

  $upload = new Upload();
  $permission = new Permission($permissionLib, $captcha, $jwt);
  $category = new Category($categoryLib, $noteLib);
  $note = new Note($noteLib, $categoryLib);

  $restful = new Restful($category, $note, $upload, $permission);
  $restful -> run();

