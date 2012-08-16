#!/bin/bash
#scp -r ../* thomas@192.168.0.98:/var/www/webtest
rsync -avzr ../* thomas@192.168.0.98:/var/www/webtest
