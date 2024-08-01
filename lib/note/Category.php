<?php

  class CategoryLib {

    private $_db;

    public function __construct($db){
      $this -> _db = $db;
    } 

    public function createCategory($categoryName){
      $sql = 'INSERT INTO `note_category` (`category_name`) VALUES (:category_name)';
      $stml = $this -> _db -> prepare($sql);
      $stml -> bindParam(':category_name', $categoryName);
      $stml -> execute();
      return $this -> _db -> lastInsertId();
    }

    public function getCategoryList(){
      $sql = 'SELECT `category_id`, `category_name` FROM `note_category` WHERE `deleted_at` IS NULL ORDER BY `create_time` DESC';
      $stml = $this -> _db -> prepare($sql);
      $stml -> execute();
      $res = $stml -> fetchAll(PDO::FETCH_ASSOC);
      return $res;
    }

    public function softDeleteCategory($categoryId){
      $currentTime = date('Y:m:d H:m:s');
      $sql = 'UPDATE `note_category` SET `deleted_at`=:deleted_at WHERE `category_id`=:category_id';
      $stml = $this -> _db -> prepare($sql);
      $stml -> bindParam(':category_id', $categoryId);
      $stml -> bindParam(':deleted_at', $currentTime);
      $stml -> execute();
    }

    public function completelyDeleteCategory($categoryId){
      $sql = 'DELETE FROM `note_category` WHERE `category_id`=:category_id';
      $stml = $this -> _db -> prepare($sql);
      $stml -> bindParam(':category_id', $categoryId);
      $stml -> execute();
    }

    public function updateCategory($categoryId, $categoryName){
      $sql = 'UPDATE `note_category` SET `category_name`=:category_name  WHERE `category_id`=:category_id';
      $stml = $this -> _db -> prepare($sql);
      $stml -> bindParam(':category_id', $categoryId);
      $stml -> bindParam(':category_name', $categoryName);
      $stml -> execute();
    }

    public function getCategoryInfo($categoryId, $isDeleted = false){
      $sql = 'SELECT * FROM `note_category` WHERE `category_id`=:category_id';

      if(!$isDeleted){
        $sql .= ' AND `deleted_at` IS NULL';
      }

      $stml = $this -> _db -> prepare($sql);
      $stml -> bindParam(':category_id', $categoryId);
      $stml -> execute();
      $res = $stml -> fetch(PDO::FETCH_ASSOC);
      return $res;
    }

    public function restoreCategory($categoryId){
      $sql = 'UPDATE `note_category` SET `deleted_at`=NULL WHERE `category_id`=:category_id';
      $stml = $this -> _db -> prepare($sql);
      $stml -> bindParam(':category_id', $categoryId);
      $stml -> execute();
    }
  }


