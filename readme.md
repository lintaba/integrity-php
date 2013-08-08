This file integrity checker, and change tracker can show and notify the developer if any changes happens on the server.

Features:
 - generates hash of the entire folder and subfolders
 - email alert
 - URL alert
 - can be a cron job
 - fast
 
Usage:
 - Copy integrity.php and integrity-conf.php to your webroot.
 - Modify config, use your email address, webURL, etc.
 - Test and initialize the db by navigating to yoursite.com/integrity.php
 - Set up a cronjob to this url
 