#服务器上时间的格式是星期是英文，在前，所以处理起来很麻烦
#下面这种TIME显示不出来，所以把时间去掉了
#set "backup_date=%date:~10,4%%date:~4,2%%date:~7,2%%TIME:~0,2%%TIME:~3,2%%TIME:~6,2%"
set "backup_date=%date:~10,4%%date:~4,2%%date:~7,2%"

# -R是导出存储过程 --add-drop-table这个是增加drop table的语句
"C:\AppServ\Mysql\bin\mysqldump.exe" --opt -Q krnt_db -uroot -p3993979102 -R --add-drop-table > C:\Dropbox\luxerp\db\krnt_db_%backup_date%.sql

#压缩并删除sql文件
#20140211 压缩文件失败，测试一下不压缩会不会正常
"C:\Program Files\7-Zip\7z.exe" a "C:\Dropbox\luxerp\db\krnt_db_%backup_date%.7z" "C:\Dropbox\luxerp\db\krnt_db_%backup_date%.sql"
Del C:\Dropbox\luxerp\db\krnt_db_%backup_date%.sql