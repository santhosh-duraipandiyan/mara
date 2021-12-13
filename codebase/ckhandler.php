<?php

 require_once 'core.php';
// include_once 'resizer.php';
 error_reporting(E_ALL);
 ini_set('display_errors', '1');
 $ajax_action=getp('action');

cklog('Decoded chunk Size: '. strlen(getp('chunk')));

// below this point all actions require prior authentication...

if ( gets('authenticated')!=='1' ): ajaxecho("Not logged in, probably due to inactivity timeout. You need to log in again from the page being edited, and then refresh this window."); exit; endif; 

if ( chkpwd(gets('pwd'),gets('usr'))!==true ): ajaxecho("Fail, incorrect login."); exit; endif; 
if ($ajax_action=="ckupload"):
 if (gets('user_privelege')>5): ajaxecho("Error: Sorry. Image uploading is not permitted in demo mode." ); exit; endif;
 if (gets('user_privelege')<2): ajaxecho("Error: User has insufficient rights, or is not logged on."); exit; endif;
 ckupload();
endif;

if ($ajax_action=="image_downsize"):
 if (gets('user_privelege')>5): ajaxecho("Error: Sorry. Image uploading is not permitted in demo mode." ); exit; endif;
 if (gets('user_privelege')<2): ajaxecho("Error: User has insufficient rights, or is not logged on."); exit; endif;
 image_downsize();
endif;


exit;

/*

New file uploader: 

Handles single files. Multiple uploads now handled by calling routine as avoids server file limit. 

Accepts and returns path/filename relative to webpage. Prepending fsroot.basename(pagefile) gives server filesystem path.
For security reasons, absolute paths or .. are not accepted. 

*/

// ************************************************************************************


function ckupload() {

// Total size of file regardless of chunking.. 
$maxuploadsize='120E6';

$destfile=getp('destfile');
$pagefile=getp('pagefile');
$overwrite=getp('overwrite');
$mimetype=getp('mimetype');
$tasks=getp('tasks');
$rtnval=''; 

cklog('Uploader - tasks: ' . $tasks . ' chunksize: ' . strlen(getp('chunk',0)) . ' CRC sent ' . getp('ccrc'));

          if (strlen(getp('ccrc'))>0){
            $crc= cms_hash(getp('chunk',0),shalevel);
            if ($crc != getp('ccrc')) {
              if (strlen(getp('chunk',0))>0) {
                cklog('CRC Error - Calculated: ' . $crc . ' | Sent: '. getp('ccrc') . ' | SHA: '. shalevel);
                ajaxecho("Error: crc fail"); return ;
              } else {
                cklog('Error - CRC sent, but no data');
                ajaxecho("Error:null data"); return ;
              }  
            }
          }

          // Need to address no destfile situation... 
          if ($destfile=='') { 
            ajaxecho ('error: No destination file.'); 
            return;
          }

          // exclude hidden files.. 
          if(substr($destfile,0,1)=="." ) {
            $error=true;
            ajaxecho ("Error: ".$destfile." - Hidden files prohibited.");
            return;
          }

          if (substr($destfile,0,1)=='/' || substr($destfile,0,1)=='\\') { 
             $destfile=substr($destfile,1);
          }
          
          
          if (strstr($destfile,"..")!==false ) { 
            ajaxecho("Error: directory traversals not allowed"); return false ;
          }
      
     
        if(strlen(getp('chunk')) > $maxuploadsize ){
  			 $error=true;
  			 ajaxecho ("Error: $destfile - File must be less than " . $maxuploadsize . " bytes");
         return;
        }	
        
        // Set server-side filename.. 
        $fspathname=fsroot . pathname(getp('pagefile')). $destfile;
        $fsdestdir=pathname($fspathname);

      
 if (stristr($tasks,'open')!==false){
        if(is_dir($fsdestdir)==false){
          mkdir($fsdestdir, 0755);		// Create directory if it does not exist
        }

      
        if((file_exists($fspathname)==true) && ($overwrite==false)){
        // Generates indexed filename if overwrite is not allowed. 
        // Might be an idea to CRC the existing file and not create a second copy if it is identical. 
              $fileidx=1;
              while (1){
                  $fileidx++;
                  $extpos=strrpos($destfile,'.');
                  if ($extpos===false) $extpos=strlen($destfile);
                  $lpath=substr($destfile,0,$extpos);
                  $rpath=substr($destfile,$extpos);
                  $idxfilepath=fsroot . pathname(getp('pagefile')). $lpath . '_' . $fileidx . $rpath;
                  if(file_exists($idxfilepath)==false) {
                    $fspathname=$idxfilepath;
                    $destfile = $lpath . '_' . $fileidx . $rpath;
                    break; 
                  } 
                  if ($fileidx>1000) break;
              }                      
        } 

        $tmpname=$fspathname. '_part' ;
        if (file_exists($tmpname)) {
          // Remove content of any old upload attempt: 
          $fh=fopen($tmpname,'w');
          fclose($fh);
        } 
 }

$tmpname=$fspathname. '_part' ;

if (stristr($tasks,'add')!==false){
             // If chunk sent, immediately saved (appended) to suggested filename. 
             // Client MUST take note of any filename change returned here. 
             // Otherwise, suggested indexed filename is returned and no action taken.
             $fh = fopen($tmpname, 'a');
             $bytes=fwrite($fh,getp('chunk'));
             fclose($fh);

 // cklog('Temp file written ' . $tmpname . strlen(getp('chunk')). ' bytes' );
             $rtnval='OK:'.$destfile; 
          } else { 
             $rtnval='OK:'.$destfile;  
  }

  if (stristr($tasks,'close')!==false){
     // check length and possibly overall crc here ...

  $rtn=true;
    $rtn=rename($tmpname,$fspathname);
    if ($rtn){
      $rtnval='OK:' . $destfile; 
    }else{
      $rtnval='Error: - Failed to Save: ' . $destfile; 
    }
  }

  ajaxecho ($rtnval);

}

  
  
function cklog($le){
  $logfile = fsroot . "log/image_uploader.log" ;
  $fh = fopen($logfile, 'a');
   fwrite($fh, $le . "\n");
  fclose($fh);
}


function ajaxecho($rtndata){ 
   $rtnstr='';
   if (is_array($rtndata)){
     foreach ($rtndata as $key => $val) {
      $rtnstr.=$key.':'.base64_encode($val).';';
     }
     // $crc=cms_hash($rtnstr);
     // $rtnstr.='crc:'.base64_encode($crc);
   } else {
     $rtnstr=base64_encode($rtndata);
   }
   $rtnstr = '~::~' . $rtnstr . '~::~' ;
   echo $rtnstr;
   return;
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

function image_downsize(){

}

?>