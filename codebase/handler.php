<?php
require_once 'core.php';
include_once 'resizer.php';
error_reporting(E_ALL);
ini_set('display_errors', '1');

if (stripos($_SERVER['HTTP_REFERER'],'dir.php?') || stripos($_SERVER['HTTP_REFERER'],'quick.php')){
 if (count($_POST)==0){
  if ($_SERVER['CONTENT_LENGTH']/(1024*1024) > (int)ini_get('post_max_size'))  {
    echo "<b>Bad request, probably due to oversize upload. No files saved.</b><br>";
    echo '<script>window.parent.uploader.upload.value="Start Upload";</script>';
    exit;
  }
 }
}

$ajax_action=getp('action');
// actions which don't require prior authentication..(must have exit as last cmd)
if ($ajax_action=="setsalt"):
 setsalt();
 exit;
endif;

if ($ajax_action=="loginform"):
 ?>
 <form name="login" enctype="none" enctype="application/x-www-form-urlencoded" method="post" action="">
 <input class="inputbox" type="text"  name="usr"  size="20" maxlength="60" value="" >usr<br>
 <input class="inputbox" type="text"  name="pwd"  size="20" maxlength="60" value="" >pwd<br>
 <input type="button" class="button" name="login" value="Login" onClick="javascript:sendLogin()">
 </form></br>');
<?php
 exit;
endif;

if ($ajax_action=="login"):
 login(true);
 exit;
endif;

if ($ajax_action=="logout"):
// ajaxecho("logoutroutine");
 login(false);
 exit;
endif;

if ($ajax_action=="keepalive"):
 if (kato_keepalive<1){
   ajaxecho("OK: No keepalive set.");
   exit; 
 }
 if (gets('authenticated')<1){
   ajaxecho("Fail: Keepalive sent after timeout or whilst not logged in.");
   exit;
 }
 if ((gets("last_keepalive")>0) && (kato_timeout>0)) {
   if (time() > (gets("last_keepalive") + kato_timeout)) {
    ajaxecho("Fail: Session timeout due to loss of client ping.");
    sets('authenticated','0');
    session_destroy();
    exit;
   }
 }
 sets("last_keepalive",time());
 ajaxecho("OK: Keepalive ping accepted.");
 exit;
endif;

// below this point all actions require prior authentication...
if ( gets('authenticated')!=='1' ): ajaxecho("Not logged in, probably due to inactivity timeout. You need to log in again from the page being edited, and then refresh this window."); exit; endif; 

if ($ajax_action=="encode_scripts"):
 script_encoder(getp('data'),'encode');
 exit;
endif;

if ($ajax_action=="decode_scripts"):
 script_encoder(getp('data'),'decode');
 exit;
endif;

if ($ajax_action=="getheadsection"):
 getheadsection();
 exit;
endif;

if ( chkpwd(gets('pwd'),gets('usr'))!==true ): ajaxecho("Fail, incorrect login."); exit; endif; 
// if ( chkpwd(getp('pwd'),getp('usr'))!==true ): ajaxecho("Fail, wrong credentials sent with request. If this page was opened in a previous editing session, you probably need to hit your' browser's Reload/Refresh button. "); exit; endif; 
/// Problem with this in ajax2. 


if (gets('user_privelege')<1): ajaxecho("Fail, user has insufficient rights."); exit; endif;

logentry($ajax_action);

if ($ajax_action=="dirlist"):
 dirlist();
endif;

if ($ajax_action=="menutest"):
 menutest();
endif;

if ($ajax_action=="menusave"):
 if (gets('user_privelege')>5): ajaxecho("Sorry. Menu alterations are not permitted in demo mode."); exit; endif;
 if (gets('user_privelege')<3): ajaxecho("Fail, user has insufficient rights."); exit; endif;
 save('text');
endif;

if ($ajax_action=="upload"):
//  if (gets('user_privelege')>5): ajaxecho("Sorry. File uploading is not permitted in demo mode."); exit; endif;
  if (gets('user_privelege')>5): echo("<h2 style='font-family:sans-serif'><br>Sorry. File uploading is not permitted in demo mode. <br> Please choose one of the existing files on the webserver.</h2>"); exit; endif;
  if (gets('user_privelege')<2): echo("Fail, user has insufficient rights."); exit; endif;
 upload();
endif;

if ($ajax_action=="quick_image"):
 if (gets('user_privelege')>5): echo("<h2 style='font-family:sans-serif'><br>Sorry. Image uploading is not permitted in demo mode. <br> Please choose one of the provided images on the tabs above.</h2>"); exit; endif;
 if (gets('user_privelege')<1): echo("Fail, user has insufficient rights."); exit; endif;
 quick_image();
endif;

if ($ajax_action=="ckupload"):
 if (gets('user_privelege')>5): ajaxecho("Error: Sorry. Image uploading is not permitted in demo mode." ); exit; endif;
 if (gets('user_privelege')<1): ajaxecho("Error: User has insufficient rights, or is not logged on."); exit; endif;
 ckupload();
endif;

if ($ajax_action=="getfile"):
 getfile();
endif;

if ($ajax_action=="getbodysection"):
 getfile('body');
 exit;
endif;

if ($ajax_action=="info"):
 info();
endif;

if ($ajax_action=="save"): 
 if (gets('user_privelege')>5): ajaxecho("Sorry. Page Save is not permitted in demo mode."); exit; endif;
 save('page');
endif;

if ($ajax_action=="preview"):
 // This one is for whole page preview from CK toolbar button. 
 if (gets('user_privelege')<1): ajaxecho("Sorry. Page Preview is not permitted in demo mode, because it might allow the uploading of malicious scripts."); exit; endif;
 save('preview_page');
endif;

if ($ajax_action=="delete"):
  if (gets('user_privelege')>5): ajaxecho("Sorry. This action is not permitted in demo mode."); exit; endif;
  // delete();
endif;

if ($ajax_action=="dir"):
 dir();
endif;

if ($ajax_action=="filecopy"):
 if (gets('user_privelege')>5): ajaxecho("Sorry. This action is not permitted in demo mode."); exit; endif;
 filecopy();
endif;

if ($ajax_action=="filenew"):
 if (gets('user_privelege')>5): ajaxecho("Sorry. This action is not permitted in demo mode."); exit; endif;
 filenew();
endif;

if ($ajax_action=="getpreview"):
 // this one is for file restore AND CK link dialog preview. 
 filerestore('preview');
 exit;
endif;

if ($ajax_action=="filerestore"):
 if (gets('user_privelege')>5): ajaxecho("Sorry. Actual restores are not permitted in demo mode."); exit; endif;
 filerestore('exec');
endif;

if ($ajax_action=="userupdate"):
 if (gets('user_privelege')>5): ajaxecho("Sorry. Changes to user rights are not permitted in demo mode."); exit; endif;
 if (gets('user_privelege')<5): ajaxecho("Fail, user has insufficient rights."); exit; endif;
 userupdate();
endif;


function login($status) {
 if ($status!==true) {
     sets('nacl', "");
     sets('usr', "");
     sets('pwd', "");
     sets('authenticated','0');
     setg('authenticated','0');
     setg('user',"");
     setg('pwd',"");
     setg('ok2admin',false);
     session_name(SESSION_NAME);
     $_SESSION = array(); session_write_close();
  //   session_destroy();
     ajaxecho('User logged out');
     return true;
 }
 $ajaxusr=getp("usr");
 $ajaxpwd=getp("pwd");
 $loginstatus=chkpwd($ajaxpwd,$ajaxusr);
  if ($loginstatus && (gets('user_privelege')>0)) {
     sets('usr', $ajaxusr);
     sets('pwd', $ajaxpwd);
     sets('authenticated','1');
     sleep(gets('logindelay'));
     sets('logindelay',"0");
     ajaxecho("OK:" . gets('user_privelege'));
  } else {
     sets('user_privelege',0);
     sets('authenticated','0');
     sets('pwd',"");
     // tarpit bruteforce attempts.. 
     $logindelay=gets('logindelay')+1;
     $_SESSION = array(); session_write_close();
     session_start(); sets('logindelay',$logindelay); 
     sleep($logindelay);
     ajaxecho("Login Fail. Bad username or password, or account not active. Attempt " . $logindelay);
     return false;
  }
  return true;
}

function chkpwd($thispwd, $thisuser="") { 
// gets hash from userdata file and compares with submitted pwd. 
// userini.php is actually an ini file, but with a php noexecute command to prevent peeking. 
  $userinifile= fsconfigdir . 'userini.php' ;
//  ajaxecho($userinifile);
  if ($thisuser=="") return false;
  $auserdata=inireadarray($userinifile, $thisuser) ;
  if( $auserdata==false ) {ajaxecho("Fail: No user data found.") ; return false;}
  $pwdhash=$auserdata['pwd'];
  $pwdshash=cms_hash($pwdhash . gets('nacl'));
  if ($thispwd == $pwdshash) {
    sets('user_active',$auserdata['privelege']);
    sets('user_privelege',$auserdata['privelege']);
    sets('user_directories','*');
    return true;
  } else {
    return false;
  }
}


function setsalt() {
    $nacl=rand(1000,9999);
    sets('nacl',$nacl);
    ajaxecho($nacl); 
}

function info() {
    ajaxecho($_PHP_SELF); 
}

function dirlist() {
$thisroot=getp('srcfile');
$contents = scandir($thisroot);
 $dirlist="";
 $filelist="";
 foreach ($contents as $thisdir) {
  $dir=is_dir($thisdir);
  if ($dir) {
    $dirlist = dirlist . "<br><a href='javascript:selectdir($thisdir);'>$thisdir</a>\n";
  } else { 
    $filelist = filelist . "<br><a href='javascript:selectdir($thisdir);'>$thisdir</a>\n";
  }
 }

$ihtml = "<table><tr><td name='lh'>";
$ihtml .= $dirlist;
$ihtml .= "</td><td name='rh'>";
$ihtml .= $filelist;
$ihtml .= "</td></tr></table>";

echo $ihtml;
}

function script_encoder($data="", $action='unspecified'){
  if ($action=='encode'){
    // Convert script tags to placeholders.. 
    $script_count=0;
    $phptags=array('<?php','?>','<script','</script');
    $edittags=array('<textarea class="cms_script" title="Inline php script. Double-click to edit" readonly="readonly"><?php','?></textarea>','<textarea class="cms_script" title="Inline js script. Double-click to edit." readonly="readonly"><script','</script></textarea');
    $data=str_ireplace($phptags,$edittags,$data,$script_count); 
    setg("script_count",$script_count);
   }
  if ($action=='decode'){
    // reconvert script textareas back to scripts...
    setg("script_count",0);
    // Reconvert script placeholders (with or without para tags)..
    $data = preg_replace_callback('~(<p><textarea.*cms_script.*>)(.*)(<\/textarea.*></p>)~siU',
    'cb_script_restore', $data,255);
    $data = preg_replace_callback('~(<textarea.*cms_script.*>)(.*)(<\/textarea.*>)~siU',
    'cb_script_restore', $data,255);
  }
 ajaxecho($data);
 return true;
}

function cb_script_restore($matches){
     setg("script_count",getg("script_count")+1);
     return str_ireplace("&#39;","'",html_entity_decode($matches[2]));
}

function script_restore($matches){
     setg("script_count",getg("script_count")+1);
     return str_ireplace("&#39;","'",html_entity_decode($matches[2]));
}

function save($datatype="text"){
/*
  data type text or binary = do not add html headers from original file.
  get crc and check
  get destfile
  save data
*/  
 //determine how many archive copies of each webpage are saved.. 
 $maxarcdepth=10; 
 $savinfo="";
 $data = getp('data');
 $enccrc=getp('enccrc',false);
// paths should be relative to site root.  
 $destfile=gets('fsroot') .  getp('destfile');
 $srcfile=gets('fsroot') .  getp('srcfile');
 $datacrc=cms_hash(getp('data',false));
// do checks for dir traversal here.. 
 if ((stristr($srcfile,"..")!==false) || (stristr($destfile,"..")!==false)) : 
   ajaxecho("Fail, directory traversals not allowed"); return false ;
 endif;
 // ajaxecho($enccrc . "---" . $datacrc );
 if ($datacrc!==$enccrc) {
   ajaxecho("Bad upload, file NOT saved. Reason: Data checksum fail. The last good online copy has been retained. ") ;
   return false;
 }
 
 if (stristr($datatype,"page")!==false) {

 // Block accidental save of protected page..
                   
 if (strstr($data,"cms_h1_msg_edit_lock")!==false){
    ajaxecho("Page NOT saved! \n You cannot save this page because you did not have sufficient priveleges when opening it for editing.") ;
    return false;
 }

 // reconvert script textareas back to scripts...
  setg("script_count",0);
  if (inline_scripts){
  // Reconvert script placeholders 
  $data = preg_replace_callback('~(<p><textarea.*cms_script.*>)(.*)(<\/textarea.*></p>)~siU',
  'script_restore', $data,255);
  $data = preg_replace_callback('~(<textarea.*cms_script.*>)(.*)(<\/textarea.*>)~siU',
  'script_restore', $data,255);
  } 

  if ($srcfile!=""){
    $callingpage=file_get_contents($srcfile);
    if ($callingpage=="") {ajaxecho("Page source file failed to open"); return false; }
    // Strip any existing shebang from headsection (since file may have been relocated) 
    if (add_shebang!=0){
      $callingpage=remove_shebang($callingpage);  
//    $callingpage=preg_replace('~^<\?php\s*include_once.*codebase/reflex\.php.*\s*\?********>\s*~siU','',$callingpage,1);
      /*    Old method... 
      $line1end=strpos($callingpage,"\n");
      $line1=substr($callingpage,0,$line1end); 
      $tline1=trim($line1);
      if (substr($tline1,0,5)=='<?php'){
        if (strlen($tline1)<101) {
          if (strpos($tline1,'/reflex.php')>0){
            if (strpos($tline1,'?>')>0) $line1end=strpos($tline1,'?>')+2;
            $callingpage=substr_replace($callingpage,"",0,$line1end);
          }
        }
      }
     */
    }
   
     // Add head and foot sections from original page;
     $nbody=0;
     $nbody = preg_match("/(^.*<body.*>)(.*)(<\/body.*>.*$)/siU",$callingpage,$aouter);
     // Less memory-intensive way of getting body tags now used.. 
     //     $aouter=tagdata($callingpage, 'body', false, true, true, true);   
     //     $nbody=$aouter['status'] ;
     $nhead = preg_match("/(.*<head.*>)(.*)(<\/head.*>)(.*)/siU",$callingpage,$aheader);
     $newhead=getp('headsection');
     if ($newhead!=""){
     // insert a modified head section where headsection is already present in file (but not otherwise) 
       if ($nhead==1){
// logentry('nhead passsed');

         $findstr = $aheader[1].$aheader[2].$aheader[3];
         $repstr= $aheader[1].$newhead.$aheader[3];
         $aouter[1]=str_replace($findstr,$repstr,$aouter[1]);
         // allow for damaged body section repair.. 
         $aheader[2]=$newhead;
       }     
     }
// logentry($aouter[1]);

     if ($nbody==1){
       // if page has body tags, place edited data between them and save..  
       $data = $aouter[1] . "\n" . $data . "\n" . $aouter[3];
     }else{
       // (earlier..) $nhead = preg_match("/(.*<head.*>)(.*)(<\/head.*>.*)/siU",$callingpage,$aheader);
       // if page has a head section but malformed body tags, prepend the head section and repair.. 
       if ($nhead==1){
         $data = '<html><head>' . "\n" . $aheader[2] . "\n" . "</head><body>" . "\n" . $data . "\n" . "</body></html>";
       }
     }
    
    //  includes body tags.. possibly better method but as yet untested. 
    //  if ((stripos($callingpage,"<body")!==false) && (stripos($callingpage,"</body")!==false)):
    //    $data = preg_replace("~(<body.*>)(.*)(<\/body.*>)~siU",'${1}'.$data.'${3}',$callingpage,1);
    //  endif; 
    // ajaxecho ($data);
    //  exit;
   if (strlen($data)<1) {ajaxecho("No data to save" . $data); return false; }
  }
 // Was end of datatype page section.. 

// Check page editing permissions in case user has changed since page load.. 
// (Should never come into play, but guards against XSS or similar user escalation)
$ahead=tagdata($callingpage,'head', false);
$ametas=@getmetas($ahead[2]);
$cms_editlevel=site_editlevel;
$cms_editors=site_editors;
if (isset($ametas['cms_editlevel']['content'])) {
 $cms_editlevel=$ametas['cms_editlevel']['content'];
}
if (isset($ametas['cms_editors']['content'])) {
 $cms_editors=$ametas['cms_editors']['content'];
}
   $specificeditor=false;
    if (strlen($cms_editors)>0){
      $acms_editors=explode(',',$cms_editors);
      foreach ($acms_editors as $thisguy) {
        if (gets('usr')==trim($thisguy)){
          $specificeditor=true;
        }
      }
      // If editor ID not found and NO edit level set, bar all except admins.     
      if ($cms_editlevel<1) $cms_editlevel=5;
      unset($acms_editors);
    }  
    if (!$specificeditor){
     if (gets('user_privelege')< $cms_editlevel){
       ajaxecho("You do not have sufficient priveleges to save this page.");
       return false;
     }
    }
  
  if (getg("script_count")>0){
   if (gets('user_privelege')< inline_scripts){
     if (gets('authenticated')){
       if (!$specificeditor){
         ajaxecho("You do not have sufficient priveleges to save this (script enabled) page.");
         return false;
       }
     }else{
       ajaxecho("You need to authenticate yourself in order to save pages");
       return false;
     }
   }
  }

// end user rights check 

// Harvesting protection..

$rxemail='([\w|\.|\-|\_]+?)@([\w|\.|\-\_]+?)';
if (harvesting_protection>0) {
  $harvestct=0;
  $data = preg_replace_callback("/$rxemail/siU", 'munge_email', $data,255,$harvestct);
  if (harvesting_protection>1){
    if ($harvestct>0){ 
      ajaxecho("Page NOT saved! \n This page contains email addresses which are vulnerable to robotic harvesting. \v Web publication has been inhibited to protect these addresses from being collected by junkmail operators. Please edit the page to remove or protect the vulnerable items.") ;
      return false;
    }
  }
}

} // end page datatype section


// make hierarchical backup...

if (stristr($datatype,"preview")===false){
 $arcdir=gets('fsroot') . 'undo/' . pathname(getp('destfile'));
 // if (file_exists($destfile) && is_dir($arcdir)) {
 $savinfo="";
 if (file_exists($destfile))  {
   $pparts = pathinfo($destfile);
   $arcfile= $pparts['basename'];
   $ct=$maxarcdepth;
   if (!is_dir($arcdir)) {
    mkdir($arcdir, 0770, true);
   }
   while ($ct>0) {
     $harcfile=$arcfile . '_' . ($ct);
     $larcfile=$arcfile . '_' . ($ct-1);
     if (file_exists($arcdir . $larcfile)) rename($arcdir . $larcfile, $arcdir .  $harcfile);
     $ct--;
    }
 // ajaxecho ("Test: $arcdir$arcfile|$harcfile|$larcfile");exit;

    if (file_exists($arcdir . $arcfile)) rename($arcdir . $arcfile, $arcdir .  $arcfile . '_2');
    copy($destfile, $arcdir . $arcfile); 
    $savinfo="Previous state saved to: " . str_replace(gets('fsroot'),"",$arcdir) . $arcfile;
 }
}

   // Tidy up improper user input as far as possible.. 
   $abadlocations=array('codebase','theme','img','log','plugin','undo');
   $destfilename=getp('destfile');
   $destfilename = ltrim(str_replace('\\','/',$destfilename),'/');
   $destrootdir=substr($destfilename,0,strpos($destfilename,'/'));
   if (@in_array($destrootdir,$badlocations)==true) { 
     ajaxecho("Bad location. Saving of webpages to that location is not permitted." );
     return;
   }

   // Add shebang if config requires it...  
   // determine relative path to reflex.php from destination file.. 
   if (add_shebang>0 && stristr($datatype,"page")!==false) {
     $pathprefix=pathname($destfilename);
     $pathprefix=str_replace('//','/',$pathprefix);
     $nslashes=substr_count($pathprefix,'/');
     $pathprefix=str_repeat('../',$nslashes);
     $reflexcall="<?php include_once '".$pathprefix."codebase/reflex.php' ?>\n";
     //   ajaxecho("rc ".$reflexcall);return;
     $data = $reflexcall . $data;
   }


 if (stristr($datatype,"preview")!==false){
   // Generate preview temp file. Add code to prevent preview hiding in background:
   if (stristr($destfile,"~preview.php")===false){
     // safeguard against other files being saved via preview mechanism..
     ajaxecho('Fail: prohibited filename');
     return false;
   }
   $fh = fopen($destfile, 'w') or die("Error: can't open destination file" . $destfile);
   $dataout="<script>window.setInterval(function(){if (!document.hasFocus()){window.close()};},10000);</script>";    
   fwrite($fh, $data . $dataout);
   fclose($fh);
   ajaxecho('OK: ' . $savinfo); 
   // sleep(5);
   // unlink($destfile);
   return true;
 }

 // Save live page...
 $fh = fopen($destfile, 'w') or die("Error: can't open destination file" . $destfile);
 fwrite($fh, $data);
 fclose($fh);
 ajaxecho('OK: ' . $savinfo);  
 return true;
}

function munge_email($matches){
     return "|".$matches[1].'**at**'.$matches[2]."|";
     // return str_ireplace("&#39;","'",html_entity_decode($matches[2]));
}

function upload() {
  echo "<b>Processing file upload request...</b><br><small>Please be patient, may take a while. <br>Do not close this window whilst upload is in progress. </small><br>";
  // note: not an ajax call so can use any parameters. Do not use ajaxecho() 
  // output goes directly to file info iframe. 
  //  ini_set('max_file_size','10M');
  //  ini_set('max_files','100');
  // ini_set('max_file_uploads','100');

  if (getp('destdir',0) != '') {
    $desired_dir=gets('fsroot') . getp('destdir',0);
  } else {
    $desired_dir=gets('fsroot') . 'img' ;
  }
  echo '<link rel=stylesheet href="system.css" type="text/css"><link rel=stylesheet href="dialog.css" type="text/css">';
  echo '<script>window.parent.uploader.upload.value="Processing..";</script>';
  echo "Destination : " . $desired_dir. "<br>";
    ?>
    <style>
    body {
    text-decoration:none;
    font-family:sans-serif,Arial,Helvetica;
    }
    </style>
    <?php
 
  if(isset($_FILES['files'])){
    if (strlen($_FILES['files']['name'][0])<1){echo $_FILES['files']['name'][0]."<br>No files selected; nothing to do.<br>"; return; }
    $errors=0;$count=0;$error=false;
  	foreach($_FILES['files']['tmp_name'] as $key => $tmp_name ){
   		$file_name = $_FILES['files']['name'][$key];
  		$file_size =$_FILES['files']['size'][$key];
  		$file_tmp =$_FILES['files']['tmp_name'][$key];
  		$file_type=$_FILES['files']['type'][$key];	
   		// echo "Processing : $file_name<br>";
        $thisext = strtolower(substr(strrchr($file_name, '.'), 1));
        if (gets('user_privelege')!=5  || admin_upload_any<1 ) {
        // if (getp('type',0)=='images'){
          // exclude hidden files.. 
          if(substr($file_name,0,1)=="." ) {
            $errors++; $error=true;
            echo "Error: $file_name - Hidden files prohibited. <br>";
            continue;
          }
          // Limit extensions to doc/image/vid/aud types.. 
          $filetypes=array('htm','html','php','txt','jpg','jpeg','gif','png','flv','mp4','mp3','avi','mkv','pdf','swf');
          if(!in_array($thisext,$filetypes) ) {
            $errors++; $error=true;
            echo "Error: $file_name - Only image/video/sound filetypes allowed. <br>";
            continue;
          }
          // Bar uploads to system locations.. 
          $badlocations='|codebase|theme|log|plugin|sitecfg|undo|'.pluginbase.'|';
          $file_dir=trim(substr($desired_dir,strrpos($desired_dir,'/')),'/');
          if (stripos($badlocations,'|'.$file_dir.'|')>0) { 
            echo "Error: $file_name - Only Admin users may upload to system areas <br>";
            continue ; 
          }
        }
        if ($file_size==0 || $file_name==""){ 
  			 $errors++; $error=true;
  			 echo "Error: $file_name - File size is zero, or name is blank. Possibly an oversize upload<br>";
             continue;
        }		
        if($file_size > 10485760){
  			 $errors++; $error=true;
  			 echo "Error: $file_name - File size must be less than 10 MB<br>";
             continue;
        }		
        if (!$error):
          $count++;
          if(empty($errors)==true){
               // echo '<br>'.getp('type',0) .'asdf'. getp('downsizing',0).'<br>';
               if ((getp('type',0)=='images') && (getp('downsizing',0)!="")){
                    // image resizing module;
                     $imgdimensions=explode('x',getp('downsizing',0));
                    // echo "$imgdimensions[0],$imgdimensions[1]";
                    imgmaxsize( $file_tmp,$imgdimensions[0],$imgdimensions[1]);
                }
              if(is_dir($desired_dir)==false){
               mkdir($desired_dir, 0755);		// Create directory if it does not exist
              }
              if(is_dir("$desired_dir/".$file_name)==false){
                 move_uploaded_file($file_tmp,$desired_dir . '/' . $file_name);
              }else{									//rename the file if another one exist
                  $new_dir=$desired_dir . '/' .$file_name.time();
                   rename($file_tmp,$new_dir) ;				
              }
              $url=$desired_dir . '/' . $file_name;
              echo "OK: $file_name uploaded.<br>";
          }else{
              echo "Error: Upload of $file_name failed.<br>"; 
              print_r($errors);
          }
        endif;
      }
  	if ($count==0){ echo "No files to save, therefore no action taken"; return;}
  	echo '<script>window.parent.uploader.upload.value="Completed";</script>';
  	if(empty($error)){
    echo  "Files saved to: $desired_dir<br>";
  		echo "All files processed successfully";
  	} else {
      echo "Upload not fully completed, $error errors encountered";
  	}
    Echo '<p>Press <input type=button value="Reload Menu Tree" class="button" onClick="window.parent.location.reload(true);"/> (or Ctrl-R) to see changes, then select a file from the lefthand tree.</p>';
  }
  echo '<script>window.parent.uploader.upload.value="Start Upload";</script>';
}

function quick_image() {
  echo "<b>Processing file upload request...</b><br><small>Please be patient, may take a while. <br>Do not close this window whilst upload is in progress. </small><br>";
  // note: not an ajax call so can use any parameters. Do not use ajaxecho() 
  // output goes directly to file info iframe. 
  //  ini_set('max_file_size','10M');
  //  ini_set('max_files','100');
  // ini_set('max_file_uploads','100');

  echo '<link rel=stylesheet href="system.css" type="text/css"><link rel=stylesheet href="dialog.css" type="text/css">';
  echo '<script>parent.document.uploader.upload.value="Processing..";</script>';
  $hostpage=getp('hostpage',0);
  $hostpage_namepart=stripext(basename($hostpage));
 // $hostpage_namepart=str_replace('/','_',$hostpage_namepart);
  $desired_dir= pathname(getp('hostpage',0)). 'img';
  echo "Destination : " . $desired_dir. "<br>";
    ?>
    <style>
    body {
    text-decoration:none;
    font-family:sans-serif,Arial,Helvetica;
    }
    </style>
    <?php
 
  if(isset($_FILES['files'])){
    if (strlen($_FILES['files']['name'][0])<1){echo $_FILES['files']['name'][0]."<br>No file selected; nothing to do.<br>"; return; }
    $errors=0;$count=0;$error=false;
  	foreach($_FILES['files']['tmp_name'] as $key => $tmp_name ){
   		$file_name = $_FILES['files']['name'][$key];
  		$file_size =$_FILES['files']['size'][$key];
  		$file_tmp =$_FILES['files']['tmp_name'][$key];
  		$file_type=$_FILES['files']['type'][$key];	
   		// echo "Processing : $file_name<br>";
        if (getp('type',0)=='images'){
	      $thisext = strtolower(substr(strrchr($file_name, '.'), 1));
          // exclude hidden files.. 
          if(substr($file_name,0,1)=="." ) {
            $errors++; $error=true;
            echo "Error: $file_name - Hidden files prohibited. <br>";
            continue;
          }
          // Limit extensions to image/vid/aud types.. 
          $filetypes=array('jpg','jpeg','gif','png');
          if(!in_array($thisext,$filetypes) ) {
            $errors++; $error=true;
            echo "<p style='color:red;font-weight:bold;'>Error: $file_name - Only Web image filetypes allowed. <br>These are typically files with a jpg, jpeg, png or gif extension.</p>";
            continue;
          }
        }		
        if($file_size > 10485760){
  			 $errors++; $error=true;
  			 echo "Error: $file_name - File size must be less than 10 MB<br>";
        }		
        if (!$error):
          $count++;
          if(empty($errors)==true){
               // echo '<br>'.getp('type',0) .'asdf'. getp('downsizing',0).'<br>';
               if ((getp('type',0)=='images') && (getp('downsizing',0)!="")){
                    // image resizing module;
                     $imgdimensions=explode('x',getp('downsizing',0));
                    // echo "$imgdimensions[0],$imgdimensions[1]";
                    imgmaxsize( $file_tmp,$imgdimensions[0],$imgdimensions[1]);
                }
              $desired_pathname=$desired_dir . '/' . $hostpage_namepart . '_' . $file_name;
              echo "DPN:" . $desired_pathname .'<br>';
              $fileidx=1;
              if(is_dir(gets('fsroot') . $desired_dir)==false){
               mkdir(gets('fsroot') . $desired_dir, 0755);		// Create directory if it does not exist
              }
              if((file_exists(gets('fsroot') . $desired_pathname)==true) && (getp('overwrite')=="")){
                    echo "Generating new filename as file already exists.. <br>";
                    while (1){
                        $fileidx++;
                        $idxfilepath= $desired_dir . '/' . $hostpage_namepart . '_' . $fileidx . '_' . $file_name;
                        if(file_exists(gets('fsroot') .$idxfilepath)==false) {
                          $desired_pathname=$idxfilepath;
                          break; 
                        } 
                        if ($fileidx>1000) break;
                    }                      
              }
              if ($fileidx<1000) move_uploaded_file($file_tmp,gets('fsroot') . $desired_pathname);
/*
desired_dir is location of opener file plus /img (can NOT be sent from browser)   
get rel dir and filename of opener
get filename and extension of uploaded image
Prefix hostpage_name and _ to filename
If file already exists AND overwrite=false, add index to hostpage_name and try again  
*/ 
              $img_url=$desired_pathname;
              $img_siteurl=siteroot.$img_url;
              echo  "OK: $file_name uploaded.<br>";
              // temp  fix, hardcoded img dir to overcome page-relative issues. *************
            //  echo "http://".$_SERVER['HTTP_HOST']."|||".$_SERVER['REQUEST_URI'];
              echo '<script>parent.document.uploader.upload.value="Start Upload";</script>';
              echo  "<script> parent.document.uploader.webpathname.value='img/".basename($img_url)."'; </script>";
              echo  "<script> document.location='".$img_siteurl."';</script>";
          }else{
              echo "Error: Upload of $file_name failed.<br>"; 
              print_r($errors);
          }
        endif;
      }

  }

}


function getfile($action='all',$mode='binary') {
 $srcfile=gets('fsroot') . getp('srcfile');
 // do checks for dir traversal here.. 
 if (stristr($srcfile,"..")!==false ) { 
   ajaxecho("Error: directory traversals not allowed"); return false ;
 }
   $filecontent=file_get_contents($srcfile);
 if ($filecontent===false) {
   ajaxecho("Error: " . $srcfile . "Not found or empty.");
   return false;
 }
 $thiscontent="Invalid command to ajax file fetcher"; 
 if ($action=='all'){
   $thiscontent=$filecontent;
 }
 if ($action=='head'){
   $thiscontent=tagdata($filecontent,'head', false);
   $thiscontent=$thiscontent[2];
 }
 if ($action=='body'){
   $thiscontent=tagdata($filecontent,'body', true);
   $thiscontent=$thiscontent[2];
 }
 if ($action=='preview'){

   $srcfile=gets('fsroot') . getp('srcfile');
   $destfile=gets('fsroot') .  getp('destfile');

   mkdir(dirname($destfile), 0777, true); //added 2.2
   $result=copy($srcfile,$destfile);
  if ($result) {
    ajaxecho("OK");
  } else {
    ajaxecho("Error - Unable to create $destfile");
  }

   // Body, but with additional filtering.
   $thiscontent=tagdata($filecontent,'body', true);
   $thiscontent=$thiscontent[2];
   $thiscontent="<html><body>".$thiscontent."</body></html>";
   
 }
 ajaxecho($thiscontent);
 return true;
}

function getheadsection() {
 $srcfile=gets('fsroot') . getp('srcfile');
 // do checks for dir traversal here.. 
 if (stristr($srcfile,"..")!==false ) { 
   ajaxecho("Error: directory traversals not allowed"); return false ;
 }
   $filecontent=file_get_contents($srcfile);
 if ($filecontent===false) {
   ajaxecho("Error: " . $srcfile . "Not found or empty.");
   return false;
 }
 $thishead=tagdata($filecontent,'head', false);
 $thishead=$thishead[2];
 ajaxecho($thishead);
 return true;
}

function ajaxecho($returndata, $base64=true) {
  // echo $returndata;
  $encoding="";
  if (is_array($returndata)){
    $encoding='+++';
    $returndata = json_encode( $returndata );
  }
  if ($base64)  $returndata = base64_encode($returndata);
  $returndata = $encoding . '~::~' . $returndata . '~::~';
//  $rtnsize=strlen($returndata);
//  header("Connection: close");
//  header("Content-Length: $rtnsize");
  echo $returndata;
  return $returndata;
}

function menutest() {
 // Menu displays in default css, linked pages display in active theme. 
 if (!defined('themedir')) define('themedir',siteroot.default_theme.'/');
 ?>
 <!doctype html><body>
 <link rel="stylesheet" href="<?php echo codedir ?>system.css" type="text/css"> 
 <div class='sidemenu' style='max-width:98%'>
 <?php  
 include_once plugin('menu');
 // displays menu in iframe for checking.. 
 $activepage=0;
 sidemenu(getp('data'),"string,test");
 echo '</div></body></html>';
 return;
}

function filecopy() {
  
   if (gets('user_privelege')<3){
       if (stripos(getp('destfile'),"/")>1){
           ajaxecho("Error: Only advanced users may create new pages outside the default folder");
           return;
       }   
   }
   // paths should be relative to site root.  
   $destfile=gets('fsroot') .  getp('destfile');
   $srcfile=gets('fsroot') . getp('srcfile');
  if (file_exists($destfile)){
    ajaxecho("Error - The file $destfile already exists.");
    return;
  }
   @mkdir(dirname($destfile), 0777, true); //added 2.2
   $result=copy($srcfile,$destfile);
  if ($result) {
    ajaxecho("OK");
  } else {
    ajaxecho("Error - Unable to create $destfile");
  }
  
}

// ****************************************************************

function filenew() {
  // Similar to copy, but adds reflex loader line. 
   if (gets('user_privelege')<3){
       if (stripos(getp('destfile'),"/")>1){
           ajaxecho("Error: Only advanced users may create new pages outside the default folder");
           return;
       }   
   }
   // Paths should be relative to site root.  
   // Tidy up improper user input as far as possible.. 
   $badlocations='|codebase|theme|img|log|plugin|sitecfg|template|undo|';
   $destfilename=getp('destfile');
   $destfilename = ltrim(str_replace('\\','/',$destfilename),'/');
   $destrootdir=substr($destfilename,0,strpos($destfilename,'/'));
   if (strpos($badlocations,'|'.$destrootdir.'|')>0) { 
     ajaxecho("Bad location. Saving of webpages to that location is not permitted." );
     return;
   }
   $destfile=gets('fsroot') . $destfilename ;
   $srcfile=gets('fsroot') . getp('srcfile');
   if (file_exists($destfile)){
     ajaxecho("Error - The file $destfile already exists.");
     return;
   }
   $filecontent=file_get_contents($srcfile);
   if ($filecontent===false) {
     ajaxecho("Error: " . $srcfile . "Not found or empty.");
     return false;
   } 
   @mkdir(dirname($destfile), 0777, true); //added 2.2

   // Strip previous shebang, since it may refer to a different location.. 
   if (add_shebang!=0){
    $filecontent=remove_shebang($filecontent);
//  $filecontent=preg_replace('~^<\?php\s*include_once.*codebase/reflex\.php.*\s*\?*******>\s*~siU','',$filecontent,1);
    /* Old method.. 
     $line1end=strpos($filecontent,"\n");
     $line1=substr($filecontent,0,$line1end); 
     $tline1=trim($line1);
     if (substr($tline1,0,5)=='<?php'){
       if (strlen($tline1)<101) {
         if (strpos($tline1,'/reflex.php')>0){
           if (strpos($tline1,'?>')>0) $line1end=strpos($tline1,'?>')+2;
           $filecontent=substr_replace($filecontent,"",0,$line1end);
         }
       }
     }
   */
   }
   // .. and add new shebang if config requires it.. 
   if (add_shebang>0){ 
     $pathprefix=pathname($destfilename);
     $pathprefix=str_replace('//','/',$pathprefix);
     $nslashes=substr_count($pathprefix,'/');
     $pathprefix=str_repeat('../',$nslashes);
     $reflexcall="<?php include_once '".$pathprefix."codebase/reflex.php' ?>\n";
     //   ajaxecho("rc ".$reflexcall);return;
     $filecontent = $reflexcall . $filecontent; 
   }
   $fh = fopen($destfile, 'wb') or die("Error: can't open destination file" . $destfile);
   $result=fwrite($fh, $filecontent );
   fclose($fh);
  
  if ($result) {
    ajaxecho("OK");
  } else {
    ajaxecho("Error - Unable to create $destfile");
  }
  
}


// *************************************************************************

function filerestore($action='preview') {
  // Also serves as preview mechanism since actions are similar. 
  // when previewing a non-restore filename should be in original dir except when template. 
  
  if (getp('srcfile')!=getp('destfile')) {ajaxecho("Error: Malformed restore command."); return false;}
 // $arcdir=fsroot . 'undo/' . pathname(getp('srcfile'));
 // paths should be relative to site root.  
 $errmsg=false;
 $srcfn=getp('srcfile');
 $destfn=getp('destfile');
// if (substr($srcfn,0,9)=="template/") $destfn=null;
 if ($srcfn=="index.php") $errmsg="You may not restore this file";
 if (substr($srcfn,0,5)!="undo/") {
  if ($action=='exec'){
   ajaxecho ('This is not a rollback copy of a file');
   return; 
  }
 }else{
   // This is a rollback copy of a file, so strip the undo/ dir prefix
   $destfn=substr($srcfn,5);
 }
 $destext="";
 if (substr($srcfn,0,9)=="template/"){
   $prevfn='~preview.php';
 }else{
   $prevfn=pathname($destfn) . '~preview.php';
 }
 // Check for existence of _ after last . that denotes extension section.
 $extpos=strrpos($destfn,".");
 $uspos=strrpos($destfn,"_");
 if ($uspos<$extpos+1) $uspos=false;
 // $uspos=strrpos($destfn,"_");
 if ($uspos!==false)$destfn=substr($destfn,0,$uspos);
 if ($extpos!==false)$destext=substr($destfn,$extpos);
 $redofn='undo/' .$destfn. "_unrestore";
 if (!is_dir(pathname(fsroot . $destfn))) $errmsg="Destination directory does not exist";

 if ($action=='preview') $destfn=pathname($destfn). '~preview.php';

// if ($errmsg!=""){ajaxecho("Error: " . $errmsg); return false;}
// ajaxecho($srcfn.'|'.$destfn.'|'.$redofn);
 if (gets('user_privelege')<3){
   if (stripos($destfn,"/")>0){
     if ($action!='preview'){
       ajaxecho("Error: Only advanced users may create or restore files outside the default folder");
       return 0;
     }
   }   
 }
 $redostatus="";
 if ($action=='exec'){
  if ($redofn!=$srcfn){
   $result=copy(fsroot . $destfn,fsroot . $redofn);
   if (!$result){
    $redostatus="No backup of the version replaced was made, possibly the file has been deleted.";
   }else{
    $redostatus="A backup of the version replaced was made as " . $redofn;
   }
  }
 }

 if ($action=='preview') {
   // ajaxecho ("qwerty".$destext); ******************destext incorrect in template folder regardless of filetype. !!!
   if (stripos("|.php|.htm|.html|.template|",$destext)!==false ){
     // html files are loaded from the default location for the sake of links... 
     $result=copy(fsroot . $srcfn,fsroot . $prevfn);
   } else if (stripos("|.gif|.png|.jpg|.jpeg|",$destext)!==false ) {
     // For images, just return the location rel to site root..  
      ajaxecho("file:///" . $srcfn );
      return 1;  
   } else if (stripos("|.txt|.mnu|",$destext)!==false ) {
     // text files need not be resaved, just return contents by ajax..
     $fcontents="<textarea readonly=readonly style='width:100%;height:100%;'>" . htmlspecialchars(file_get_contents(fsroot . $srcfn)) . "</textarea>";
     ajaxecho($fcontents);
     return 1;
   } else {
     ajaxecho('No preview available for this file type');
     return 1;
   }
 }else{
   $result=copy(fsroot . $srcfn,fsroot . $destfn);
 }

 if ($result) {
  if ($action=='preview'){
   // echo back the location of the preview file to be loaded in the browser..
   ajaxecho("file:///" . $prevfn );
   return 1;  
  }
  if ($redofn!=$srcfn){
   ajaxecho("OK: Previous version of file reinstated. " . $redostatus );
  }else{
   ajaxecho("OK: Previous file restore was undone by replacing the file with " . $redofn);
  }
 } else {
   ajaxecho("Error: - Unable to over-write $destfn. Possibly you do not have modify permissions to the file, or the file may be in-use by another process.");
 }
 return 1;  
}

// ***********************************************************************

function userupdate() {
 $data = getp('data');
 $crc=getp('crc');
 $datacrc=cms_hash($data);
 $newshash="";
 if ($datacrc!==$crc) {
  ajaxecho("Bad upload, file NOT saved. Reason: Data checksum fail. The last good online copy has been retained. ") ;
  return false;
 }
$userinifile= fsconfigdir . 'userini.php' ;
$outfile= fsconfigdir . 'userini.php' ;
$sitefile=fsconfigdir . 'siteini.php' ;

$existingusers=inireadarray($userinifile);
// Remove MSIE weirdness..
$sanitised_data = str_replace("\r\n","\n", getp("data"));
$adata=preg_split("~\n~",$sanitised_data);
$newusers=inireadarray($adata);
$isoneadmin=false;

  foreach ($newusers as &$thisuser){
    $username=$thisuser['section_name'];
    if ($thisuser['privelege']==-1){
      $thisuser['is_deleted']=true;    
      continue; 
    }
//     if ($username=='user')  ajaxecho($username .'|'. $thisuser['pwd'] .'|'.$existingusers[$username]['pwd']);

   if (isset($thisuser['shash']))  {
     if ($newshash==""){
       $newshash=$thisuser['shash'];  
       unset($thisuser['shash']);
       $thisuser['salt']=rand(5, 515);
     }
   }

   if ((strstr($thisuser['pwd'],"-:set:-") !== false) || (strlen($thisuser['pwd'])<1) ) {   
      $thisuser['pwd']=$existingusers[$username]['pwd'];
//     if ($username=='user')  ajaxecho($username .'|'. $thisuser['pwd'] .'|'.$existingusers[$username]['pwd']);
//       ajaxecho($username .'|'. $thisuser['pwd'] .'|'.$existingusers[$username]['pwd']);
    }
    if ($thisuser['privelege']==5){
      $isoneadmin=true;    
    }
    if ($thisuser['pwd']=="" || $thisuser['privelege']<1 ) {
      // deactivate users with blank passwords or no priveleges..
      $thisuser['active']=0;
    } else {
      $thisuser['active']=1;
    }
//   if ($username=='user')  ajaxecho($username .'|'. $thisuser['pwd'] .'|'.$existingusers[$username]['pwd']);

  }

if (!$isoneadmin) {
      ajaxecho("Error: At least one user must be an Administrator, or you will lock yourself out! Update rejected."); 
      return false;
}

$addinfo='';
if ($newshash!=''){
 $asitedata=inireadarray($sitefile);
 $asitedata['site']['salt']=$newshash;
 $result=iniwritearray($asitedata,$sitefile); 
 $addinfo="\n A new encryption salt of $newshash has been set in sitecfg.php. \n As the active user's password has been updated, you may need to refresh the main window and log in again. ";
 if (!$result)  {
   ajaxecho("Error - Unable to update siteini.php");
   return false; 
 }
}


$result=iniwritearray($newusers,$outfile); 

 if ($result) {
    ajaxecho("OK - User accounts updated in: " . $outfile . $addinfo );
 } else {
    ajaxecho("Error - Unable to create userfile");
 }

}

function logentry($thisaction = "undefined") {
  $logfile = fsroot . "log/" . @date('ymd') . "_log.txt";
  date_default_timezone_set('UTC');
  $logline = date('h:i') . ' ~' . ' User: ' . gets('usr') . ' Src: ' . getp('srcfile') . ' Dest: ' . getp('destfile') .' Action: '. $thisaction ."\n" ;
  $fh = fopen($logfile, 'a');
  if (!$fh) return ;
  fwrite($fh, $logline);
  fclose($fh);
}

?>


