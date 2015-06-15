#!/bin/bash
SERVER=test.doors-software.com
SERVERA=$SERVER:/var/www/webtest
SERVERB=$SERVER:/etc/web-admin/

#scp -r ../* thomas@$SERVERA
rsync -avzr --delete ../ --exclude uploads --exclude 'setup' --exclude '.git' thomas@$SERVERA/
rsync -azvr ../uploads/ thomas@$SERVERA/uploads/
rsync -avzr --delete ./etc/web-admin/ thomas@$SERVERB
