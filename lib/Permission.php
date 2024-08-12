<?php

class PermissionLib {
  private $_db;

  public function __construct($db){
    $this -> _db = $db;
  }

  public function login($username){
    $sql = 'SELECT `user_id` as `uid`, `username` as `unm`, `password` FROM `user` WHERE `username`=:username';
    $stmt = $this -> _db -> prepare($sql);
    $stmt -> bindParam(':username', $username);
    $stmt -> execute();
    $res = $stmt -> fetch(PDO::FETCH_ASSOC);
    return $res;
  }

  public function getUserInfo($userId){
    $sql = 'SELECT `user_id`, `username` FROM `user` WHERE `user_id`=:user_id';
    $stmt = $this -> _db -> prepare($sql);
    $stmt -> bindParam(':user_id', $userId);
    $stmt -> execute();
    $res = $stmt -> fetch(PDO::FETCH_ASSOC);
    return $res;
  }

  public function getPasswordByUserId($userId){
    $sql = 'SELECT `password` FROM `user` WHERE `user_id`=:user_id';
    $stml = $this -> _db -> prepare($sql);
    $stml -> bindParam(':user_id', $userId);
    $stml -> execute();
    $result = $stml -> fetch(PDO::FETCH_ASSOC);
    return $result;
  }

  public function changePassword($userId, $body){
    $sql = 'UPDATE `user` SET `password`=:password WHERE `user_id`=:user_id';
    $stml = $this -> _db -> prepare($sql);
    $stml -> bindParam(':user_id', $userId);
    $stml -> bindParam(':password', $body['new_password']);
    $stml -> execute();
  }
}
