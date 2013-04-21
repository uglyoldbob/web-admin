#!/bin/bash
OPTION_FILE="/etc/web-admin/config.ini"

DBNAME=`php parseini.php database_login database_name`
OPTS="--defaults-extra-file=$OPTION_FILE --compact"

#-d is to not dump data
mysqldump $OPTS -d $DBNAME contacts
mysqldump $OPTS -d $DBNAME contact_permission
mysqldump $OPTS -d $DBNAME cost_estimations
mysqldump $OPTS -d $DBNAME equipment
mysqldump $OPTS -d $DBNAME images
mysqldump $OPTS -d $DBNAME inspections
mysqldump $OPTS -d $DBNAME jobs
mysqldump $OPTS -d $DBNAME job_status
mysqldump $OPTS -d $DBNAME job_tasks
mysqldump $OPTS -d $DBNAME locations
mysqldump $OPTS -d $DBNAME payments
mysqldump $OPTS -d $DBNAME properties
mysqldump $OPTS -d $DBNAME status
mysqldump $OPTS -d $DBNAME status_codes
mysqldump $OPTS $DBNAME version

