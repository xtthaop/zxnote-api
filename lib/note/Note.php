<?php
  class NoteLib {
    
    private $_db;

    public function __construct($db){
      $this -> _db = $db;
    }

    public function publishNote($noteId, $status){
      $sql = 'UPDATE `note` SET `publish_status`=:publish_status WHERE `note_id`=:note_id';
      $stml = $this -> _db -> prepare($sql);
      $stml -> bindParam(':publish_status', $status);
      $stml -> bindParam(':note_id', $noteId);
      $stml -> execute();
    }

    public function createNote($title, $categoryId){
      $sql = 'INSERT INTO `note` (`note_title`, `category_id`) VALUES (:note_title, :category_id)';
      $stml = $this -> _db -> prepare($sql);
      $stml -> bindParam(':note_title', $title);
      $stml -> bindParam(':category_id', $categoryId);
      $stml -> execute();
      return $this -> _db -> lastInsertId();
    }

    public function getPublishedNoteList(){
      $sql = 'SELECT * FROM `note` where `publish_status`=1';
      $stml = $this -> _db -> prepare($sql);
      $stml -> execute();
      $result = $stml -> fetchAll(PDO::FETCH_ASSOC);
      return $result;
    }

    public function getCategoryNote($categoryId){
      $sql = 'SELECT `note_id`, `note_title`, `create_time`, `publish_status` FROM `note` WHERE `category_id`=:category_id ORDER BY `create_time` DESC';
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

    public function moveNote($categoryId, $noteId){
      $sql = 'UPDATE `note` SET `category_id`=:category_id WHERE `note_id`=:note_id';
      $stml = $this -> _db -> prepare($sql);
      $stml -> bindParam(':category_id', $categoryId);
      $stml -> bindParam(':note_id', $noteId);
      $stml -> execute();
    }

    public function getNoteContent($noteId){
      $sql = 'SELECT `note_content` FROM `note` WHERE `note_id`=:note_id';
      $stml = $this -> _db -> prepare($sql);
      $stml -> bindParam(':note_id', $noteId);
      $stml -> execute();
      $content = $stml -> fetch(PDO::FETCH_ASSOC);
      return $content;
    }

    public function saveNote($noteId, $noteTitle, $noteContent){
      $sql = 'UPDATE `note` SET `note_title`=:note_title, `note_content`=:note_content WHERE `note_id`=:note_id';
      $stml = $this -> _db -> prepare($sql);
      $stml -> bindParam(':note_id', $noteId);
      $stml -> bindParam(':note_title', $noteTitle);
      $stml -> bindParam(':note_content', $noteContent);
      $stml -> execute();
    }
  }

