<?php

class WXSDK {
  private $appId;
  private $appSecret;

  public function __construct($appId, $appSecret){
    $this -> appId = $appId;
    $this -> appSecret = $appSecret;
  }

  public function getSignPackage($url){
    $jsApiTicket = $this -> getJsApiTicket();
    
    if(!$url){
      $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? 'https://' : 'http://';
      $url = "$protocol$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
    }
    
    $timestamp = time();
    $nonceStr = $this -> createNonceStr();
    
    $string = "jsapi_ticket=$jsApiTicket&noncestr=$nonceStr&timestamp=$timestamp&url=$url";
    $signature = sha1($string);

    $signPackage = array(
      "appId" => $this -> appId,
      "nonceStr" => $nonceStr,
      "timestamp" => $timestamp,
      "url" => $url,
      "signature" => $signature,
    );
    
    return $signPackage;
  }

  private function createNonceStr($length = 16){
    $chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
    $str = '';
    for($i = 0; $i < $length; $i++){
      $str .= substr($chars, mt_rand(0, strlen($chars) - 1), 1);
    }
    return $str;
  }

  private function getJsApiTicket(){
    $data = json_decode($this -> get_php_file('wx_jsapi_ticket.php'));

    if($data -> expire_time < time()){
      $accessToken = $this -> getAccessToken();
      $url = "https://api.weixin.qq.com/cgi-bin/ticket/getticket?type=jsapi&access_token=$accessToken";
      
      $res = json_decode($this -> httpGet($url));
      $ticket = $res -> ticket;

      if($ticket){
        $data -> expire_time = time() + 7000;
        $data -> jsapi_ticket = $ticket;
        $this -> set_php_file('wx_jsapi_ticket.php', json_encode($data));
      }
    }else{
      $ticket = $data -> jsapi_ticket;
    }

    return $ticket;
  }

  private function getAccessToken(){
    $data = json_decode($this -> get_php_file('wx_access_token.php'));

    if($data -> expire_time < time()){
      $url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=$this->appId&secret=$this->appSecret";
      $res = json_decode($this -> httpGet($url));
      $accessToken = $res -> access_token;
      if($accessToken){
        $data -> expire_time = time() + 7000;
        $data -> access_token = $accessToken;
        $this -> set_php_file('wx_access_token.php', json_encode($data));
      }
    }else{
      $accessToken = $data -> access_token;
    }

    return $accessToken; 
  }

  private function httpGet($url){
    $curl = curl_init();
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl, CURLOPT_TIMEOUT, 500);
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, true);
    curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 2);
    curl_setopt($curl, CURLOPT_URL, $url);
    
    $res = curl_exec($curl);
    curl_close($curl);
    
    return $res;
  }

  private function get_php_file($filename){
    return trim(substr(file_get_contents($filename), 15));
  }

  private function set_php_file($filename, $content){
    $fp = fopen($filename, "w");
    fwrite($fp, "<?php exit();?>" . $content);
    fclose($fp);
  } 
}
