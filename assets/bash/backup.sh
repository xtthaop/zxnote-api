set -e

echo -e "\n"$(date "+%Y-%m-%d %H:%M:%S") "开始自动备份" >> backup.log

DIR="backup_pkg"

if [ -d "$DIR" ]; then
  rm -rf "$DIR"
fi

# 备份数据库
mkdir mysql_databases
mysqldump -u root --databases zxnote > zxnote.sql
mv zxnote.sql mysql_databases

echo $(date "+%Y-%m-%d %H:%M:%S") "数据库备份生成" >> backup.log

# 备份图片
mkdir uploads
mkdir zxnote
cp -r /var/www/zxnote/zxnote-api/uploads/images ./
mv images zxnote
cp -r /var/www/zxnote/zxnote-api/uploads_clear_backup/images ./
mv images images_clear
mv images_clear zxnote
mv zxnote uploads

echo $(date "+%Y-%m-%d %H:%M:%S") "图片备份生成" >> backup.log

# 备份 apache2 配置文件
mkdir confs
cp -r /etc/apache2 ./
mv apache2 confs

echo $(date "+%Y-%m-%d %H:%M:%S") "配置文件备份生成" >> backup.log

# 生成压缩包
mkdir backup_pkg
mv mysql_databases uploads confs backup_pkg
tar -czvf backup_pkg.tar.gz backup_pkg > /dev/null
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
