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
          case 'logout':
            return $this -> _handleUserLogout();
          default:
            throw new Exception('请求的资源不存在', 404);
        }
      case 'PUT':
        switch($params[2]){
          case 'change_password':
            return $this -> _handleChangePassword();
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
    
    $xMax = $_SESSION['captcha_x'] + 6;
    $xMin = $_SESSION['captcha_x'] - 6;
    
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

    $password = $this -> _jwt -> md5Password($body['password']);
    // var_dump($password);
    $res = $this -> _permission -> login($body['username'], $password);

    if(!empty($res)){
      $payload = $this -> _jwt -> generatePayload($res);
      $token = $this -> _jwt -> getToken($payload);

      return [
        'code' => 0,
        'message' => 'success',
        'data' => [
          'token' => $token,
        ],
      ];
    }else{
      // 重置验证码否则拿到这个验证码可不受验证限制不断重试密码
      $_SESSION['captcha_x'] = mt_rand(0, $_SESSION['captcha_x'] - 10);
      throw new Exception("用户名与密码不匹配", ErrorCode::USER_VERIFY_FAILED);
    }
  }

  private function _handleGetUserInfo(){
    global $gUserId;
    
    if(!$gUserId){
      throw new Exception('参数错误', ErrorCode::INVALID_PARAMS);
    }

    $res = $this -> _permission -> getUserInfo($gUserId);

    return [
      'code' => 0,
      'message' => 'success',
      'data' => $res,
    ];
  }

  private function _handleChangePassword(){
    global $gUserId;
    $raw = file_get_contents('php://input');
    $body = json_decode($raw, true);

    if(
      !(isset($body['old_password']) && strlen($body['old_password'])) ||
      !(isset($body['new_password']) && strlen($body['new_password'])) ||
      $body['confirm_password'] !== $body['new_password']
    ){
      throw new Exception('参数错误', ErrorCode::INVALID_PARAMS);
    }

    $oldPassword = $this -> _jwt -> md5Password($body['old_password']);
    $res = $this -> _permission -> verifyOldPassword($gUserId, $oldPassword);

    if(!empty($res)){
      $body['new_password'] = $this -> _jwt -> md5Password($body['new_password']);
      $this -> _permission -> changePassword($gUserId, $body);
    }else{
      throw new Exception('旧密码验证失败', ErrorCode::OLD_PASSWORD_VERIFY_FAILED);
    }

    return [
      'code' => 0,
      'message' => 'success',
    ];
  }

  private function _handleUserLogout(){
    $this -> _jwt -> addTokenToBlack($_SERVER['HTTP_X_TOKEN']);

    return [
      'code' => 0,
      'message' => 'success',
    ];
  }
} 
