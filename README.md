<p align="center">
  <img src="https://github.com/xtthaop/zxnote-web/blob/main/src/assets/images/logo.png?raw=true" width="50px" />
</p>

<p align="center">
  <span style="color: #42c02e">知行笔记</span> - 内容管理系统开源后端
</p>

系统预览：[ZXNOTE](https://zxctb.top:9090/notebook)  
接口文档：[API](https://apifox.com/apidoc/shared-44ae147c-54da-4bb5-8295-64c962184bf9)

### 简介
  
支持新增、重命名和删除笔记分类；  
支持新增、删除笔记和移动笔记至其他分类；  
笔记标题、内容修改实时（延时 800ms）保存，支持 ctrl+s （command+s）、ctrl+z（command+z）、 ctrl+shift+z（command+shift+z）等常用快捷键；  
支持 Markdown 语法，LaTeX 语法，支持上传本地图片；  
支持切换预览模式，一边编辑笔记内容一边查看笔记预览；  
支持恢复笔记到某个历史版本，也支持在回收站中找回笔记；    
……

### 快速开始
以下内容仅供参考！！！

服务器系统版本：server ubuntu 22.04.1 LTS

**安装 mysql(8.0.33)**
```
apt install mysql-server
```

**安装 redis(6.0.16)**
```
apt install redis-server
```

**安装 php(8.1.2)**
```
apt install php
```

**安装 php 扩展**
```
apt install php-redis
apt install php-mysql
apt install php8.1-gd
apt install php8.1-curl
```

**安装 apache(2.4.52)**
```
apt install apache2
```

**启用 apache 模块**
```
a2enmod rewrite
a2enmod ssl
a2enmod proxy
a2enmod proxy_http
apt install libapache2-mod-php8.1
a2dismod mpm_event
a2enmod mpm_prefork
a2enmod php8.1
```

**初始化数据库**  
连接数据库执行：
```
source ......zxnote-api/assets/mysql/zxnote.sql
```
记得代码里面修改数据库连接配置

**修改文件夹权限**
```
chmod 777 ./public
chmod 777 ./uploads
chmod 777 ./uploads_clear_backup
```

**启动 apache 服务**  
记得启动服务之前修改 apache 服务配置
```
/etc/init.d/apache2 start
```

### 自动清除黑名单过期令牌
执行 `crontab -e` 写入：
```
0 6 * * * bash ......zxnote-api/assets/bash/zxnote-clear-expired-token.sh > /dev/null 2>&1 &
```

### 自动备份数据到百度云盘
**安装 bypy**
```
pip install bypy
```

**百度云盘验证**
```
bypy info
```
获取验证链接后在浏览器中打开复制验证码到命令行进行验证

**编写数据备份脚本**  
以下内容仅供参考！！！
```
echo -e "\n"$(date "+%Y-%m-%d %H:%M:%S") "开始自动备份" >> backup.log

# 备份数据库
mkdir mysql_databases
mysqldump -u root --databases zxnote > zxnote.sql
mv zxnote.sql mysql_databases

echo $(date "+%Y-%m-%d %H:%M:%S") "数据库备份生成" >> backup.log

# 备份图片
mkdir uploads
mkdir zxnote
cp -r ......zxnote-api/uploads/images ./
mv images zxnote
cp -r ......zxnote-api/uploads_clear_backup/images ./
mv images images_backup
mv images_backup zxnote
mv zxnote uploads

echo $(date "+%Y-%m-%d %H:%M:%S") "图片备份生成" >> backup.log

# 生成压缩包
mkdir backup_pkg
mv mysql_databases uploads backup_pkg
tar -czvf backup_pkg.tar.gz backup_pkg
rm -rf backup_pkg

echo $(date "+%Y-%m-%d %H:%M:%S") "压缩包生成" >> backup.log

# 上传到百度云盘
bypy upload backup_pkg.tar.gz

if [ $? -eq 0 ]
then
  echo $(date "+%Y-%m-%d %H:%M:%S") "备份到百度云盘成功" >> backup.log
else
  echo $(date "+%Y-%m-%d %H:%M:%S") "备份到百度云盘失败" >> backup.log
fi
```

**自动运行备份脚本**
执行 `crontab -e` 写入：
```
0 3 * * * bash ......backup.sh > /dev/null 2>&1 &
```

### 关于作者
ZXNOTE 的作者是一名前端小学生，水平不高，文档或者代码中如有不当之处还请指正。

### 成为赞助者
<img src="https://github.com/xtthaop/image-lib/blob/master/comodo-admin/sponsor.png?raw=true" width="300px" />

维护这个项目需要一定的服务器费用用作项目预览，还需要消耗我本人一定的精力，所以如果这个项目帮助到你的话，请多多予以支持！感谢！

### 许可证
[MIT](LICENSE.md)
