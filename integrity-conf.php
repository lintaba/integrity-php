<?php
#integrity-checker configuration file

#database file; (files with .ht wont visible from apache)
define( "DATAFILE", "./.ht-checksum.log" );

#base path of the directory that'll be checked
define( "CHECK_DIR", "./" );

#regex pattern, to skip files (and folders) from checking, like the database file
define( "PATTERN_SKIP", "/\\.ht-checksum\\.log$/" );

#crc32, md5, sha1
define( "HASH_ALGO", "crc32" );

#send email/url report if everything is ok?
define( "REPORT_OK", false );

#show HTML-based report?
define( "REPORT_HTML", true );

#send email about reports?
define( "REPORT_EMAIL", false);

#email extra headers, like sender, content-type, etc.
define( "REPORT_EMAIL_HEADERS", "Content-type: text/html; charset=utf-8" );

#url to save the report. There will be a ?name=title&data=html_content variables sent via POST.
define( "REPORT_URL", false && "http://SERVER/log.php" );

#title of report
define( "REPORT_TITLE", "[REPORT] Changes on ".$_SERVER["HTTP_HOST"] );

#quick scan, means skip hash checks, just modification time and filesize matters. (still generates hash for new/changed files)
$scan_quick=false;

#recursive to subdirectories
$recursive=true;

#show OK and SKIP messages in log for those files, witch are identical with the last stored state.
$show_all=false;
