<?php

class Permission {
  private $_captcha;

  public function __construct(Captcha $captcha){
    $this -> _captcha = $captcha;
  }

  public function handlePermission(){
    $requestMethod = $_SERVER['REQUEST_METHOD'];
    switch($requestMethod){
      case 'GET':
        return $this -> _handleGetCaptcha();
      case 'POST':
        return $this -> _handleLogin();
      default:
        throw new Exception("请求方法不被允许", 405);
    }
  }

  private function _handleGetCaptcha(){
    $res = $this -> _captcha -> makeCaptcha();
    session_start();
    $_SESSION['captcha_x'] = $res['x'];
    unset($res['x']);
    return [
      'code' => 0,
      'message' => 'success',
      'data' => $res,
    ];
  }

  private function _handleLogin(){
    
  }
} 
