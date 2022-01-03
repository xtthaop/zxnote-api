<?php
  class NoteLib {
    
    private $_db;

    public function __construct($db){
      $this -> _db = $db;
    }

    public function publishNote($noteId, $status){
      $currentTime = date('Y:m:d H:m:s');
      $null = null;

      $selectSql = 'SELECT `publish_status` FROM `note` WHERE `note_id`=:note_id';
      $stml = $this -> _db -> prepare($selectSql);
      $stml -> bindParam(':note_id', $noteId);
      $stml -> execute();
      $publishStatus = $stml -> fetch()[0];

      $arr = array();
      $updateSql = 'UPDATE `note` SET `publish_note_title`=`note_title`, `publish_note_content`=`note_content`, 
                   `publish_status`=:publish_status, `publish_update_status`=:publish_update_status,';
      $arr[':publish_status'] = $status;
      $arr[':publish_update_status'] = $status;

      if($status){
        if($publishStatus){
          $updateSql .= ' `publish_update_time`=:current_time';
        }else{
          $updateSql .= ' `publish_time`=:current_time';
        }
        $arr[':current_time'] = $currentTime;
      }else{
        $updateSql .= ' `publish_update_time`=:update_null_time, `publish_time`=:publish_null_time';
        $arr[':update_null_time'] = $null;
        $arr[':publish_null_time'] = $null;
      }

      $updateSql .= ' WHERE `note_id`=:note_id';
      $arr[':note_id'] = $noteId;
      $stml = $this -> _db -> prepare($updateSql);
      $stml -> execute($arr);
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
      $sql = 'SELECT `note_id`, `publish_note_title`, `publish_note_content`, `publish_time`, 
             `publish_update_time` FROM `note` where `publish_status`=1 ORDER BY publish_time DESC';
      $stml = $this -> _db -> prepare($sql);
      $stml -> execute();
      $result = $stml -> fetchAll(PDO::FETCH_ASSOC);
      return $result;
    }

    public function getCategoryNote($categoryId){
      $sql = 'SELECT `note_id`, `note_title`, `create_time`, `publish_status`, `publish_update_status` 
              FROM `note` WHERE `category_id`=:category_id ORDER BY `create_time` DESC';
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

    public function getNote($noteId){
      $sql = 'SELECT `note_id`, `publish_note_title`, `publish_note_content`, `publish_time`, 
             `publish_update_time` FROM `note` WHERE `note_id`=:note_id AND `publish_status`=1';
      $stml = $this -> _db -> prepare($sql);
      $stml -> bindParam(':note_id', $noteId);
      $stml -> execute();
      $res = $stml -> fetch(PDO::FETCH_ASSOC);
      return $res;
    }

    public function saveNote($noteId, $noteTitle, $noteContent){
      $sql = 'UPDATE `note` SET `note_title`=:note_title, `note_content`=:note_content, 
             `publish_update_status`=0 WHERE `note_id`=:note_id';
      $stml = $this -> _db -> prepare($sql);
      $stml -> bindParam(':note_id', $noteId);
      $stml -> bindParam(':note_title', $noteTitle);
      $stml -> bindParam(':note_content', $noteContent);
      $stml -> execute();
    }

    public function getAllNoteContent(){
      $sql = 'SELECT `note_id`, `note_content` FROM `note`';
      $stml = $this -> _db -> prepare($sql);
      $stml -> execute();
      $result = $stml -> fetchAll(PDO::FETCH_ASSOC);
      return $result;
    }

    public function updateNoteContentAndState($noteId, $noteContent) {
      $sql = 'UPDATE `note` SET `note_content`=:note_content, `publish_note_content`=`note_content`,
             `publish_update_status`=1 WHERE `note_id`=:note_id';
      $stml = $this -> _db -> prepare($sql);
      $stml -> bindParam(':note_id', $noteId);
      $stml -> bindParam(':note_content', $noteContent);
      $stml -> execute();
    }
  }

