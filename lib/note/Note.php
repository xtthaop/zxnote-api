<?php
  class NoteLib {
    
    private $_db;

    public function __construct($db){
      $this -> _db = $db;
    }

    // 后台
    public function publishNote($noteId, $status){
      $currentTime = date('Y:m:d H:m:s');

      $selectSql = 'SELECT `status` FROM `note` WHERE `note_id`=:note_id';
      $stml = $this -> _db -> prepare($selectSql);
      $stml -> bindParam(':note_id', $noteId);
      $stml -> execute();
      $oldStatus = $stml -> fetch()[0];

      $arr = array();
      $updateSql = 'UPDATE `note` SET `status`=:status,';
      $arr[':status'] = $status;

      if($status){
        $updateSql .= ' `publish_note_title`=`note_title`, `publish_note_content`=`note_content`,';
        if($oldStatus){
          $updateSql .= ' `publish_update_time`=:publish_update_time';
        }else{
          $updateSql .= ' `publish_time`=:publish_time, `publish_update_time`=:publish_update_time';
          $arr[':publish_time'] = $currentTime;
        }
        $arr[':publish_update_time'] = $currentTime;
      }else{
        $updateSql .= ' `publish_note_title`=null, `publish_note_content`=null, `publish_update_time`=null, `publish_time`=null';
      }

      $updateSql .= ' WHERE `note_id`=:note_id';
      $arr[':note_id'] = $noteId;
      $stml = $this -> _db -> prepare($updateSql);
      $stml -> execute($arr);
    }

    public function addNote($title, $categoryId){
      $sql = 'INSERT INTO `note` (`note_title`, `category_id`) VALUES (:note_title, :category_id)';
      $stml = $this -> _db -> prepare($sql);
      $stml -> bindParam(':note_title', $title);
      $stml -> bindParam(':category_id', $categoryId);
      $stml -> execute();
      return $this -> _db -> lastInsertId();
    }

    public function getCategoryNote($categoryId){
      $sql = 'SELECT `note_id`, `note_title`, `create_time`, `status`
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
      $sql = 'SELECT `note_id`, `note_title`, `note_content`, `status`
              FROM `note` WHERE `note_id`=:note_id';
      $stml = $this -> _db -> prepare($sql);
      $stml -> bindParam(':note_id', $noteId);
      $stml -> execute();
      $content = $stml -> fetch(PDO::FETCH_ASSOC);
      return $content;
    }

    public function saveNote($noteId, $noteTitle, $noteContent){
      $selectSql = 'SELECT `status` FROM `note` WHERE `note_id`=:note_id';
      $stml = $this -> _db -> prepare($selectSql);
      $stml -> bindParam(':note_id', $noteId);
      $stml -> execute();
      $oldStatus = $stml -> fetch()[0];

      $updateSql = 'UPDATE `note` SET `note_title`=:note_title, `note_content`=:note_content';
      if($oldStatus === 1){
        $updateSql .= ', `status`=2';
      }
      $updateSql .= ' WHERE `note_id`=:note_id';
      $stml = $this -> _db -> prepare($updateSql);
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

    // 前台
    public function getPublishedNoteList(){
      $sql = 'SELECT `note_id`, `publish_note_title`, `publish_note_content`, `publish_time`, 
             `publish_update_time` FROM `note` where `status`>=1 ORDER BY publish_time DESC';
      $stml = $this -> _db -> prepare($sql);
      $stml -> execute();
      $result = $stml -> fetchAll(PDO::FETCH_ASSOC);
      return $result;
    }

    public function getNote($noteId){
      $sql = 'SELECT `note_id`, `publish_note_title`, `publish_note_content`, `publish_time`, 
             `publish_update_time` FROM `note` WHERE `note_id`=:note_id AND `status`>=1';
      $stml = $this -> _db -> prepare($sql);
      $stml -> bindParam(':note_id', $noteId);
      $stml -> execute();
      $res = $stml -> fetch(PDO::FETCH_ASSOC);
      return $res;
    }

    // 调试
    // public function updateNoteContentAndState($noteId, $noteContent) {
    //   $sql = 'UPDATE `note` SET `note_content`=:note_content, `publish_note_content`=`note_content`,
    //          `publish_update_status`=1 WHERE `note_id`=:note_id';
    //   $stml = $this -> _db -> prepare($sql);
    //   $stml -> bindParam(':note_id', $noteId);
    //   $stml -> bindParam(':note_content', $noteContent);
    //   $stml -> execute();
    // }
  }

