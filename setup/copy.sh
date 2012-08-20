#!/bin/bash
#scp -r ../* thomas@192.168.0.98:/var/www/webtest
rsync -avzr ../* --exclude 'setup' thomas@192.168.0.98:/var/www/webtest
rsync -avzr ./etc/web-admin/* thomas@192.168.0.98:/etc/web-admin
