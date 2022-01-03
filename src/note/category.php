<?php
  class Category{

    private $_categoryLib;
    private $_noteLib;

    public function __construct(CategoryLib $categoryLib, NoteLib $noteLib){
      $this -> _categoryLib = $categoryLib;
      $this -> _noteLib = $noteLib;
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
          throw new Exception('请求方法不被允许', 405);
      }
    }

    private function _handleCreateCategory(){
      $raw = file_get_contents('php://input');
      $body = json_decode($raw, true);
      
      if(!$body['category_name']){
        throw new Exception('请输入分类名称', ErrorCode::NO_CATEGORY_NAME);
      }

      $id = $this -> _categoryLib -> createCategory($body['category_name']);
      return [
        'code' => 0,
        'message' => 'success',
        'data' => [
          'category_id' => intval($id),
        ]
      ];
    }

    private function _handleGetCategoryList(){
      $categoryList = $this -> _categoryLib -> getCategoryList();
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

      $this -> _categoryLib -> deleteCategory($body['category_id']);
      $this -> _noteLib -> deleteCategoryAllNote($body['category_id']);
      return [
        'code' => 0,
        'message' => 'success'
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
     
      $this -> _categoryLib -> updateCategory($body['category_id'], $body['category_name']);
      return [
        'code' => 0,
        'message' => 'success'
      ];
    }
  }

