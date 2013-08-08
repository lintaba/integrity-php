<?php
//checksum and data integrity checker
//by Lintaba
//2013.08.08
//License: CC by-sa http://creativecommons.org/licenses/by-sa/3.0/


ob_start();
error_reporting(E_ALL);
require("integrity-conf.php");


//init variables
$started=microtime(true);
$log=array();
$data=array();
$counter=0;
$changenum=0;
$skipped=0;
$scan=true;
define("VERSION","0.5");
define("HASH_UNREADABLE","[readonly]");
define("HASH_UNKNOWN","???");
define("HASH_DIR","DIR");
define("FOUNT",0);
define("HASH",1);
define("SIZE",2);
define("TIME",3);

//helper functions
function escape($str){
	return htmlspecialchars($str);
}
function escape_path($str){
	return "<span style='font-family:monospace;background-color:#aaa;padding:1px 3px;border-radius:2px;'>".escape($str)."</span>";
}
function format_date($stamp){
	return "<span style='font-family:monoscape;color:#666;' title='".date("c(U)")."''>".date("F d,D H:i",$stamp)."</span>";
}
function size($s){
	for($q=" kmgtepy",$i=0;$s>=1000;++$i,$s/=1000);
	return round($s*10)/10 .$q[$i]."b";
}

//task state:
//  full:    recursive/deep scan, with hashes, and show every scanned file.
//  data:    show cached file hash info from DATAFILE
//  phpinfo: just a phpinfo()
if(isset($_REQUEST["task"]))
	switch($_REQUEST["task"]){
		case "full":
			$scan_quick=false;
			$recursive=true;
			$show_all=true;
		break;
		case "phpinfo":
			phpinfo();
			$scan=false;
		break;
		case "data":
			$res=file_get_contents(DATAFILE);
			$res=str_replace(":","</td><td>",$res);
			$res=str_replace("\r\n","</td></tr>\r\n<tr><td>",$res);
			$res="<table><tr><td>$res</td></tr></table>";
			$scan=false;
			$log[]=$res;
		break;
	}

//load database file
if(!is_file(DATAFILE)|| !is_readable(DATAFILE)){
	touch(DATAFILE);
	$data=array();
	$log[]="Error: unable to read datafile: ".escape_path(DATAFILE);
}else {
	$data=array();
	$f=fopen(DATAFILE,'r');
	while($r=fgets($f)){
		$r=explode(":",trim($r));
		if(count($r))
			$data[$r[0]]=array(false,$r[HASH],$r[SIZE],$r[TIME]);
	}
	fclose($f);
}
//iterate files
if($scan){
	$dirs=array(CHECK_DIR);#add current dir to the queue, and iterate over all of those
	for($i=0;$i<count($dirs);$i++){
		$dir=$dirs[$i];
		$content=scandir($dir);
		foreach($content as $f){
			if($f=="." || $f==".." || !$f){continue;}
			#build path
			$path=$dir.$f;
			if(is_dir($path))
				$path.="/";
				
			#skip files
			if(preg_match(PATTERN_SKIP,$path)){
				if($show_all)
					$log[]="SKIP: ".escape_path($path);
				$skipped++;
				continue;
			}
			#add folders to queue
			if($recursive && is_dir($path))
				$dirs[]=$path;
				
			#collect information
			$time=-1;
			$size=-1;
			if(!is_readable($path)){
				$hash=HASH_UNREADABLE;
			}elseif(is_file($path)){
				if(!$scan_quick)
					$hash=hash_file(HASH_ALGO,$path);
				$time=filemtime($path);
				$size=filesize($path);
			}elseif(is_dir($path)){
				$hash=HASH_DIR;
			}else {
				$hash=HASH_UNKNOWN;
			}
		
			#new file
			if(!isset($data[$path])){
				if($scan_quick && is_file($path) && is_readable($path))
					$hash=hash_file(HASH_ALGO,$path);
				$log[]="New: ".escape_path($path).", hash:".$hash;
				$data[$path]=array(true,$hash,$size,$time);
				$changenum++;
			}else
			#modified file
			if(($scan_quick?false:$data[$path][HASH]!=$hash) || ($time==-1?false:$data[$path][TIME]!=$time) || $data[$path][SIZE]!=$size){
				if($scan_quick && is_file($path)&& is_readable($path))
					$hash=hash_file(HASH_ALGO,$path);
				$log[]="Changed: ".escape_path($path)." ".$data[$path][HASH]." to ".$hash."; "
					." <b>Last</b>: size: ".size($data[$path][SIZE])." modified: ".format_date( $data[$path][TIME])
					." <b>Curr</b>: size: ".size($size)." modified: ".format_date($time);
				$data[$path]=array(true,$hash,$size,$time);
				$changenum++;
			}
			#unchanged file
			else{
				$data[$path][FOUNT]=true;
				if($show_all)
					$log[]="Ok: ".escape_path($path)." size: ".size($data[$path][SIZE])." modified: ".format_date($data[$path][TIME]);
			}
			$counter++;
		}
	}

	#deleted files
	foreach($data as $path=>$file){
		if(!$file[FOUNT]){
			$log[]="Deleted: ".escape_path($path);
			unset($data[$path]);
			$changenum++;
		}
	}
}

#save data
if($changenum>0){
	$f=fopen(DATAFILE,'w');
	foreach($data as $k=>$v){
		$w=$k.":".$v[HASH].":".$v[SIZE].":".$v[TIME]."\r\n";
		fputs($f,$w,strlen($w));
	}
	fclose($f);
}

#handle changes, display/send reports

#fields in the report
$report=array(
	"Changed"=>$changenum?$changenum." db":false,
	"Checked"=>$counter?$counter. "db":false,
	"Skipped"=>$skipped?$skipped." db":false,
	"Report time"=>format_date(time()),
	"Running time"=>round((microtime(true)-$started)*1000)." ms",
	"Last update"=>format_date(getlastmod()),
	"Self-sum"=>hash_file(HASH_ALGO,__FILE__),
	"App-version"=>VERSION,
	"Php-version"=>phpversion(),
	"Server"=>php_uname(),
	
	"Errors"=>ob_get_clean(),
	"Output"=>implode("<br>\r\n",$log),
);

# generating report
$result="\r\n<h1>Report of ".$_SERVER["HTTP_HOST"]."</h1>\r\n";
$result.="<table>";
foreach($report as $k=>$d){
	if($d)
		$result.="<tr><td><b>$k</b></td><td>$d</td></tr>\r\n";
}
$base = 'http://' . $_SERVER['SERVER_NAME'].($_SERVER["SERVER_PORT"]==80?"":":".$_SERVER["SERVER_PORT"]). $_SERVER['SCRIPT_NAME'];
$result.="</table><hr>\r\n";
$result.="
<a href='$base'>Run</a> &bull; 
<a href='$base?task=full'>Full report</a> &bull; 
<a href='$base?task=data'>Datafile</a> &bull;
<a href='$base?task=phpinfo'>phpinfo()</a>
";
# /generating report


#report html depends only on config, and dont rely on any changes
if(REPORT_HTML){
	echo "<html><head><meta http-equiv='Content-Type' content='text/html; charset=utf-8' />
	<title>".REPORT_TITLE."</title>
</head><body>
	
	".$result."
</body></html>";
}

#if there was any change, or we should report anyways, we'll report
$should_report=REPORT_OK || $changenum>0;

#send email if we should
if($should_report && REPORT_EMAIL){
	$should_report=!mail(REPORT_EMAIL,REPORT_TITLE,$result,REPORT_EMAIL_HEADERS);
}

#send url report if we should AND IF we didn't sent any email
if($should_report && REPORT_URL){
	file_get_contents(REPORT_URL,false,stream_context_create(array(
		'http' => array(
			'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
			'method'  => 'POST',
			'content' => http_build_query(array('name'=>REPORT_TITLE,'data'=>$result)),
		),
	)));
}
#if there wasnt any useable output, we show a general info, without any useable information
if(!REPORT_HTML && REPORT_EMAIL)
	echo "integrity checked; ver ".VERSION." hash ".hash_file(HASH_ALGO,__FILE__).":".hash_file(HASH_ALGO,DATAFILE). "(result sent via email)";
