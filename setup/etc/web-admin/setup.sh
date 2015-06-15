#!/bin/bash
#this script is intented to run as the same user as the webserver
#in order to properly setup the directories of the web folders
#it must be passed the location of the web folder
	#it might be something like the following
	#/var/www/something

if [ ! -d "$1/16x16" ]
  then
    unzip "$1/icons.zip" -d "$1/"
fi

#the uploads folder needs to be writable by the webserver user
if [ ! -d "$1/uploads" ]
  then
    mkdir "$1/uploads"
fi

