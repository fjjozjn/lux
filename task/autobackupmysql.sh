file_time=`date +%Y%m%d%H%M%S`
mysqldump -u root --password=3993979102 --default-character-set=utf8 -R --add-drop-database krnt_db > /root/Dropbox/luxerp/db/krnt_db_${file_time}.sql
