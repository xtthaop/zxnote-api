<p align="center">
  <img src="https://github.com/xtthaop/zxnote-web/blob/main/src/assets/images/logo.png?raw=true" width="50px" />
</p>

<p align="center">
  <span style="color: #42c02e">知行笔记</span> - 内容管理系统开源后端
</p>

[系统预览](https://zxctb.top:8080/notebook)  
[接口文档](https://apifox.com/apidoc/shared-44ae147c-54da-4bb5-8295-64c962184bf9)

![screenshot.png](https://github.com/xtthaop/image-lib/blob/master/zxnote/zxctb.top.png?raw=true")

### 简介
     
支持新增、重命名和删除笔记分类；  
支持新增、删除笔记和移动笔记至其他分类；  
笔记标题、内容修改实时（延时 800ms）保存，支持常用快捷键；  
支持 Markdown 语法，LaTeX 语法，支持上传本地图片；  
支持切换预览模式，一边编辑笔记内容一边查看笔记预览；  
支持恢复笔记到某个历史版本，在回收站中找回笔记；  
可配置所有数据每日定时备份至云端，最大程度避免数据丢失；  
前后端代码全部开源并积极更新维护，[前端](https://github.com/xtthaop/zxnote-web)提供详细交互功能文档；  
可组合[知行博客](https://github.com/xtthaop/zxblog-web)开源项目搭建个人博客；   
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
apt install libapache2-mod-php8.1
```

**启用 apache 模块**

```
a2enmod rewrite
a2enmod ssl
a2enmod proxy
a2enmod proxy_http
a2dismod mpm_event
a2enmod mpm_prefork
a2enmod php8.1
```

**初始化数据库**  

使用 ./assets/mysql/zxnote.sql 文件初始化数据库，在 ./lib/db.php 文件中修改数据库连接配置

**修改文件权限**

```
chmod 777 ./public
chmod 777 ./uploads
chmod 777 ./uploads_clear_backup
chmod 777 ./wx_access_token.php
chmod 777 ./wx_jsapi_ticket.php
```

**启动服务**  

修改 apache 服务配置并执行：

```
/etc/init.d/apache2 start
```

**启动前端服务**

[开源前端](https://github.com/xtthaop/zxnote-web)  

初始账号：admin  
初始密码：111111

### 自动清理令牌黑名单过期令牌

执行 `crontab -e` 写入：
```
0 6 * * * bash (实际路径)/assets/bash/zxnote-clear-expired-token.sh > /dev/null 2>&1 &
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
获取验证链接后在浏览器中打开，复制验证码到命令行进行验证

**自动运行备份脚本**  

执行 `crontab -e` 写入：
```
0 3 * * * bash (实际路径)/assets/bash/backup.sh > /dev/null 2>&1 &
```

### 关于作者

知行笔记的作者是一名前端小学生，水平不高，文档或者代码中如有不当之处还请指正。

联系我：chentao231205@163.com

### 成为赞助者
<img src="https://github.com/xtthaop/image-lib/blob/master/comodo-admin/sponsor.png?raw=true" width="300px" />

维护这个项目需要一定的服务器费用用作项目预览，还需要消耗我本人一定的精力，所以如果这个项目帮助到你的话，请多多予以支持！感谢！

### 许可证
[MIT](LICENSE.md)
