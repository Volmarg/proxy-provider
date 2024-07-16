#!/bin/bash

service apache2 restart;

echo -e "[DEBUG] Calling install-or-update \n";
cd /var/www/html && ./install-or-update.sh;