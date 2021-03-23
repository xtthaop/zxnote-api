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
        case 'DELETE':
          return $this -> _handleDeleteCategory();
        case 'PUT':
	  return $this -> _handleUpdateCategory();
        default:
          throw new Exception('请求方法不允许', 405);
      }
    }

    private function _handleCreateCategory(){
      $raw = file_get_contents('php://input');
      $body = json_decode($raw, true);
      
      if(!$body['category_name']){
        throw new Exception('请输入分类名称', ErrorCode::NO_CATEGORY_NAME);
      }

      $id = $this -> _lib -> createCategory($body['category_name']);
      return [
        'code' => 0,
        'success' => 'success',
        'data' => [
          'category_id' => intval($id),
        ]
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

    private function _handleDeleteCategory(){
      $raw = file_get_contents('php://input');
      $body = json_decode($raw, true);

      if(!$body['category_id']){
        throw new Exception('参数错误', ErrorCode::INVALID_PARAMS);
      }

      $this -> _lib -> deleteCategory($body['category_id']);
      return [
        'code' => 0,
        'success' => 'success'
      ];
    }

    private function _handleUpdateCategory(){
      $raw = file_get_contents('php://input');
      $body = json_decode($raw, true);

      if(!$body['category_name']){
        throw new Exception('请输入分类名称', ErrorCode::NO_CATEGORY_NAME);
      }

      if(!$body['category_id']){
        throw new Exception('参数错误', ErrorCode::INVALID_PARAMS);
      }
     
      $this -> _lib -> updateCategory($body['category_id'], $body['category_name']);
      return [
        'code' => 0,
        'success' => 'success'
      ];
    }
  }

  return new Category($lib);
