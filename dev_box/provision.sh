#!/usr/bin/env bash

set -e # Exit script immediately on first error.
set -x # Print commands and their arguments as they are executed.

# Copy the sites to the nginx directory
sudo cp /vagrant/default /etc/nginx/sites-available

# Import the configuration file
source /vagrant/config.cfg

# Add our site routes to the virtual /etc/hosts file
echo "127.0.1.1 $site_web $site_api" | sudo tee -a /etc/hosts

# Edit the server config to update the web / api server
sed -i -e "s/site_web/$site_web/g" /etc/nginx/sites-available/default
sed -i -e "s/site_api/$site_api/g" /etc/nginx/sites-available/default

# Enable the site
sudo ln -s /etc/nginx/sites-available/default /etc/nginx/sites-enabled/default

# Touch the log files to make sure they exist
sudo touch /var/www/log/api-calls.log
sudo touch /var/www/log/api-slim.log
sudo touch /var/www/log/nginx.access.log
sudo touch /var/www/log/nginx.api-access.log
sudo touch /var/www/log/nginx.api-error.log
sudo touch /var/www/log/nginx.error.log

# Enable permissions on log folders
sudo chmod -R 775 /var/www/log

# Restart the nginx service to show enabled sites
sudo service nginx restart
