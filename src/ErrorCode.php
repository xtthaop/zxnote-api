<?php
  class ErrorCode{
    const NO_CATEGORY_NAME = 4001; // 分类名称不存在
    const INVALID_PARAMS = 4002; // 参数错误
    const NO_NOTE_TITLE = 4003; // 笔记标题不存在
    const NO_CATEGORY_ID = 4004; // 分类ID不存在
    const RECORD_NOT_FOUND = 4005; // 记录不存在
    const UPLOAD_FILE_FAILED = 4006; // 上传文件失败
    const CAPTCHA_VERIFY_FAILED = 4007; // 拼图验证码验证失败
    const USERNAME_CANNOT_EMPTY = 4008; // 用户名不能为空
    const PASSWOED_CANNOT_EMPTY = 4009; // 密码不能为空
    const USER_VERIFY_FAILED = 4010; // 用户名与密码不匹配 
  }
