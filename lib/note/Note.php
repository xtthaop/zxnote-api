<?php
  class NoteLib {
    
    private $_db;

    public function __construct($db){
      $this -> _db = $db;
    }

    public function createNote($title, $categoryId){
      $sql = 'INSERT INTO `note` (`note_title`, `category_id`) VALUES (:note_title, :category_id)';
      $stml = $this -> _db -> prepare($sql);
      $stml -> bindParam(':note_title', $title);
      $stml -> bindParam(':category_id', $categoryId);
      $stml -> execute();
      return $this -> _db -> lastInsertId();
    }

    public function getAllNote(){
      $sql = 'SELECT * from `note`';
      $stml = $this -> _db -> prepare($sql);
      $stml -> execute();
      $result = $stml -> fetchAll(PDO::FETCH_ASSOC);
      return $result;
    }

    public function getCategoryNote($categoryId){
      $sql = 'SELECT * from `note` WHERE `category_id`=:category_id';
      $stml = $this -> _db -> prepare($sql);
      $stml -> bindParam(':category_id', $categoryId);
      $stml -> execute();
      $result = $stml -> fetchAll(PDO::FETCH_ASSOC);
      return $result;
    }

    public function deleteNote($noteId){
      $sql = 'DELETE FROM `note` WHERE note_id=:note_id';
      $stml = $this -> _db -> prepare($sql);
      $stml -> bindParam(':note_id', $noteId);
      $stml -> execute();
    }
    
    public function deleteCategoryAllNote($categoryId){
      $sql = 'DELETE FROM `note` WHERE category_id=:category_id';
      $stml = $this -> _db -> prepare($sql);
      $stml -> bindParam(':category_id', $categoryId);
      $stml -> execute();
    }
  }

