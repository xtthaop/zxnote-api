<?php

class JwtAuth {
  private static $tokenRedisKey = 'zxnote_token_blacklist';

  private static $header = [
    'alg' => 'HS256',
    'typ' => 'JWT'
  ];

  private static $key = 'zxnote';

  private static function signature(string $input, string $key, string $alg = 'HS256'){
    $alg_config = [ 'HS256' => 'sha256' ];
    return self::base64UrlEncode(hash_hmac($alg_config[$alg], $input, $key, true));
  }

  public static function getToken(array $payload){
    if(is_array($payload)){
      $base64header = self::base64UrlEncode(json_encode(self::$header, JSON_UNESCAPED_UNICODE));
      $base64payload = self::base64UrlEncode(json_encode($payload, JSON_UNESCAPED_UNICODE));

      $token = $base64header . '.' . $base64payload . '.' . self::signature($base64header . '.' . $base64payload, self::$key, self::$header['alg']);
      return $token;
    }else{
      return false;
    }
  } 

  public static function verifyToken(string $token){
    $tokens = explode('.', $token);
    if(count($tokens) != 3){
      return false;
    }

    list($base64header, $base64payload, $sign) = $tokens;

    $base64decodeheader = json_decode(self::base64UrlDecode($base64header), JSON_OBJECT_AS_ARRAY);
    if(empty($base64decodeheader['alg'])){
      return false;
    }

    if(self::signature($base64header . '.' . $base64payload, self::$key, $base64decodeheader['alg']) !== $sign){
      return false;
    }

    $payload = json_decode(self::base64UrlDecode($base64payload), JSON_OBJECT_AS_ARRAY);

    if(isset($payload['iat']) && $payload['iat'] > time()){
      return false;
    }

    if(isset($payload['nbf']) && $payload['nbf'] > time()){
      return false;
    }

    $refreshTime = 30 * 60;

    if(isset($payload['exp']) && $payload['exp'] < time()){
      return false;
    }

    if(isset($payload['exp']) && $payload['exp'] > time() && ($payload['exp'] - $refreshTime) <= time()){
      $newPayload = self::generatePayload($payload);
      $newToken = self::getToken($newPayload);
      setcookie('ZXNOTETOKEN', $newToken, 0, '/');
    }
    
    return $payload;
  }

  public static function generatePayload($info){
    $lifeTime = 2 * 60 * 60;


    return [
      "iss" => "root",
      "sub" => "zxnote",
      "iat" => time(),
      "nbf" => time(),
      "exp" => time() + $lifeTime,
      "jti" => md5(uniqid('JWT').time()),
      "uid" => $info['uid'],
      "unm" => $info['unm'],
    ];
  }

  private static function base64UrlEncode(string $input){
    return str_replace('=', '', strtr(base64_encode($input), '+/', '-_'));
  }

  private static function base64UrlDecode(string $input){
    $remainder = strlen($input) % 4;
    if($remainder){
      $addlen = 4 - $remainder;
      $input .= str_repeat('=', $addlen);
    }
    return base64_decode(strtr($input, '-_', '+/'));
  }

  public function md5Password($string, $key = 'ZxNo@te!19@96#'){
    return md5($string . $key);
  }

  public function addTokenToBlack($token) {
    $redis = new Redis();
    $redis -> connect('127.0.0.1', 6379);
    $redis -> zAdd(self::$tokenRedisKey, time(), $token);
    $redis -> close();
  }

  public function checkTokenInBlack($token) {
    $redis = new Redis();
    $redis -> connect('127.0.0.1', 6379);
    $res = $redis -> zScore(self::$tokenRedisKey, $token);
    $redis -> close();
    return $res;
  }
}
