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
      $sql = 'SELECT * FROM `note_category`';
      $stml = $this -> _db -> prepare($sql);
      $stml -> execute();
      $result = $stml -> fetchAll(PDO::FETCH_ASSOC);
      return $result;
    }

    public function deleteCategory($categoryId){
      $sql = 'DELETE FROM `note_category` WHERE category_id=:category_id';
      $stml = $this -> _db -> prepare($sql);
      $stml -> bindParam(':category_id', $categoryId);
      $stml -> execute();
    }

    public function updateCategory($categoryId, $categoryName){
      $sql = 'UPDATE `note_category` SET category_name=:category_name  WHERE category_id=:category_id';
      $stml = $this -> _db -> prepare($sql);
      $stml -> bindParam(':category_id', $categoryId);
      $stml -> bindParam(':category_name', $categoryName);
      $stml -> execute();
    }
  }

