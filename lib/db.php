<?php
  // 连接数据库并返回数据库连接句柄
  $pdo = new PDO('mysql:host=127.0.0.1;dbname=zxnote', 'root', '4fc89863bd2fdea65f2f5643b3e98dc1');
  $pdo -> setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
  return $pdo;

