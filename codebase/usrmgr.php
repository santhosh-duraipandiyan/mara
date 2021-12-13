<?php
require_once "core.php";
echo "<!doctype html><html><head>";
echo js_syspaths;
?>
<title>Mara User Manager</title>
<link rel=stylesheet href="system.css" type="text/css">
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
</head>
<body style='background:#444;color:white;'>
<div id='adminbar'><img src="<?php echo codedir ?>/img/cms_logo.gif" style="vertical-align:middle;text-align:left;" id="adminbar_logo" >
&nbsp; <?php echo "User: <i>" . gets('usr'). "</i>&nbsp;&nbsp; </div>";?>
<p>&nbsp;</p>
<?php
editusers();
function editusers() { 
  if (gets('authenticated')<1) {echo "<h2 style='color:red'>Access denied: Not logged in.</h2>";exit ;}
  // add privelege level check here.. 
  if (gets('user_privelege')<5) {echo "<h2 style='color:red'>Access denied: Insufficient user rights.</h2>";exit ;}
  
  $ct=0;
  $firstuser="";
  $thisusername="";
  // gets hash from userdata file and compares with submitted pwd. 
  // userini.php is actually an ini file, but with a php noexecute command to prevent peeking. 
  if (gets('user_privelege')==5) {
    $userinifile = fsconfigdir . 'userini.php' ;
  }else{
    $userinifile = fsconfigdir . 'userdemo.php' ;
  }

  $auserdata=inireadarray($userinifile) ;
  if( $auserdata==null ) {echo("Error. No user data found.") ; return false;}
  for ($ct=1;$ct<6;$ct++){
     $auserdata[]=array('','','','1'); // add a blank user
  }
  echo '<form name="userdata" action="javascript:senduserdata()">';
  echo"<table class='cms_usrmgr'><tr>\n";
  echo "<td colspan='4' style='text-align:center;padding:10px;'> <h1 style='margin:0px;'>Mara User Manager</h1></td></tr><tr>";
  echo"<td>Login</td><td>Description</td><td>Password</td><td>Status</td>";  
  $aprivelege=array('Delete','Deactivated','Typist','Writer','Editor','Manager','Administrator','Demo mode');
  foreach ($auserdata as $thisuser)  {
    if (!isset($thisuser['section_name']))$thisuser['section_name']="";
    if (!isset($thisuser['description']))$thisuser['description']="";
    if (!isset($thisuser['pwd']))$thisuser['pwd']="";
    if (!isset($thisuser['privelege']))$thisuser['privelege']=0;
    $nowrite='';
    if ($thisuser['section_name']!='')$nowrite=' readonly=readonly ';
    echo "<tr>\n"; 
    $ct++;
      $pwdstate="";
      if ($thisuser['privelege']=="") $thisuser['privelege']= 0;
      if ($thisuser['pwd']!="") { 
        $pwdstate="-:set:-";
      }
      // $fieldname=$ct . "_" . $itemname;
      echo"<td><input class=\"inputbox\" type=\"text\" " . $nowrite . " name=\"".$ct."_section_name\"  size=\"25\" maxlength=\"60\" value=\"".$thisuser['section_name']."\" ></td>\n";
      echo"<td><input class=\"inputbox\" type=\"text\"  name=\"".$ct."_description\"  size=\"25\" maxlength=\"60\" value=\"".$thisuser['description']."\" ></td>\n";
      echo"<td><input class=\"inputbox\" type=\"text\"  name=\"".$ct."_pwd\"  size=\"25\" maxlength=\"60\" value=\"$pwdstate\" ></td>\n";
      echo"<td><select name=\"".$ct."_privelege\">\n";
      $opct=-1;
      foreach ($aprivelege as $thisprivelege) {
         $selected="";
         if ($thisuser['privelege']==$opct) $selected="selected"; 
         echo"<option value=$opct $selected>".$thisprivelege."</option>\n";
         $opct++;
      }
      echo "</select></td>";
      echo "</tr>\n";
    } // end of this user line;
}
?>
<tr><td colspan="4" style='text-align:center;padding:10px;'>
<input type="submit" name="senduserdata" value="Update Users">
</td></tr><tr><td colspan="4" style='text-align:center;padding:10px;'>
Please save any work open in other browser tabs before changing user account settings.
</td></tr></table>

</form>

<script>

function senduserdata(){
    document.ajax.action.value="userupdate";
    document.ajax.data.value=packuserdata();
    thiscrc=cms_hash(document.ajax.data.value);
    document.ajax.crc.value=thiscrc;
    document.ajax.srcfile.value="userini.php";
    document.ajax.destfile.value="userini.php";
    ajax('userupdate');

   if (document.ajax.response.value.substring(0,2)=='OK'){
     var choice=confirm(document.ajax.response.value + "\n\n  Do you want to close user manager and re-login? \n\n Provided you have not changed the active login, you can press Cancel to continue adding users.");
     if ((choice) || (document.ajax.response.value.indexOf('salt')>0)){
       window.opener.sendLogin(false);
       window.opener.location.reload();
       window.close();
     } else {
       window.location.reload();
     }
   }else{
     alert(document.ajax.response.value); 
   }
}

function packuserdata() {
 // Pack the usermanager form fields into ini file format.
 var thisuser="";
 var sepchar="\n";
 var userstr="";
 var fieldcount=document.userdata.elements.length; 
 var setnewshash=false;
 if (shash==9552){setnewshash=true;}
 // alert(fieldcount)

 // Check if all accounts have a password defined..
  for (thisfield=0;thisfield<fieldcount;thisfield++) {
    var tsd=document.forms["userdata"].elements[thisfield];
    if (tsd.name.indexOf("pwd")>0 ) {
      if (tsd.value == "-:set:-") { 
        setnewshash=false;
      }
    }
  }
 if (setnewshash){
    shash=Math.floor((Math.random() * 999999) + 10001); 
 }
   for (thisfield=0;thisfield<fieldcount;thisfield++) {
   var tsd=document.forms["userdata"].elements[thisfield];
   if (typeof(tsd)=='undefined') continue;
   var stindex=tsd.name.indexOf("_")+1;
   var fieldname=tsd.name.substring(stindex,255);
   if (tsd.name.indexOf("section_name")>0){
     var thisuser=tsd.value;
     if (thisuser!="") userstr = userstr + "[" + tsd.value + "]" + sepchar;
     continue;
   }
   if (tsd.name=="senduserdata") {
     continue;
   }   
   if (tsd.name.indexOf("pwd")>0 && (thisuser!="")) {
     var pwdhash="";
     if (setnewshash){
        userstr = userstr + "shash" + "=" + shash + sepchar;
     }
     if (tsd.value != "-:set:-") { 
         pwdhash=cms_hash(tsd.value + shash + thisuser);
         userstr = userstr + fieldname + "=" + pwdhash + sepchar;
     }
     if (tsd.value == "-:set:-") { 
         userstr = userstr + fieldname + "=" + tsd.value + sepchar;
     }
     if (tsd.value == "") {
       userstr = userstr + fieldname + "=" + Math.random()*1111 + sepchar;
       alert("Warning: User " + thisuser + " has no password. Account will remain locked until one is set.");
     }
     continue;
   }
   if (thisuser!="") userstr = userstr + fieldname + "=" + tsd.value + sepchar;
  } 
 return userstr;
}

</script>
<?php
require_once "ajax.php";
?>
</body>
</html>
