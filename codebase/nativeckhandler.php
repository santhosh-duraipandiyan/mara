<?php

exit;

// This is for the default CK upload dialog. Not currently implemented as we went the drag and drop route instead. 
 
require_once 'core.php';
include_once 'resizer.php';
//error_reporting(E_ALL);
//ini_set('display_errors', '0');

$ajax_action=getp('action');
$ajax_action="upload";
// actions which don't require prior authentication..(must have exit as last cmd)

// below this point all actions require prior authentication...
if ( gets('authenticated')!=='1' ): ajaxecho("Not logged in, probably due to inactivity timeout. You need to log in again from the page being edited, and then refresh this window."); exit; endif; 

/*
!!! Re-enable once testing complete **************************
if ( chkpwd(gets('pwd'),gets('usr'))!==true ): ajaxecho("Fail, incorrect login."); exit; endif; 
if ( chkpwd(getp('pwd'),getp('usr'))!==true ): ajaxecho("Fail, wrong credentials sent with request. If this page was opened in a previous editing session, you probably need to hit your' browser's Reload/Refresh button. "); exit; endif; 
if (gets('user_privelege')<1): ajaxecho("Fail, user has insufficient rights."); exit; endif;


*/

if ($ajax_action=="upload"):
  // Presently only action needed for CK dialogs.
  if (gets('user_privelege')>5): ajaxecho("Sorry. File uploading is not permitted in demo mode."); exit; endif;
  if (gets('user_privelege')<2): ajaxecho("Fail, user has insufficient rights."); exit; endif;
 ckupload();
endif;

if ($ajax_action=="delete"):
  if (gets('user_privelege')>5): ajaxecho("Sorry. This action is not permitted in demo mode."); exit; endif;
  // delete();
endif;


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



function ckupload() {
cklog('In Function');
  // echo "<b>Processing file upload request...</b><br><small>Please be patient, may take a while. <br>Do not close this window whilst upload is in progress. </small><br>";
  // note: not an ajax call so can use any parameters. Do not use ajaxecho() 
  // output goes directly to file info iframe. 
  //  ini_set('max_file_size','10M');
  //  ini_set('max_files','100');
  // ini_set('max_file_uploads','100');

  //echo '<link rel=stylesheet href="system.css" type="text/css"><link rel=stylesheet href="dialog.css" type="text/css">';
  //echo '<script>parent.document.uploader.upload.value="Processing..";</script>';
  $hostpage=getp('hostpage',0);
  $hostpage_namepart=stripext(basename($hostpage));
 // $hostpage_namepart=str_replace('/','_',$hostpage_namepart);
  $desired_dir= pathname(getp('hostpage',0)). 'img';
  //echo "Destination : " . $desired_dir. "<br>";
 
  if(isset($_FILES['upload'])){
    if (strlen($_FILES['upload']['name'])<1){ajaxecho ("No file selected; nothing to do."); return; }
    $errors=0;$count=0;$error=false;
//  	foreach($_FILES['upload'] as $key => $tmp_name ){
   		$file_name = $_FILES['upload']['name'];
  		$file_size =$_FILES['upload']['size'];
  		$file_tmp =$_FILES['upload']['tmp_name'];
  		$file_type=$_FILES['upload']['type'];	
   		// echo "Processing : $file_name<br>";
        if (getp('type',0)=='images'){
	      $thisext = strtolower(substr(strrchr($file_name, '.'), 1));
          // exclude hidden files.. 
          if(substr($file_name,0,1)=="." ) {
            $errors++; $error=true;
            ajaxecho ("Error: $file_name - Hidden files prohibited.");
            continue;
          }
          // Limit extensions to image/vid/aud types.. 
          $filetypes=array('jpg','jpeg','gif','png');
          if(!in_array($thisext,$filetypes) ) {
            $errors++; $error=true;
            ajaxecho ("<p style='color:red;font-weight:bold;'>Error: $file_name - Only Web image filetypes allowed. <br>These are typically files with a jpg, jpeg, png or gif extension.</p>");
            continue;
          }
        }		
        if($file_size > 10485760){
  			 $errors++; $error=true;
  			 ajaxecho ("Error: $file_name - File size must be less than 10 MB<br>");
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
              //echo "DPN:" . $desired_pathname .'<br>';
              $fileidx=1;
              if(is_dir(gets('fsroot') . $desired_dir)==false){
               mkdir(gets('fsroot') . $desired_dir, 0755);		// Create directory if it does not exist
              }
              if((file_exists(gets('fsroot') . $desired_pathname)==true) && (getp('overwrite')=="")){
                    //echo "Generating new filename as file already exists.. <br>";
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
            cklog("Upload: " . $file_tmp .' To ' . gets('fsroot') . $desired_pathname . "ov" . getr('overwrite'));
            jsrtn(1,$file_name,$desired_pathname, "File :".$desired_pathname);
            // jsrtn(0,"foo.txt","foo.txt","Test entry");
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
              //echo  "OK: $file_name uploaded.<br>";
              // temp  fix, hardcoded img dir to overcome page-relative issues. *************
            //  echo "http://".$_SERVER['HTTP_HOST']."|||".$_SERVER['REQUEST_URI'];
              //echo '<script>parent.document.uploader.upload.value="Start Upload";</script>';
              //echo  "<script> parent.document.uploader.webpathname.value='img/".basename($img_url)."'; </script>";
              //echo  "<script> document.location='".$img_siteurl."';</script>";
          }else{
              //echo "Error: Upload of $file_name failed.<br>"; 
              //print_r($errors);
          }
        endif;
      }

//  }

}


function jsecho($msg) {
cklog($msg);
}


function jsrtn($status, $fname, $pname, $msg=''){

if ($status >0){
  $response='{"uploaded": '.$status.',
    "fileName": "'.$fname.'",
    "url": "'.$pname.'",
    "message": "xkcd",

    "error": {
      "message": "'.$msg.'"
             }
  }';
}else{
  $response='{"uploaded": '.$status.',
    "error": {
      "message": "Upload failed. '.$msg.'"
             }
  }';
}    
   
echo $response;
}


function ajaxecho($returndata) {
 cklog($returndata);
 jsrtn(0,'','',$returndata);
}


// ***********************************************************************

function cklog($le){
  $logfile = fsroot . "log/ckhandler.txt" ;
  $fh = fopen($logfile, 'a');
   fwrite($fh, $le . "\n");
  fclose($fh);
}

?>


