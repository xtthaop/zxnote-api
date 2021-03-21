<?php
  $lib = require './lib/note/Category.php';
  
  class Category{

    private $_lib;

    public function __construct(CategoryLib $lib){
      $this -> _lib = $lib;
    } 

    public function handleCategory(){
      $requestMethod = $_SERVER['REQUEST_METHOD'];
      switch($requestMethod){
        case 'POST':
          return $this -> _handleCreateCategory();
        case 'GET':
          return $this -> _handleGetCategoryList();
        default:
          throw new Exception('请求方法不允许', 405);
      }
    }

    private function _handleCreateCategory(){
      $raw = file_get_contents('php://input');
      $body = json_decode($raw, true);
      
      $id = $this -> _lib -> createCategory($body['category_name']);
      return [
        'code' => 0,
        'success' => 'success',
	'category_id' => $id 
      ];
    }

    private function _handleGetCategoryList(){
      $categoryList = $this -> _lib -> getCategoryList();
      return [
        'code' => 0,
        'message' => 'success',
        'data' => [
	  'category_list' => $categoryList,
        ],
      ];
    }
  }

  return new Category($lib);
