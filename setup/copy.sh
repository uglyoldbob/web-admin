#!/bin/bash
#scp -r ../* thomas@192.168.0.98:/var/www/webtest
rsync -avzr --delete ../ --exclude 'setup' --exclude '.git' thomas@192.168.0.98:/var/www/webtest/
rsync -avzr --delete ./etc/web-admin/ thomas@192.168.0.98:/etc/web-admin/
