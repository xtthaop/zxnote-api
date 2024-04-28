<?php
  session_start();
  date_default_timezone_set('PRC');
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
  require './utils/wxsdk.php';

  $gUserId;

  class Restful{
    private $_category;
    private $_note;
    private $_upload;
    private $_permission;
    private $_jwt;

    private $_requestMethod;

    private $_resourceName;

    private $_allowResource = ['category', 'note', 'upload', 'permission'];

    private $_allowRequestMethod = ['GET', 'PUT', 'POST', 'DELETE', 'OPTIONS'];

    private $_permissionWhiteList = [
      '/permission/login', 
      '/permission/get_captcha', 
      '/note/get_published_note_list', 
      '/note/get_note', 
      '/note/get_wx_config',
    ];

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

    public function __construct(Category $category, Note $note, Upload $upload, Permission $permission, JwtAuth $jwt){
      $this -> _category = $category;
      $this -> _note = $note;
      $this -> _upload = $upload;
      $this -> _permission = $permission;
      $this -> _jwt = $jwt;
    }

    private function _setupRequestMethod(){
      $this -> _requestMethod = $_SERVER['REQUEST_METHOD'];
      if(!in_array($this -> _requestMethod, $this -> _allowRequestMethod)){
        throw new Exception('请求方法不被允许', '405');
      }
    }

    private function _setupResource(){
      $path = $_SERVER['PATH_INFO'];
      $params = explode('/', $path);
      $this -> _resourceName = $params[1];
      if(!in_array($this -> _resourceName, $this -> _allowResource)){
        throw new Exception('请求的资源不存在', '400');
      }
    }

    private function _verifyToken(){
      global $gUserId;
      $path = $_SERVER['PATH_INFO'];

      if(empty($_SERVER['HTTP_X_TOKEN'])){
        if(!in_array($path, $this -> _permissionWhiteList)){
          throw new Exception("权限验证失败，请登录", 401);
        }
      }else{
        if (in_array($path, $this -> _permissionWhiteList)){
          return;
        }

        $tokenInBlack = $this -> _jwt -> checkTokenInBlack($_SERVER['HTTP_X_TOKEN']);
        if($tokenInBlack){
          throw new Exception("权限验证失败，请重新登录", 401);
        }

        $res = $this -> _jwt -> verifyToken($_SERVER['HTTP_X_TOKEN']);

        if(!empty($res)){
          $gUserId = $res['uid'];
        }else{
          throw new Exception("权限验证失败，请重新登录", 401);
        }
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
        $this -> _verifyToken();
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

  $wxAppId = 'wx9dd2acc63e6647f7';
  $wxAppSecret = '409859a0db59e7522179734ddc4feed2';
  $wxsdk = new WXSDK($wxAppId, $wxAppSecret);

  $categoryLib = new CategoryLib($pdo);
  $noteLib = new NoteLib($pdo);
  $permissionLib = new PermissionLib($pdo);

  $upload = new Upload();
  $permission = new Permission($permissionLib, $captcha, $jwt);
  $category = new Category($categoryLib, $noteLib);
  $note = new Note($noteLib, $categoryLib, $wxsdk, $upload);

  $restful = new Restful($category, $note, $upload, $permission, $jwt);
  $restful -> run();

