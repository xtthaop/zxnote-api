<?php
  // 连接数据库并返回数据库连接句柄
  $pdo = new PDO('mysql:host=127.0.0.1;dbname=zxnote', 'root', 'root');
  $pdo -> setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
  $pdo -> query('set names utf8');
  return $pdo;

