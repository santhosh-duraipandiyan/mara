<?php

// Functions loaded when in administrative mode (but not necesarily logged on) 
// - this file is only loaded in admin mode... so no need for adminmode test here. 
// global ok2admin=true can be assumed.


$echoline= '<script type="text/javascript" src="' . codedir . 'enc.js' . jsindex . '"></script>';
echo $echoline;

//Add custom CKEditor variables;

if (!getg('editing_disallowed')){
  $echoline= '<script type="text/javascript" src="' . codedir . 'ckinline.js' . jsindex . '"></script>' . "\n";
  echo $echoline;
}

echo "<script>shash='".shash."';debug=".debug.";ok2edit=true;user_privelege=\"".gets('user_privelege')."\";editing_disallowed=\"".getg('editing_disallowed')."\";</script>";
if(get_magic_quotes_gpc()) echo "<b>Warning: PHP magic quotes are enabled. This feature is deprecated, and might cause problems.</b><br>";

if (debug>1){
 echo"<textarea rows=10 cols=80>";
 var_dump($_SESSION);   // get_defined_vars());
 echo"</textarea>";
}

// Convenience addition, allows username in first URL to be opened in browser.. 
if (gets('usr')=="") {
 $loginusr = get(editkey);
}else{
 $loginusr = gets('usr');
}

?>

<script type="text/javascript">

// present the login form as required when no user is logged on, or the toobar when one is. 
if (authenticated=='1'){
  kato_setping(true);
  adminbar(true);
  if (editor_autostart>0) {ckinline(true);}
  } else {  
  adminbar(false);
}

// JS Functions from this point on.... 

function adminbar(isloggedin) {
 // Note: admindiv block is inserted by reflex.php; 
 admindiv=document.getElementById('adminbar');
 var cms_browser_version=parseFloat('<?php echo getg('browser_version')?>');
 var cms_browser_name='<?php echo getg('browser_name')?>';
 // Modified to allow for IE11 wrongly reporting itself as IE7, or giving no version number... 
 if (cms_browser_version>0){
  if('msie'==cms_browser_name && cms_browser_version<8) { 
   admindiv.innerHTML="Your browser is unsuitable for editing Mara documents. Login inhibited to prevent possible data corruption.";
   alert("Your browser has reported itself as an old version of Microsoft Internet Explorer. Use of such an out-of-date browser (or modern browser emulating an out-of-date version) may cause data corruption. Note that this situation may arise with IE 11, which has a bug causing it to go into IE7-emulation mode on local/intranet websites. If so, you need to adjust your browser's Compatibility View settings." )
   return;
  }
 }

//Prevent admin menu loading in menu test frame:
  if (window.location.href != window.top.location.href) {
    admindiv.style.display='none';
    return;
  }
 var whynoedit="Typically, only Admins and Managers are permitted to edit protected pages - which include pages containing php or js scripts. Note: You may need to reload this page if you have just logged-on as an administrative user"; 
 var lf = '<b style="color:yellow;font-family:courier;">Mara &nbsp;&nbsp;&nbsp;</b>';
 var lf = "";
 if (!isloggedin) {
     lf = lf + '&nbsp;<img src="<?php echo codedir ?>img/cms_logo.gif" id="adminbar_logo" class="admin_logo">';
     lf = lf + '<form name="login" enctype="multipart/form-data" method="post" action="javascript:sendLogin(true)" style="display:inline;">';
     lf = lf + '&nbsp;&nbsp;User:<input class="inputbox" type="text" name="usr" id="login_usr" size="20" maxlength="60" value="<?php echo $loginusr;?>" > ';
     lf = lf +  '&nbsp;Password:<input class="inputbox" type="password"  name="pwd" id="login_pwd" size="20" maxlength="60" value="" >';
     lf = lf +  '&nbsp;<input type="submit" class="button" name="login" value="Login" >';
     lf = lf +  '';
     lf = lf +  '</form>';
     lf = lf  + '<span id="hypermsg"></span>'
     admindiv.innerHTML=lf;
     if (document.login.usr.value==""){
       document.getElementById('login_usr').focus();
     }else{  
       document.getElementById('login_pwd').focus();
     }
 } else {
     
     var currentuser= document.ajax.usr.value;
     var adminmenu_user="User" 
     if (currentuser.length<10) {adminmenu_user=currentuser}
     lf =lf +  '<ul id="admin_nav">';
     lf =lf +  '<li class=\'admin_logo\'><img src="<?php echo codedir ?>img/cms_logo.gif"></li>';
     lf =lf +  '<li><i><a href="#" title="Logged in as: ' + document.ajax.usr.value + '" >' + adminmenu_user + '</a></i>';
     lf =lf +  '<ul>';
     lf =lf +  '<li><a href="javascript:adminbar(false)" title="Change user from: ' + currentuser + '">Log In</a></li>';
     lf =lf +  '<li><a href="javascript:sendLogin(false)" title="Log out as: ' + currentuser + '">Log Out</a></li>';
     lf =lf +  '</ul>';
     lf =lf +  '</li>';
     lf =lf +  '<li><a href="#">File</a>';
     lf =lf +  '<ul>';
     if(user_privelege>1) {
      lf =lf +  '<li><a href="<?php echo codedir ?>dir.php?type=filenew" target="_blank">New</a></li>';
     }
     lf =lf +  '<li><a href="<?php echo codedir ?>dir.php?type=fileopen" target="_blank">Open</a></li>';
     if (editing_disallowed){
        lf =lf +  '<li><a style="color:gray" href="#" title="Protected Page" >Save</a></li>';   
     }else{
        lf =lf +  '<li><a href="javascript:savefrommenu()">Save</a></li>';
     }
     lf =lf +  '</ul>';
     lf =lf +  '</li>';
     lf =lf +  '<li><a href="#">Edit</a>';
     lf =lf +  '<ul>';
     if (editing_disallowed){
       lf =lf +  '<li><a style="color:gray" href="javascript:alert(\''+whynoedit+'\');" title="Protected page" >Editor On</a></li>';   
       lf =lf +  '<li><a href="#">Editor Off</a></li>';
     }else{
       lf =lf +  '<li><a href="javascript:if(typeof window.ckinline == \'function\') {ckinline(true);}">Editor On</a></li>';
       lf =lf +  '<li><a href="javascript:if(typeof window.ckinline == \'function\') {ckinline(false);}">Editor Off</a></li>';
       // (Experimental) lf =lf +  '<li><a href="javascript:if(typeof window.ckinline == \'function\') {ckinline(true,\'Basic\');}">Editor full toolbar</a></li>';
     }
     if(user_privelege>2) {
       lf =lf +  '<li><a href="<?php echo codedir ?>menuedit.php" target="_blank">Edit Menu</a></li>';
     }else{
       lf =lf +  '<li><a style="color:gray" href="#" title="You have insufficient rights" >Edit Menu</a></li>';
     }
     lf =lf +  '<li><a href="#"></a></li>';
     lf =lf +  '</ul>';
     lf =lf +  '</li>';
     lf =lf +  '<li><a href="#">Tools</a>';
     lf =lf +  '<ul>';
     <?php if (is_dir(fsroot.codebase.'fm')) {?>
     if(user_privelege>4) {
       lf =lf +  '<li><a href="' + codebase + 'fm.php">File Manager</a></li>';
     }else{
       lf =lf +  '<li><a style="color:gray" href="#" title="You have insufficient rights" >File Manager</a></li>';
     }
     <?php } ?>
     if(user_privelege>4) {
       lf =lf +  '<li><a href="<?php echo siteroot . codebase . 'usrmgr.php' ?>" target="_blank">User Manager</a></li>';
     }else{
       lf =lf +  '<li><a style="color:gray" href="#" title="You have insufficient rights" >User Manager</a></li>';
     }
     if(user_privelege>=file_restore) {
       lf =lf +  '<li><a href="<?php echo siteroot . codebase . 'dir.php?type=filerestore' ?>" target="_blank">File Restore</a></li>';
     }else{
       lf =lf +  '<li><a style="color:gray" href="#" title="You have insufficient rights" >File Restore</a></li>';
     }
     <?php if (file_exists(fsroot . 'help.htm')) {?>
     lf =lf +  '<li><a href="<?php echo siteroot . 'help.htm' ?>" target="_help">Help</a></li>';
     <?php } ?>
     lf =lf +  '</ul>';
     lf =lf +  '</li>';
     lf =lf +  '</ul>';
     lf = lf  + '<span id="hypermsg"></span>'
     admindiv.innerHTML=lf;
 }
}

function savefrommenu() {
    if ((!ok2edit) || (typeof CKEDITOR.instances.editable!=='object')){
      alert("Editor must be ON in order to save the editable area of page.");
      return;
    }
  SaveFile();
  return;
}
  
  function LaunchFileManager(){
    document.ajax.srcfile.value=fsroot;
    document.ajax.action=codedir + 'fm/';
    document.ajax.submit();
    document.ajax.action="";
  }
  
  function sendLogin(status) {
   // log user out, or log in, acc. status flag. 
   if (status!=true) {
    ajax('logout');
    document.ajax.usr.value="";
    document.ajax.hash.value="";
    document.ajax.pwd.value="";
    authenticated='0';
    user="";
    pwd="";
    nacl="";
    ok2admin=false;
    adminbar(false); 
    halert(document.ajax.response.value);
    //ckinline (editor off) call interferes with ajax call if placed before logout. 
    if(typeof window.ckinline == 'function') {ckinline(false);}
    kato_setping(false);
    return ;
   }
   
   ajax('setsalt');
   nacl=document.ajax.response.value;
    // alert('nacl: ' +  nacl);
    // return;
   var pwdhash=cms_hash(document.login.pwd.value + shash + document.login.usr.value);
   // user=document.login.usr.value;
   var pwdshash=cms_hash(pwdhash + nacl);
   document.ajax.usr.value=document.login.usr.value
   document.ajax.hash.value=pwdhash;
   document.ajax.pwd.value=pwdshash ;
   document.ajax.action.value="login";
   // alert(pwdhash);
   document.login.pwd.value="";
   ajax('login');
   if (document.ajax.response.value.indexOf("OK")==0) {
     user_privelege=document.ajax.response.value.substring(3);
     // alert(user_privelege)
     authenticated='1';
     // added for default pwd check: 
     if (pwdhash=='c64853aef0e470c979b41ab7fb550a967517ce3e8e3866f446db76e0abd66a40' || pwdhash=='875e83b77cc7436105ed93bb4887b0e2') {
       var usraction=confirm("You are using the default admin login.\n Please change the password for this account IMMEDIATELY.\n Otherwise, if the site is visible from the Internet, it may be hacked. " ); 
       if (usraction){
         usrmgrwindow=window.open("<?php echo siteroot . codebase . 'usrmgr.php' ?>" , "usrmgrwindow");
         usrmgrwindow.focus();
       }
     }
     if(typeof window.ckinline == 'function') {ckinline(true);}
     adminbar(true);
     kato_setping(true);
     cms_h1_msg=document.getElementById('cms_h1_msg_edit_lock');
     if (undefined != cms_h1_msg){
       if (cms_h1_msg.innerHTML.indexOf('Login Required:-')>0){
         window.location.reload();
       }
     }
   } else {
     alert('Error : ' + document.ajax.response.value);
     authenticated='0';
     kato_setping(false);
     if(typeof window.ckinline == 'function') {ckinline(false);}
   }
  if (typeof document.login !== 'undefined') {document.login.pwd.value="";}
  return ;
  }

function kato_setping(kato_action) {
  // requires kato_keepalive and kato_timeout consts.
  if (typeof kato_pingprocess !== 'undefined') {clearInterval(kato_pingprocess);}
  kato_lastping=0;
  kato_pingfailcount=0;
  if (kato_action) {
    if (authenticated && (kato_keepalive >0)){
      kato_pingprocess=setInterval("kato_pinger()",kato_keepalive*1000);
    }
  }
}

function kato_pinger() {
  var resumed_process=false;
  var d = new Date();
  var timenow = parseInt(d.getTime()/1000);
  var server_timeout=false;
  if (authenticated){
      kato_lastping=timenow;
      ajax('keepalive');
      if (document.ajax.response.value.indexOf("timeout")>1){
         // server replies that php session has timed out, so force logoff
         server_timeout = true ;
      }
  }
  if (server_timeout || (kato_pingfailcount>3) ) {
        // (Note, does not lose editor data as it is still possible to log back in by ajax)
        kato_setping(false);
        sendLogin(false);
        halert("User logged out due to non-continuous session. " + kato_pingfailcount );
  }
  kato_lastping=timenow;
  // halert(kato_lastping);
  if (document.ajax.response.value.indexOf("OK")!=0){
    // no response (or bad response) from server..
    kato_pingfailcount++ ;
  }
return;
}


function EditSource(){
  if (user_privelege<sourcecode_editlevel){
    alert("Sorry, you do not have the necessary permissions to use the page sourcecode editing tool.");
    return;
  }
  
  var adataimages=document.getElementById('editable').getElementsByTagName('img');
  var doimages=false;
  var srclength=0;

  for (ct=0;ct<adataimages.length;ct++) {
      thissrc=adataimages[ct].src.length;
      srclength+=thissrc ; 
  }

  if (srclength>100000) {
    alert('You must upload the temporary (inline) images in this page before you can edit the sourcecode.\n');
    return;   
  }
  

  // Get viewport width/height in various browsers...
  var vpWidth = 800;
  if( typeof( window.innerWidth ) == 'number' ) {
    //Non-IE
    vpWidth = window.innerWidth;
  } else if( document.documentElement && document.documentElement.clientWidth ) {
    //IE 6+ in 'standards compliant mode'
    vpWidth = document.documentElement.clientWidth;
  } else if( document.body && document.body.clientWidth ) {
    //IE 4 compatible
    vpWidth = document.body.clientWidth;
  }
  var vpHeight = 600;
  if( typeof( window.innerHeight ) == 'number' ) {
    //Non-IE
    vpHeight = window.innerHeight;
  } else if( document.documentElement && document.documentElement.clientHeight ) {
    //IE 6+ in 'standards compliant mode'
    vpHeight = document.documentElement.clientHeight;
  } else if( document.body && document.body.clientHeight ) {
    //IE 4 compatible
    vpHeight = document.body.clientHeight;
  }
  var srcHeight=(vpHeight-10).toString(); 
  var srcWidth=(vpWidth-100).toString();    
  var sizeparms="height="+srcHeight+",width="+srcWidth+",left=20,top=20,scrollbars=yes,titlebar=no,toolbar=no,status=no";
  // global handle for sake of close when page is reloaded... 
  srcwindow=window.open("<?php echo siteroot . 'codebase/srcedit.php?debug='.debug ?>" , "srcwindow", sizeparms);
  srcwindow.focus();
}

function QuickImage(){
  // added for test purposes ***********************

  // cms_uploader('quick');

  var qiURL=siteroot + 'codebase/quick.php'
  var qiParams="titlebar=0, location=0, toolbar=0, status=0, resizable=1, scrollbars=1, width=800, height=800";   
  var qiWindow = window.open(qiURL, 'cms_qiwindow', qiParams, true);
  qiWindow.focus();
}

function PreviewPage(){

  if (user_privelege <1){
    alert("Sorry. Owing to disk writes being prohibited, Page Preview is not active in demo mode.");
    return false;
  }

  var thisdestfile=getFileDirectory(pagefile) + "~preview.php"
  // alert(thisdestfile);
    var savenote="";
    var filtpagestr="";
    var oorc=0;
    var pagestr=CKEDITOR.instances.editable.getData();
    document.ajax.data.value = pagestr;
    document.ajax.action.value="save";
    var thiscrc=cms_hash(document.ajax.data.value);
    document.ajax.crc.value=thiscrc;
    document.ajax.srcfile.value=pagefile;
    document.ajax.destfile.value=thisdestfile ; 
    ajax('preview');
    // alert(document.ajax.response.value);
    if (document.ajax.response.value.indexOf("OK")==0) {
      var qiURL=siteroot + thisdestfile +"?noadmin=1&hide=top";
      var qiParams="titlebar=yes,resizable=yes,scrollbars=yes,width=1024, height=768";   
      var qiWindow = window.open(qiURL,"Edit Preview",qiParams);
      qiWindow.onLoad=qiWindow.focus();
    }
 function getFileDirectory(filePath) {
  var thisfp='undefined';
  if (filePath.indexOf("\\") != -1) { // windows
     thisfp= filePath.substring(0, filePath.lastIndexOf('\\')+1);
  } 
  else if (filePath.indexOf("/") != -1) { // unix
     thisfp=filePath.substring(0, filePath.lastIndexOf('/')+1);
  }
  if (thisfp=='undefined'){ thisfp=''}
  return thisfp;
 }

}

// end admin JS functions.

function halert(h_msg){
    var hb=document.getElementById('hypermsg');
    hb.innerHTML="&nbsp;" + h_msg;
    if (typeof halert_timer !== 'undefined') {clearInterval(halert_timer);}
    halert_timer=setTimeout(function(){document.getElementById('hypermsg').innerHTML=""}, 30000);
}

</script>


<style>
/* Make broken images visible in editable content (but only for site editors) */
@-moz-document url-prefix(http), url-prefix(file) {
#editable img:-moz-broken{
    -moz-force-broken-image-icon:1;
    border:1px dotted gray;
    width:32px;
    height:32px;
    margin:1px 3px;
  }
}
</style>  

