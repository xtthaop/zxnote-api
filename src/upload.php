<?php

class Upload {
  public function handleUpload(){
    $requestMethod = $_SERVER['REQUEST_METHOD'];
    switch ($requestMethod){
      case 'POST':
        return $this -> _handleUploadFile();
      default:
        throw new Exception("请求方法不被允许", 405);
    }
  }

  private function _handleUploadFile(){
    $key = $_POST['key'];

    if(!$key){
      throw new Exception('参数错误', ErrorCode::INVALID_PARAMS);
    }

    $params = explode('/', $key);
    $uploadDir = $params[0];
    $fileName = $params[1];

    if(!$uploadDir || !$fileName){
      throw new Exception('参数错误', ErrorCode::INVALID_PARAMS);
    }

    $root = '/usr/local/httpd/htdocs/zxnote/uploads/';
    $urlPrefix = 'https://zxctb.top/zxnote_uploads/';
    if(move_uploaded_file($_FILES["file"]["tmp_name"], $root . $key)){
      return [
        'code' => 0,
        'message' => 'success',
        'data' => [
           'url' => $urlPrefix . $key,
         ],
      ];
    }else{
      throw new Exception('上传文件失败', ErrorCode::UPLOAD_FILE_FAILED);
    }
  }
}

