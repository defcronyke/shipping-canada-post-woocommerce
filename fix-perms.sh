#!/bin/sh
#
# Run this after making new files to fix the permissions.

sudo chmod -R ug+rw .
sudo chown -R www-data:www-data .

