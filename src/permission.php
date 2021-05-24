<?php

class Permission {
  private $_permission;

  private $_captcha;
  private $_jwt;

  public function __construct(PermissionLib $permission, Captcha $captcha, JwtAuth $jwt){
    $this -> _permission = $permission;
    $this -> _captcha = $captcha;
    $this -> _jwt = $jwt;
  }

  public function handlePermission(){
    $requestMethod = $_SERVER['REQUEST_METHOD'];
    $path = $_SERVER['PATH_INFO'];
    $params = explode('/', $path);
    switch($requestMethod){
      case 'GET':
        switch($params[2]){
          case 'get_captcha':
            return $this -> _handleGetCaptcha();
          case 'get_user_info':
            return $this -> _handleGetUserInfo();
          default:
            throw new Exception('请求的资源不存在', 404);
        }
      case 'POST':
        switch($params[2]){
          case 'login':
            return $this -> _handleLogin();
          default:
            throw new Exception('请求的资源不存在', 404);
        }
      default:
        throw new Exception("请求方法不被允许", 405);
    }
  }

  private function _handleGetCaptcha(){
    $res = $this -> _captcha -> makeCaptcha();
    $_SESSION['captcha_x'] = $res['x'];
    unset($res['x']);
    return [
      'code' => 0,
      'message' => 'success',
      'data' => $res,
    ];
  }

  private function _handleLogin(){
    $raw = file_get_contents('php://input');
    $body = json_decode($raw, true);
    
    $xMax = $_SESSION['captcha_x'] + 2;
    $xMin = $_SESSION['captcha_x'] - 2;
    
    if(!$body['x'] && $body['x'] != 0){
      throw new Exception('参数错误', ErrorCode::INVALID_PARAMS);
    }

    if($body['x'] > $xMax || $body['x'] < $xMin){
      throw new Exception('拼图验证失败', ErrorCode::CAPTCHA_VERIFY_FAILED);
    }

    if(!$body['username']){
      throw new Exception("用户名不能为空", ErrorCode::USERNAME_CANNOT_EMPTY);
    }

    if(!$body['password']){
      throw new Exception("密码不能为空", ErrorCode::PASSWOED_CANNOT_EMPTY);
    }

    $password = $this -> _md5($body['password']);
    $res = $this -> _permission -> login($body['username'], $password);

    if(!empty($res)){
      $lifeTime = 24 * 60 * 60;
      $payload = [
        "iss" => "root",
        "sub" => "zxnote",
        "iat" => time(),
        "nbf" => time(),
        "exp" => time() + $lifeTime,
        "jti" => md5(uniqid('JWT').time()),
        "uid" => $res['user_id'],
        "unm" => $res['username'],
      ];

      $token = $this -> _jwt -> getToken($payload);

      return [
        'code' => 0,
        'message' => 'success',
        'data' => [
          'token' => $token,
        ],
      ];
    }else{
      throw new Exception("用户名与密码不匹配", ErrorCode::USER_VERIFY_FAILED);
    }
  }

  private function _md5($string, $key = 'ZxNo@te!19@96#'){
    return md5($string . $key);
  }

  private function _handleGetUserInfo(){
    if(!$userId){
      throw new Exception('参数错误', ErrorCode::INVALID_PARAMS);
    }

    $res = $this -> _permission -> getUserInfo($userId);

    return [
      'code' => 0,
      'message' => 'success',
      'data' => $res,
    ];
  }
} 
