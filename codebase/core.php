<?php
// Core functions used wherever needed. Called by both page and ajax routines. 
// Fix php5.4+ timezone issue... 
@date_default_timezone_set(date_default_timezone_get());
// @date_default_timezone_set('UTC');

// Prevent PHP 7.2 nags messing up the session start.. 
 ini_set('display_errors', 0);

// For the sake of hardening, you can if you wish use a custom config directory.. 
define('configdir','sitecfg/',1);
// get site settings from ini file.. 
setsitedefaults();
getuseragent();

// Determine relative paths to content locations... 
syspaths();
// session_save_path(fsroot . 'tmp')  Portable demo only. Do not activate on production versions!
session_set(cookie,siteroot);
// Check if SSL (HTTPS) is available on this server, and if so switch to it...
sslcheck();

// Cache control moved here (7.5) to allow session check.. 
if(isset($_GET[editkey]) || gets('authenticated')=='1' ):
  define('browser_cache',0);
else:
  define('browser_cache',ini_browser_cache);
endif;

if (browser_cache) {
   define('jsindex','');
 }else{
   header('Cache-Control: max-age=10');
   define('jsindex','?' . rand(1,1000));
}

// if debug parameter is set OR you are authenticated, log php errors to file. 
if (gets('authenticated') || get('debug')){
  ini_set('error_log', dirname(__FILE__) . '/../log/php_error.log');
  ini_set('log_errors', 1);
  error_reporting(E_ALL);
}    

// if debug is set AND you are authenticated, show ajax transactions, etc.
// Otherwise, suppress php error output to screen. 
if (gets('authenticated') && get('debug')){
  define("debug",1,1);
}else{
  ini_set('display_errors', 0);
  define("debug",0,1);
//  define("debug",1,1);
}    

// Echo paths to page if in debug mode... 
showpaths();

// Allow plugins to execute. Mainly for benefit of eXtplorer. (no longer used)
// if (!defined('_VALID_MOS')) define( '_VALID_MOS', 1 );

return;


function sslcheck(){
/* Automatic SSL/HTTPS loader. 
   Must be called after session setup, but before any text output.
   Avoids need for messing with .htaccess settings, just make one change in siteini.php file  
   to convert your site to secure sockets mode. (Server capability permitting) 
   [security] use_https=0|1|2 
   0: Whichever mode the visitor requested
   1: HTTPS mode if an ssl test is passed
   2: Always use HTTPS
   3: Always use, even on HTTP page reload attempts. 
   
   First page loading is slightly faster with 2 (no test) but subsequent page loads are the same. 
   The test can provide a valuable fallback though in case of server-side (but not client-side) ssl trouble. 
   Now works with Cloudflare, where it overcomes the endless loop problem of .htaccess redirects. 
*/
    if (cms_use_https < 1) {
      return;
    }
    // Tests to avoid infinite loops - We must not try to redirect more than once per page request. 
    // If server reports https on, then do nothing...
    if (@$_SERVER["HTTPS"] == 'on' || $_SERVER['SERVER_PORT']==443) return;
    // For Cloudflare, check for special forwarding header. (Since https will be reported 'off' regardless.)  
    @$aph=apache_request_headers();
    if (is_array($aph)) {
       if (strlen(@$aph['X-Forwarded-Proto'])>4) return; 
    }
    // Find out what port is in use and build URL accordingly.. 
    if ($_SERVER["SERVER_PORT"] != "80") {
      $pageURL = $_SERVER["SERVER_NAME"].":".$_SERVER["SERVER_PORT"].$_SERVER["REQUEST_URI"];
    } else {
      $pageURL = $_SERVER["SERVER_NAME"].$_SERVER["REQUEST_URI"];
    }

    // Where sessions are available, we note the number of failed attempts and stop after 3. 
    // After 3 successful attempts we just use https anyway, no further testing. 

    $sslURL='https://' . $pageURL ;
    $https_status=(int)gets('https_status');

    // Don't redirect a second attempt to load the same page (Unless in mode 3): 
    $lastURL=gets('last_redirect');
    sets('last_redirect',$pageURL);
    if ($lastURL == $pageURL && cms_use_https < 3) {
     // echo "LR" . gets('last_redirect') . "PU" . $pageURL . "HS" . $https_status . "<br>";
     return;
    }

    if ($https_status > 2 || cms_use_https > 1) {
      header('Location: ' . $sslURL );
      exit;
    }
    if ($https_status < -2) {
      echo "<div style='background:red;color:white;'>There may be a problem with HTTPS on this server, or else too many reloads of the same page have been made. </div>";
      return;
    }

    $cms_dstime=ini_get('default_socket_timeout');
    if ($cms_dstime>10) $cms_dstime=10 ;
    ini_set("default_socket_timeout","02");
    $f=fopen($sslURL,"r");
    $r=fread($f,1000);
    fclose($f);
    ini_set("default_socket_timeout",$cms_dstime);
    if(strlen($r)>1) {
      $https_status+=1;
      sets('https_status',(string)$https_status);
      header('Location: ' . $sslURL );
      exit;
    } else {
      $https_status-=1;
      sets('https_status',(string)$https_status);
      return;
    }
}

function cms_hash($inputval) {
// Included for compatibilty with old user accounts...
  if (shalevel > 1) {
    return hash('sha256',$inputval);
  } else {
    return md5($inputval);
  }
}


function plugin($name){
  $pluginphp = siteroot . path(pluginbase) . $name . '/' . 'plugin.php' ;
  $fspluginphp = fsroot . path(pluginbase) . $name . '/' . 'plugin.php' ;
  $fspluginjs = fsroot . path(pluginbase) . $name . '/' . 'plugin.js' ;
  $pluginjs = siteroot . path(pluginbase) . $name . '/' . 'plugin.js' ;
  $fsplugincss = fsroot . path(pluginbase) . $name . '/' . 'plugin.css' ;
  $plugincss = siteroot . path(pluginbase) . $name . '/' . 'plugin.css' ;
  $pfound=false;
    if (file_exists($fsplugincss)){
      $pfound=true;
      echo "<link rel='stylesheet' href='$plugincss".jsindex."' type='text/css' >\n";
    }
  if (file_exists($fspluginjs)){
    $pfound=true;
    echo "<script type='text/javascript' src='$pluginjs".jsindex."'></script>\n";
  }
  if (file_exists($fspluginphp)){
    $pfound=true;
    return $fspluginphp;     
  }
  if ($pfound==false){
    if (debug||get('debug')) echo "<p style='color:red;'>Warning: Plugin <i>$name</i> failed to load.<p>";
  }
  return "" ;
}


function loadplugin($pluginname){
    if (strpos($pluginname,".")!==false) return false;
    if (debug && !file_exists(siteroot . $pluginname)) {
        echo "<small>[missing plugin $pluginname]</small>"; 
        return false;
    }
    $phpinclude=siteroot . pluginbase . '/' . pluginname . '/plugin.php';
    $jsinclude=siteroot . pluginbase . '/' . pluginname . '/plugin.js';
    if (file_exists($phpinclude)) include_once($phpinclude); 
    if (file_exists($jsinclude)) echo "<script src='$jsinclude'></script>";
}


function session_set($sname="maracms",$spath="/") {
  // Variables are by default placed in a sub-array to prevent clashes 
  // with any other software loaded. 
  $sname.="_session";
  if(session_id()=="") {
    if (!defined('SESSION_NAME')) define( 'SESSION_NAME', $sname);
    session_name(SESSION_NAME);
    $sessionCookieExpireTime=0;
    session_set_cookie_params($sessionCookieExpireTime,$spath);
    session_start();
  }
}

function setsitedefaults(){
// Read site settings from ini and put into constants. Use default if no ini value.  
// Note, we get the location relative to codebase because we don't yet have the system path info..
    $absfile=str_replace('\\','/',__FILE__);
    if (strpos($absfile,":/")==1 ) $absfile=substr($absfile,2,512);
    // Go two-up to get filesystem root of this website... 
    $fsroot= pathname($absfile,2) ;  
  $siteinifile = $fsroot . configdir . 'siteini.php' ;
  $asiteconfig=inireadarray($siteinifile) ;
  setconst($asiteconfig,'site','salt','shash','9553');
  setconst($asiteconfig,'site','shalevel','shalevel',0);
  setconst($asiteconfig,'site','sitename','sitename','Mara');
  setconst($asiteconfig,'site','cookie','cookie',sitename);
  setconst($asiteconfig,'site','theme','default_theme','theme');
  setconst($asiteconfig,'site','pluginbase','pluginbase','plugin');
  setconst($asiteconfig,'site','imgdir','imgdir','img/'); // not implemented
  setconst($asiteconfig,'site','default_extension','default_extension','php');
  setconst($asiteconfig,'site','browser_cache','ini_browser_cache',1);
  setconst($asiteconfig,'site','doctype','site_doctype','html');
  setconst($asiteconfig,'site','charset','ini_charset','utf-8');
  setconst($asiteconfig,'site','language','site_language','en');
  setconst($asiteconfig,'site','msie','site_msie','11');
  setconst($asiteconfig,'site','phjscss','site_phjscss',0);
  setconst($asiteconfig,'menu','mainmenu','mainmenu','tree.mnu');
  setconst($asiteconfig,'menu','quicklinkmenu','quicklinkmenu','quicklink.mnu');
  setconst($asiteconfig,'menu','menu_separator','menu_separator','=');
  setconst($asiteconfig,'menu','multiviews','multiviews',0);
  setconst($asiteconfig,'menu','sidemenu_autoclose','sidemenu_autoclose',0);
  setconst($asiteconfig,'menu','topmenu_timeout','topmenu_timeout',10);
  setconst($asiteconfig,'editing','add_shebang','add_shebang',1);
  setconst($asiteconfig,'editing','site_editors','site_editors',"");
  setconst($asiteconfig,'editing','site_editlevel','site_editlevel',1);
  setconst($asiteconfig,'editing','sourcecode_editlevel','sourcecode_editlevel',3);
  setconst($asiteconfig,'editing','inline_scripts','inline_scripts',4);
  setconst($asiteconfig,'editing','file_restore','file_restore',3);
  setconst($asiteconfig,'editing','admin_upload_any','admin_upload_any',0);
  setconst($asiteconfig,'editing','autostart','editor_autostart',0);
  setconst($asiteconfig,'uploading','smartsize','upload_smartsize','1280x1024');
  setconst($asiteconfig,'uploading','overwrite','upload_overwrite',1);
  setconst($asiteconfig,'uploading','align','upload_align','center');
  setconst($asiteconfig,'security','use_https','cms_use_https','0');
  setconst($asiteconfig,'security','editkey','editkey','login');
  setconst($asiteconfig,'security','keepalive','kato_keepalive',15);
  setconst($asiteconfig,'security','timeout','kato_timeout',45);
  setconst($asiteconfig,'security','harvesting_protection','harvesting_protection','2');
  setconst($asiteconfig,'security','allowed_ips','allowed_ips','');
  setconst($asiteconfig,'security','prohibited_ips','prohibited_ips','');

  // Mod to fix filename character encoding issue with PHP<7.1 on Windows servers.. 
  if (ini_charset=='native'){
     define('site_charset',mb_internal_encoding());
  }else{
     define('site_charset',ini_charset);
  }

}


function setconst(&$iniarray,$section="general",$key,$constant,$default=""){
  if (isset($iniarray[$section][$key])) {
      define($constant,$iniarray[$section][$key],1);
  }else{
      define($constant,$default,1);
  }
}

function gets($value, $context="mara"){
  // gets a session OR global parameter; exits gracefully if it does not exist. 
  if(session_id() != ""){ 
    if (isset($_SESSION[$context][$value])) return $_SESSION[$context][$value];
  }
    if (isset($GLOBALS[$context][$value])) return $GLOBALS[$context][$value];
  return null; 
}

function getg($value, $default=null, $context="mara"){
  // gets a global parameter; exits gracefully if it does not exist. 
    if (isset($GLOBALS[$context][$value])) {
        return $GLOBALS[$context][$value];
    }else{
        return $default;
    }     
}

function sets($value,$data, $context="mara"){
  // sets a global parameter; uses session if available. 
  if (session_id() != "") {
    $_SESSION[$context][$value]=$data;
  }
    $GLOBALS[$context][$value]=$data;
}

function setg($value,$data, $context="mara"){
  // Sometimes we don't want a variable to span pages, in which case we always use the global array. 
    $GLOBALS[$context][$value]=$data;
}

// These next are to prevent errors being thrown -or the need for repeated isset checks- 
// when submitted data might be null, and to allow binary safe POST variables. 

function get($parameter) {
 if (isset($_GET[$parameter])):
  return $_GET[$parameter];
 else:
  return ""; 
 endif;
}

function getp($parameter,$encoded=true) {
 if (!isset($_POST[$parameter])) return "";
  $data = str_replace(' ','+',$_POST[$parameter]);
  if ($encoded)$data=base64_decode($data,true); 
  return $data;
}

function getr($parameter) {
 if (isset($_REQUEST[$parameter])):
  return $_REQUEST[$parameter];
 else:
  return ""; 
 endif;
}

// Data conversion... 

function brbr2p($page) {
  // Convert pages using <br><br> para breaks to <p> for compatibility with CKEditor.
  // No need to enter </p> tags - CK will do that on first save.
    $pattern="~(<br>|<br />)[\r\n|\n]*(<br>|<br />)~siU";
    $replacement= "\r\n<p class='brbr'>\r\n";
    $page = preg_replace($pattern, $replacement, $page);
  return $page;
}

function taglist($html, $thistag, $tcondition=''){
   $offset=0; $tagstr=''; $ct=0;
   $voidtags='|<area>|<base>|<br>|<col>|<command>|<embed>|<hr>|<img>|<input>|<keygen>|<link>|<meta>|<param>|<source>|<track>|<wbr>|';
   while ($offset < strlen($html)):
   $ct++; if ($ct>100) break;
   $istagok=true;
   $otstart=stripos($html,'<'.$thistag,$offset);
   $nchar=substr($html,$otstart+strlen($thistag)+1,1);
   if ($nchar!='>'){
     if ($nchar!=' ') $istagok=false;
   }
   if ($otstart===false) break;
   $otend=stripos ($html,'>',$otstart+1);
   if (stristr($voidtags,$thistag)!==false) :
    $atagparts[1]=substr($html,$otstart,$otend +1 -$otstart);
    $atagparts[2]="";
    $atagparts[3]="";
    $atagparts[0]=$atagparts[1];
    $ctstart=$otend;
    $ctend=$otend;
   else :  
    if ($thistag=='body'){
      // More efficient since the body will usually take-up most of the file.. 
      $ctstart=strripos($html,'</body');
    } else {
      $ctstart=stripos($html,'</'.$thistag,$otend+1);
    }
    $ctend=stripos ($html,'>',$ctstart+1);
    if ($ctend < $otstart) $istagok=false;
     $atagparts[1]=substr($html,$otstart,$otend +1 -$otstart);
     $atagparts[2]=substr($html,$otend+1,$ctstart -1 -$otend);
     $atagparts[3]=substr($html,$ctstart,$ctend +1 -$ctstart);
     $atagparts[0]=$atagparts[1].$atagparts[2].$atagparts[3];
   endif;
   if ($ctend>$offset){
     $offset=$ctend;
   } else {
     break;
   }
   if ($istagok) {
     if (strlen($tcondition)==0 || strpos($atagparts[1],$tcondition)!==false) {
       $tagstr.= $atagparts[0] . "\n";
     }
   }else{
     // break;
   }
   endwhile;
 return $tagstr;
}


function tagdata($html, $thistag, $simulate=false, $newsemantics=false, $precursor=false){
 // Might be worth adding some more traps to this fn for broken HTML like a missing body close tag..  
 // New semantics were added to overcome length limit on PHP regexes.
 if (!$newsemantics) $istagok = preg_match("~(<".$thistag.".*>)(.*)(</".$thistag.".*>)~siU",$html,$atagparts);
 if ((preg_last_error() == PREG_BACKTRACK_LIMIT_ERROR) || $newsemantics ) {
   $istagok=0;
   $otstart=stripos($html,'<'.$thistag);
   $otend=stripos ($html,'>',$otstart+1);
   if ($thistag=='body'){
     // More efficient since the body will usually take-up most of the file.. 
     $ctstart=strripos($html,'</body');
   }else{
     $ctstart=stripos($html,'</'.$thistag);
   }
   $ctend=stripos ($html,'>',$ctstart+1);
   if ($ctend > $otstart) $istagok=1;
   if ($precursor){
     // Precursor is necessary for headsection insertion.
     $atagparts[1]=substr($html,0,$otend +1);
   }else{
     $atagparts[1]=substr($html,$otstart,$otend +1 -$otstart);
   }
   $atagparts[2]=substr($html,$otend+1,$ctstart -1 -$otend);
   $atagparts[3]=substr($html,$ctstart,$ctend +1 -$ctstart);
   $atagparts[0]=$atagparts[1].$atagparts[2].$atagparts[3];
 }
 
 // Allow for text pages containing no tags: 
 if (($istagok<1) and $simulate):
   $atagparts[1] = "<".$thistag.">";  
   $atagparts[2] = $html;
   /// was callingpage. changed as throwing error on siteground. 
   $atagparts[3] = "</".$thistag.">";
 endif;
 // prevent missing closing tags from throwing errors; 
 for ($ct=0;$ct<4;$ct++) {
    if (!isset($atagparts[$ct])) $atagparts[$ct]="";
 }
 $atagparts['status']=$istagok;
 return $atagparts;
}


function oldtagdata($html, $thistag, $simulate=false){
  // Has file length limitation, new version overcomes this. 
  $istagok = preg_match("~(<".$thistag.".*>)(.*)(</".$thistag.".*>)~siU",$html,$atagparts);
  // Allow for text pages containing no tags: 
  if (($istagok<1) and $simulate):
   $atagparts[1] = "<".$thistag.">"; 
   $atagparts[2] = $html;
   /// was callingpage. changed as throwing error on siteground. 
   $atagparts[3] = "</".$thistag.">";
  endif;
  // prevent missing closing tags from throwing errors; 
  for ($ct=0;$ct<4;$ct++) {
    if (!isset($atagparts[$ct])) $atagparts[$ct]="";
  }
return $atagparts;
}

function getmetas($contents)
{
    $result = false;
    if (isset($contents) && is_string($contents))
    {
        $title = null;
        $metaTags = null;
        preg_match_all('/<[\s]*meta[\s]*name="?' . '([^>"]*)"?[\s]*' . 'content="?([^>"]*)"?[\s]*[\/]?[\s]*>/si', $contents, $match);
        if (isset($match) && is_array($match) && count($match) == 3)
        {
            $originals = $match[0];
            $names = $match[1];
            $values = $match[2];
            if (count($originals) == count($names) && count($names) == count($values))
            {
                $metaTags = array();
                for ($i=0, $limiti=count($names); $i < $limiti; $i++)
                {
                    $metaTags[$names[$i]]['content'] =  $values[$i];
                    $metaTags[$names[$i]]['value'] =  $originals[$i];
                }
            }
        }
    }
    return $metaTags;
}


function frag($key, $thisstr, $nparts=3){
  $split=explode($key, $thisstr);
  for ($ct=0;$ct<=$nparts;$ct++) {
    if (!isset($split[$ct])) $split[$ct]="";
  }
 return $split;
}

function syspaths($mastercopy=1){

    // Should only be executed in a file in the codebase directory.
    // Can be included from anywhere.     
    // Note; pagefile is not necessarily the displayed webpage when calling from a system file!  
    
    /*
    Logistics: 
    Relative paths are used wherever possible in page content, to keep websites portable. 
    Relative paths do not start with slash. Absolute paths do. 
    For system folders, the system generates paths from the document root on the fly. 
    Directories always end with slash, whether relative or absolute. 
    */
    $rtnval="";
    
    // Strip Windows notation from this include's path, if present...
    $absfile=str_replace('\\','/',__FILE__);
    if (strpos($absfile,":/")==1 ) $absfile=substr($absfile,2,512);
    // Go two-up to get filesystem root of this website... 
    $fsroot= pathname($absfile,2) ;  
 
    // codebase gives the system folder name..  
    $syspath=explode("/", $absfile);
    // This will be the folder with the system scripts..  
    $codebase=path($syspath[count($syspath)-2]);
 
    $callingpage= $_SERVER['SCRIPT_NAME'];
    // Get the path to this webpage, as from Web root 
    // (not necessarily site root, note, as site may be in subdir of webspace)  
    $pagedir=pathname($callingpage);
    if ($pagedir=='.') $pagedir="";
    
    // Identify path from root of webspace to base folder of website. 
    // Works by comparing filesystem root with page dir, looking for contiguous set of directories. 
    // This method has to be used, because the document root variable returned by php is not always correct on shared hosting. 
    // Note that this mechanism may fail if the site has a chain of same-named subdirectories. (though this is very rare) 
    $apagedir=explode("/", $pagedir);
    $afsroot=explode("/", $fsroot);
    $pidx=0;
    $siteroot="";
    $insiteroot=false;
    foreach ($afsroot as $thisdir){
      if ($thisdir==$apagedir[$pidx]) {
        $pidx++;
        if ($thisdir=="") continue;
          $insiteroot=true;
          $siteroot = $siteroot . '/' . $thisdir;
      } else {
          if ($insiteroot) break; 
      }
    }
    $siteroot .= '/';

    $codedir=$siteroot . $codebase;
    $fsconfigdir= $fsroot . configdir ;
    $pagefile=ltrim(substr($callingpage,strlen($siteroot),255),'/');

    // vars common to all page instances;     
  if (!defined('siteroot')){
    // define ('themedir', $siteroot . theme . '/',1);
    define ('themebase','theme/');
    setg('siteroot',$siteroot);
    define('siteroot',$siteroot,1);
    setg('fsroot',$fsroot);
    define('fsroot',$fsroot,1);
    setg('codebase',$codebase);
    define('codebase',$codebase,1);
    setg('codedir',$codedir);
    define('codedir',$codedir,1);
    define('fsconfigdir',$fsconfigdir,1);
    setg('pagefile',$pagefile);
    define('pagefile',$pagefile,1);
    setg('pagedir',$pagedir);
    define('pagedir',$pagedir,1);
  }

   // Provide these vars to Javascript:
   // (To use, echo the returned string to the browser)
   // Possibly should be constants, not local vars? v6.
   $crlf='';
   // if (debug>0) $crlf="\n" ;
   $rtnval= "<script>$crlf";
   $rtnval.= "var siteroot='$siteroot';$crlf";
   $rtnval.= "var fsroot='$fsroot';$crlf";
   $rtnval.= "var codebase='$codebase';$crlf";
   $rtnval.= "var codedir='$codedir';$crlf";
   $rtnval.= "var pagefile='$pagefile';$crlf";
   $rtnval.= "var pagedir='$pagedir';$crlf";
   $rtnval.= "var imgdir='". imgdir ."';$crlf";
   $rtnval.= "var editor_autostart='". editor_autostart ."';$crlf";
   $rtnval.= "var upload_smartsize='". upload_smartsize ."';$crlf";
   $rtnval.= "var upload_overwrite='". upload_overwrite ."';$crlf";
   $rtnval.= "var upload_align='". upload_align ."';$crlf";
   $rtnval.= "var configdir='".configdir."';$crlf";
   $rtnval.= "var shalevel='".shalevel."';$crlf";
   $rtnval.= "var kato_keepalive='".kato_keepalive."';$crlf";
   $rtnval.= "var kato_timeout='".kato_timeout."';$crlf";
   $rtnval.= "var sourcecode_editlevel='".sourcecode_editlevel."';$crlf";
   $rtnval.= "var file_restore='".file_restore."';$crlf";
   $rtnval.= "var default_extension='".default_extension."';$crlf";
   $rtnval.= "ok2edit=false;$crlf";
   $rtnval.= "</script>$crlf";
    setg('js_syspaths',$rtnval);
    @define('js_syspaths',$rtnval,1);
}


function showpaths() {
    if (debug>1) {
    echo __FILE__ . ' file<br>';
    echo siteroot . ' siteroot<br>';
    echo fsroot . ' fsroot<br>';
    echo codebase . ' codebase<br>';
    echo codedir . ' codedir<br>';
    echo $callingpage . ' callingpage<br>';
    echo pagefile . ' pagefile<br>';
    echo pagedir . ' pagedir<br>';
    echo themedir . ' themedir<br>';
    echo imgdir . ' imgdir<br>';
    }
}

function pathname($inpath,$levels=1) {
// Better filename-strip function, minus idiosyncrasies (eg null=.) and able to strip n levels from path (1=strip filename) 
$thispath=rtrim($inpath,'/');
$ct=$levels;
 while ($ct>0) {
  $ct--;
  $lastfs=strripos($thispath,"/");
  $thispath=substr($thispath,0,$lastfs);  
 }
 if ((substr($inpath,0,1)=='/') && (substr($thispath,0,1)!='/')) $thispath= '/' . $thispath;
 if ($thispath!="" && substr($thispath, -1,1)!="/") $thispath .= '/';
 return $thispath;  
}

function path($loc){
// Rationalises pathnames to always end with /
 if ($loc!="") {
   if (substr($loc, -1,1)<>"/"){
     $loc .= '/';
   }
 } 
// echo ">".$loc. "|";
 return $loc;
}

function stripext($thispath, $exts=".htm|.html|.php") {
// Removes extension from local link, for multiviews linking.
   $dotpos=strripos($thispath,"."); 
   $rtnval=$thispath;
   if ($dotpos>0){
     $ext =  substr($thispath, $dotpos);
     if (stripos($exts,$ext)!==false){
       if (strpos($ext,"/")===false){
          $rtnval = substr($thispath,0,$dotpos) ;    
       }    
     }
   }
   return $rtnval;
}

function relroot($loc){
   $nslashes=substr_count(ltrim($loc,'/'),'/'); 
   // echo $loc .$nslashes;
   $rel=str_repeat('../',$nslashes) . $loc;
   return $rel;
}

function unwinpath($absfile){
   $absfile=str_replace('\\','/',$absfile);
   if (strpos($absfile,":/")==1 ) $absfile=substr($absfile,2,512);
   return $absfile;
}

function alt_thisfsdir($callingfile,$rtn_echo=false){
  $callingpage = $_SERVER['SCRIPT_NAME'];
  $fscallingpage = unwinpath($_SERVER['SCRIPT_FILENAME']);
  $absfile=unwinpath($callingfile);
  $fsrootdiff=strlen($fscallingpage)-strlen($callingpage);
  $rtn=pathname(substr($absfile,$fsrootdiff));  
  if ($rtn_echo==1) echo $rtn;
  if ($rtn_echo==2) echo "var thispage='$rtn'";
  if ($rtn_echo==3) echo "<script>var thispage='$rtn'</script>";
return $rtn;
}   

function thisfsdir($callingfile,$rtn_echo=false){
  $absfile=unwinpath($callingfile);
  $rtn=pathname(siteroot.substr($absfile, strlen(fsroot)),1);
  if ($rtn_echo==1) echo $rtn;
  if ($rtn_echo==2) echo "var thispage='$rtn'";
  if ($rtn_echo==3) echo "<script>var thispage='$rtn'</script>";
return $rtn;
}   

function thisdir($callingpage,$rtn_echo=false){
  $rtn=pathname(siteroot.substr($callingpage, strlen(fsroot)),1);
  if ($rtn_echo==1) echo $rtn;
  if ($rtn_echo==2) echo "var thispage='$rtn'";
  if ($rtn_echo==3) echo "<script>var thispage='$rtn'</script>";
return $rtn;
}    


/*  ******** ini file handling ************  */

function inireadarray($path, $section='*') {
if (!is_array($path)) {
  if(!file_exists($path)) return false;
  $fdata=file($path,FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
} else {
  $fdata=$path;
}
   if ($fdata==null) return false; 
  $inisection="-"; $ct=0; $array=array(); // php7 ;
  foreach ($fdata as $thisline) { 
    $thisline=trim($thisline," \t");
    if ($thisline=="") continue; 
    if (substr($thisline,0,1)=="<") continue; 
    if (substr($thisline,0,1)==";") continue; 
    if (substr($thisline,0,1)=="[") {
      $inisection=trim($thisline,"[]");
      $array[$inisection]["section_name"]=$inisection;
    } else {
      $item=explode("=",$thisline);
      if (!isset($item[1])) $item[1]="";
      $ct++;
      
      if ($section=='*') {
          $array[$inisection][$item[0]]=$item[1];
      } else {
          if ($section==$inisection) $array[$item[0]]=$item[1];
      }
              
    }
   }
 return $array;
}

function iniwritearray($array, $pathname) {
  if (!is_array($array)) return false; 
  $writestring=""; $inisection="-"; 
  if (pathinfo($pathname,PATHINFO_EXTENSION)=='php') $writestring.="; <?php exit ?> Retain this line to prevent viewing of file in browser.\n";
  foreach ($array as $inisection=>$inisectionval) { 
    if (isset($array[$inisection]['is_deleted'])) continue; 
    if ($inisection!='-') $writestring.="[".$inisection."]\n";  
    foreach ($inisectionval as $item=>$value) { 
      $writestring.="$item=$value\n";
    }
  } 
   if (!$handle = fopen($pathname, 'w')) { 
        return false; 
    } 
    if (!fwrite($handle, $writestring)) { 
        return false; 
    } 
    fclose($handle); 
    return true; 
  }


/*  ********* Menu Code *********** */
// include_once(plugin('menu'));


function ipcheck(){
$ip=clientip();
$ipn=ip2long($ip);
$ipok=255;

if (strlen(allowed_ips)>0){
  $ipok=0;
  if (trim(allowed_ips)=="*") {
      $ipok=3;
  }else{
    $aallowed=explode(",",allowed_ips);
    foreach ($aallowed as $thisrange) {
      if (strpos($ip,$thisrange)===0) $ipok=2;
      if (debug>2 )echo "IP: $thisrange :  $ipok<br>";
    }
  }
}
if (strlen(prohibited_ips)>0){
  if (trim(prohibited_ips)=="*") {
      $ipok=0;
  }else{
    $aprohibited=explode(",",prohibited_ips);
    foreach ($aprohibited as $thisrange) {
      if (strpos($ip,$thisrange)!==false) $ipok=0;
      if (debug>2 )echo "Prohibited_IP: $thisrange :  $ipok<br>";
    }
  }
}

// if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE) !== true) $ipok=1;

  if ($ipok==0){
   // nonsense error message to confuse foreign intruder..    
   die ('Parameter must be numeric, not string. Invalid function call in /libs/pear/loader.php, line 3423.');
  }
 if (get('debug')>2) echo "Client IP is: " . $ip . " " . $ipn. " " . $ipok;
}

function clientip() {
    foreach (array('HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_X_FORWARDED', 'HTTP_X_CLUSTER_CLIENT_IP', 'HTTP_FORWARDED_FOR', 'HTTP_FORWARDED', 'REMOTE_ADDR') as $key)
    {
        if (array_key_exists($key, $_SERVER) === true)
        {
            foreach (array_map('trim', explode(',', $_SERVER[$key])) as $ip)
            {
                if (filter_var($ip, FILTER_VALIDATE_IP) !== false)
                {
                    return $ip;
                }
            }
        }
    }
}

function widgets(){
  thememenu();
  printbutton();
}


function thememenu() {
   // creates expanding list of themes.. 
   $out= "<button type='button' id='themebutton' class='widget' title='Show available styling themes' onClick='showThemeSelector(true);'>
   <img src='".codedir."img/view.png' alt='+'></button>" ;
   $out.='<div id=\'thememenu\' onClick=\'showThemeSelector(false)\' title=\'Select a theme to view this site in. The theme determines the layout and styling of this website. The choice will apply to all pages until you close the browser or leave the site for a while. The active theme is '. theme .'. \'>';

   $out.= "<span>" ; // <button type='button' class='widget'  onClick='showThemeSelector(false);'>
//   <img src='".codedir."img/view.png' alt='+'></button>" ;

   $dcontents = scandir(fsroot.themebase);
   natsort($dcontents);
   $numthemes=0;
   foreach ($dcontents as $thisitem) {
     if(substr($thisitem,0,1)==".") continue;
     $thisclass = (stristr($thisitem, theme)!==false) ? "class='active_theme' " : "" ;
     if (!is_dir(fsroot.themebase.$thisitem)) continue;
     $out.="<a href='".siteroot.pagefile."?theme=". $thisitem ."' $thisclass>" . ucwords($thisitem) . "</a>";
     $numthemes++;
   }
   $out.='</span></div>';
   if ($numthemes>1) echo $out;
}


// retained as sep. function for backward compatibility..
function printbutton($icon="print.gif"){
  // Simpler aproach using css3 only, but not so effective ..
  //  echo '<a href="javascript:window.print()" title="Print page content" class="widget"><img alt="print" src="'. themedir . 'menu_img/'. $icon .'" border=0></a>';
   echo '<a href="'. siteroot . pagefile .'?style=print" title="Print page content" target="printout" class="widget"><img alt="print" src="'. themedir . 'menu_img/'. $icon .'" style="border:none;"></a>';
}    


function getuseragent($agent=null) {
  // Return the name and version of the client browser 
  $known = array('msie', 'firefox', 'safari', 'webkit', 'opera', 'netscape',
    'konqueror', 'gecko', 'seamonkey', 'iron', 'chrome');
  $agent = strtolower($agent ? $agent : $_SERVER['HTTP_USER_AGENT']);
  $pattern = '#(?P<browser>' . join('|', $known) .
    ')[/ ]+(?P<version>[0-9]+(?:\.[0-9]+)?)#';
  if (!preg_match_all($pattern, $agent, $matches)) return array();
  $i = count($matches['browser'])-1;
//  echo $matches['browser'][$i];
  setg('browser_name',$matches['browser'][$i]);
  $thisversion=$matches['version'][$i];
  if ($thisversion=='') $thisversion='unknown';
  setg('browser_version',$thisversion);  
//  echo getg('browser_version');
}


function multicolumn($thesecontainers="div.multicolumn",$columns=2,$spacing='10px', $reversionpoint='800px') {
// 5.3 responsive column count introduced. 
// echo getg('browser_name') .'|'. getg('browser_version')  ;
  if (getg('browser_name')=="msie" ){ 
   if (getg('browser_version')>8) {
    echo <<<EOF
    <style>
    $thesecontainers {
      column-count:$columns;
      column-gap:$spacing;
      -moz-column-count:$columns; 
      -webkit-column-count:$columns; 
      -moz-column-gap:$spacing; 
      -webkit-column-gap:$spacing; 
    } 
    @media screen and (max-width: $reversionpoint) {
     $thesecontainers {
       column-count:1;
       column-gap:$spacing;
       -moz-column-count:1; 
       -webkit-column-count:1; 
       -moz-column-gap:$spacing; 
       -webkit-column-gap:$spacing; 
     } 
    }
    </style>
EOF;
   }else{
     echo "<br><i><sub>Please note: Your browser is too old to display this page in multi-column format.</sub></i>";
   }
  }else{
   echo <<<EOF
   <style>
   $thesecontainers {
    -moz-column-count:$columns; 
    -webkit-column-count:$columns; 
    -moz-column-gap:$spacing; 
    -webkit-column-gap:$spacing; 
   } 
   @media screen and (max-width: $reversionpoint) {
    $thesecontainers {
      -moz-column-count:1; 
      -webkit-column-count:1; 
      -moz-column-gap:$spacing; 
      -webkit-column-gap:$spacing; 
    } 
   }
   </style>
EOF;
  }
}

function remove_shebang($pagedata){
   $pagedata=preg_replace('~^<\?php\s*?include_once.*?codebase/reflex\.php.*?\s*\?>\s*~si','',$pagedata,1);
   return $pagedata;
}

function cms_truncate($string, $limit) {
    // Limits length of string to maxchars, trimming back to last whole word
    // $string = strip_tags($string); //Strip HTML tags off the text
    // $string = html_entity_decode($string); //Convert HTML special chars into normal text
    $string = str_replace(array("\r", "\n"), "", $string); //Also cut line breaks
    if(mb_strlen($string, "UTF-8") <= $limit) return $string; //If input string's length is no more than cut length, return untouched
    $last_space = mb_strrpos(mb_substr($string, 0, $limit, "UTF-8"), " ", 0, "UTF-8"); //Find the last space symbol position
    return mb_substr($string, 0, $last_space, "UTF-8").' ...'; //Return the string's length substracted till the last space and add three points
}


?>
