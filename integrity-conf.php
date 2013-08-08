<?php

define( "DATAFILE", "./.ht-checksum.log" );
define( "CHECK_DIR", "./" );
define( "PATTERN_SKIP", "/\\.ht-checksum\\.log$/" );
define( "HASH_ALGO", "crc32" );//crc32, md5, sha1
define( "REPORT_OK", false );
define( "REPORT_HTML", true );
define( "REPORT_EMAIL", "lintaba+report@gmail.com" );
define( "REPORT_EMAIL_HEADERS", "From:dev@lintaba.hu\r\nContent-type: text/html; charset=utf-8" );
define( "REPORT_URL", false&&"http://lintaba.hu/log.php" );
define( "REPORT_TITLE", "[REPORT] Changes on ".$_SERVER["HTTP_HOST"] );

//runtime config
$scan_quick=false;
$recursive=true;
$show_ok=false;
$scan=true;
$show_data=false;