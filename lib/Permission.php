<?php

class PermissionLib {
  private $_db;

  public function __construct($db){
    $this -> _db = $db;
  }

  public function login($username, $password){
    $sql = 'SELECT * FROM `user` WHERE `username`=:username AND `password`=:password';
    $stmt = $this -> _db -> prepare($sql);
    $stmt -> bindParam(':username', $username);
    $stmt -> bindParam(':password', $password);
    $stmt -> execute();
    $res = $stmt -> fetch(PDO::FETCH_ASSOC);
    return $res;
  }

  public function getUserInfo($userId){
    $sql = 'SELECT * FROM `user` WHERE `user_id`=:user_id';
    $stmt = $this -> _db -> prepare($sql);
    $stmt -> bindParam(':user_id', $userId);
    $stmt -> execute();
    $res = $stmt -> fetch(PDO::FETCH_ASSOC);
    return $res;
  }
}
