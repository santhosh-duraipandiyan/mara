<?php
require_once 'core.php';
 
if (get('iframe')==1){
  echo '<!doctype html><meta http-equiv="Content-Type" content="text/html; charset=utf-8"><div id="preview1"></div>';
  exit;
}

$siteroot=siteroot;

if (gets('authenticated')<1) {echo "<h2 style='color:red'>Access denied: Not logged in.</h2>"; exit; }
// echo "<script>alert (opener.fsroot+'|'+opener.siteroot+ '|' +opener.pagedir) </script>";
$direxclusions = array( 'cgi-bin', '.', '..', 'codebase', 'archive' ,'plugin','theme','sitecfg','material','undo','tmp','log','.thumb');
$ck_callback=get('CKEditorFuncNum');
$actiontype=get('type');
$uploaddir="img";
$flatfile=false;
$treemode="standard";
if (gets('user_privelege')<3) $flatfile=true;
$caption="" ;
$filetypes='';
if ( $actiontype=='images' ) {
  $caption="Image selector mode - select a photo or graphic";
  $filetypes=array('jpg','gif','png','jpeg','svg');
  $initdir="img";
  $uploaddir="img";
}

if ( $actiontype=='files' ) {
  $caption="Select a file to open or link to";
  $filetypes=array('htm','html','php','txt','jpg','gif','png','jpeg','svg','mp3','mp4','webm','avi');
  $initdir="";
  $uploaddir="";
}

if ( $actiontype=='filenew' ) {
  $caption="New file mode - create a new page based on an existing file";
  $filetypes=array('htm','html','php','txt','template');
  $initdir="template";
  $uploaddir="";

}

if ( $actiontype=='fileopen' ) {
  $caption="Select an HTML or php file to edit (which need not necessarily be on the main menu)";
  $filetypes=array('htm','html','php','txt','template');
  $initdir="";
  $uploaddir="";
}

if ( $actiontype=='filerestore' ) {
  if (gets('user_privelege')<file_restore) {
    echo "<h2 style='color:red'>You have insufficient priveleges to perform file restores.</h2>"; 
    exit;
  }
  $caption="File restore mode - select a previous version to restore";
  $direxclusions = array( 'cgi-bin', '.', '..', 'codebase', 'archive' ,'menu','theme','material','tmp','log','.thumb');
  $filetypes='*' ; // array('htm','html','php','txt','mnu');
  $initdir="undo";
  $uploaddir="undo";
}
// echo "</div>";
?>
<!DOCTYPE HTML>
<html><meta http-equiv="Content-Type" content="text/html; charset=utf-8"><body class='dir' style="overflow:auto">
<link rel=stylesheet href="system.css" type="text/css">
<link rel=stylesheet href="dialog.css" type="text/css">
<?php echo  '<script type="text/javascript" src="' .codedir. 'system.js' .jsindex. '" ></script>' . "\n"; ?>
<div id='adminbar'><img src="<?php echo codedir ?>/img/cms_logo.gif" style="vertical-align:middle;text-align:left;" id="adminbar_logo" >
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; Filesystem Browser : &nbsp;&nbsp;&nbsp; <?php echo $caption; ?>
</div>
<script>cms_popup_autoclose();</script>
<?php
echo "<script>var actiontype = '$actiontype'; var ck_callback = '$ck_callback' </script>";

  echo js_syspaths;
  foreach($_REQUEST AS $ptkey => $ptvalue) {
   ${$ptkey} = rawurldecode($ptvalue);
//  echo "$ptkey:<input type='text' name='$ptkey' value='$ptvalue'>";
  }
?>  
<table class='cms_browser' >
<tr align="left"><td rowspan=2 style="text-align:left;vertical-align:top" width=300>
<div id='divdirlist' >

<?php

// echo "<script>window.resizeTo(300,600);</script>";
$startpath=gets('siteroot');
$openpath=path(gets('fsroot') . $initdir);

$pagedir=gets('siteroot');
if (!$flatfile) {
 if ( $actiontype!='filerestore' ) {
  $scanq[]=gets('fsroot');
  $scanq[]="-::-";
 }
}
$scanq[]=gets('fsroot').$initdir;
/*
echo "startpath: $startpath <br>";
echo "openpath: $openpath <br>";
echo "pagedir: $pagedir <br>";
echo "siteroot: $siteroot <br>";
echo "fsroot: $fsroot <br>";
*/

if (!$flatfile) {
 if ($actiontype!='filerestore'){
  echo "<sub><b>View: ";
  echo "<a href='#' id='tree_click_standard' title='Show only the typical storage locations for this file type' onclick='javascript:treeview(\"standard\")'>Standard</a>";
  echo " | <a href='#' id='tree_click_full' title='Show the whole of the website disk space' onclick='javascript:treeview(\"full\")'>Full</a></b></sub>";
 }
}
 ?>    
 <script>
      function treeview(viewmode){
       if(document.getElementById('menutree_full')==null){return false;}
       if (viewmode=='full'){
         document.getElementById('menutree_standard').style.display='none';
         document.getElementById('menutree_full').style.display='block';
       }else{
         document.getElementById('menutree_standard').style.display='block';
         document.getElementById('menutree_full').style.display='none';
       }
      }
 </script>
 <?php
$dirct=0;
$pathdepth=0;
$divdepth=0;

if (get('type')=='filerestore'){
  echo  "<div id='menutree_standard'><b>Page Version History:</b><br>";
}else{
  echo  "<div id='menutree_standard'><small><b>Default location:</b> $siteroot$initdir</small><br>";
}
$offset=substr_count($scanq[0],'/');

while (true) :

$thispath=path(array_pop($scanq));

if (substr($thispath,0,4)=='-::-') {
  while ($divdepth>0) {
   echo "</div></div>\n";
   $divdepth--;
  }

  //workaround for CK image link bug (for clickable images, it asks for another image as the link)... 
  if ($actiontype=='files'){
    $filetypes=array('htm','html','txt','jpg','gif','png','jpeg','svg');
  }
  echo  "</div><div id='menutree_full' style='display:none;'><sub><b>Website root:</b> $siteroot </sub><br>";
  continue;
}

$pathdepth=substr_count($thispath,'/')-$offset;
// echo "Thispath: $thispath <br>";
if ($thispath==null) break;
 $subscanq=null;
 $dcontents = scandir($thispath);
 $files=null;
 $dirct++;
 $thisvis="none";
// echo "$thispath | $openpath";
 if ($thispath==$openpath) $thisvis="block";
 $dirdisplay="<img id='idir$dirct' src='img/folder.gif'>&nbsp;" . basename($thispath);
 
 $thisrelpath=$thispath;
 $thisrelpath=path(ltrim(substr($thisrelpath,strlen(gets('fsroot'))),'/'));

 while (($divdepth>=$pathdepth) && ($divdepth>0)) {
   echo "</div></div>\n";
   $divdepth--;
 }

 $dirindent=($divdepth*8) . 'px';
 $fileindent=($divdepth*8)+15 . 'px';


 echo "<a id='adir$dirct' title=\"$thisrelpath\" href=\"javascript:selectdir('dir$dirct');\" class='dir' style='margin-left:$dirindent;'>$dirdisplay</a>\n";
// opening of indent 
 echo "<div><div id='dir$dirct' style='display:$thisvis;'>\n"; $divdepth++;

 foreach ($dcontents as $thisitem) {
  $dir=is_dir($thispath.$thisitem);
  if ($dir) {
     if(!in_array($thisitem,$direxclusions)) $subscanq[]=$thispath . $thisitem;
//    if(!in_array($thisitem,$direxclusions)) $scanq[]=$thispath . $thisitem;
  } else {
     $files[]=$thisitem;     
  }
 }

 if (is_array($files)){
   natcasesort($files);
 }
 
 if (is_array($subscanq)){
   natcasesort($subscanq);
   for($ct=count($subscanq); $ct>0 ; $ct--){
     $scanq[]=$subscanq[$ct-1];
   }
 }
 $filect=0;
 if (isset($files)) {
  foreach ($files as $thisfile) {
    if (is_array($filetypes)){
      $thisext = strtolower(substr(strrchr($thisfile, '.'), 1));
      if(!in_array($thisext,$filetypes) ) continue;
    }
    // exclude hidden files.. 
    if(substr($thisfile,0,1)=="." ) continue;
    if(substr($thisfile,0,1)=="~" ) continue; // Don't list temp files (75)
    $fileabsurl=substr($thispath.$thisfile,strlen(fsroot));
    $fileabsurl=$thisrelpath.$thisfile; // temp change **************** 
    $thisfileinfo=filesize($thispath.$thisfile) . " bytes : " . $fileabsurl;
     $filect++;
//   echo "$depth ~~~ $prevdepth ";

     echo "<a title=\"$thisfileinfo\" ondblclick='javascript:selectfile(\"$startpath$fileabsurl\");' onclick='javascript:previewfile(\"$fileabsurl\");' class='file' style='margin-left:$fileindent;'>$thisfile</a>\n";
  }
  unset($files) ; // $files="" php7 ;
 }
 if ($filect==0){
    if ($thisvis=='none'){
      echo "<div class='file' style='margin-left:$fileindent;'><sup>~no matching files~</sup></div>\n";
    }
 }
 // close of indent 

// if ($divdepth==0) echo"</div></div>endoftoplevelfiles\n";
 
 if ($filect==0){
    echo "<style> #adir$dirct {color:#aaaaaa;}</style>";
 }

 // Scan only root dir if opening files for a basic user..
 if ( $actiontype=='fileopen' || $actiontype=='filerestore' ) {
   if ($flatfile) {
     break;
   }
 }
 
endwhile;

 // close out second listing .. 
 while ($divdepth>0) {
   echo  "</div></div>\n";
   $divdepth--;
 }

echo '</div>'; //closes full tree list
?>

<script>
// alert(window.parent.siteroot);
// alert(window.outerWidth);
// alert(this.name + ' ' + window.parent);
treeview('standard');
previewedfile="";

function selectdir(thispathitem) {
 var thisid=document.getElementById(thispathitem);
 var thisaid=document.getElementById('a'+thispathitem);
 var thisiid=document.getElementById('i'+thispathitem);
// alert(thisid.display);
if (thisid.style.display=="none") {
  if (document.getElementById('uploadform')) {
     document.uploader.destdir.value=thisaid.title;
  }
  thisid.style.display="block";
  thisiid.src='img/folderopen.gif'; 
 } else {
  thisiid.src='img/folder.gif'; 
  thisid.style.display="none";
 }

// var rhid=document.getElementById("td_files");
// rhid.innerHTML=thisid.innerHTML;
}

function selectfile(fileselected) {
// var dirlen=opener.fsroot.length;

// fileselected is in relation to website root.
// if page is in a subdir, strip website root path from page path to give relative path to page.. 
var relpagedir=opener.pagedir.substring(opener.siteroot.length);

// Three scenarios:
// Linked file is in same directory, or subdirectory
// Or, linked file is in another branch. 

if (relpagedir=="") {
  // Page is in site root (easy situation!) 
var  pathrel2page=fileselected;
} else if (fileselected.substring(0,relpagedir.length)==relpagedir) { 
  // Linked file is in a subdirectory of nonroot page location
  //  then strip off part of path prior to page location..
var   pathrel2page=fileselected.substring(relpagedir.length);
} else {
  //count number of levels down to page location and navigate back up to site root..
  var numdirsup=(relpagedir.split('/').length - 1)
  var dirprefix=repeat('../',numdirsup) 
  var pathrel2page=dirprefix+fileselected
}
 
function repeat(s, n){
    var a = [];
    while(a.length < n){
        a.push(s);
    }
    return a.join('');
}

 // alert(fileselected +" | "+ pathrel2page);

 if (fileselected==""){alert('No file selected');return false;}
 /*
 if (fileselected.substring(0,fsroot.length)==fsroot) { 
  fileselected=fileselected.substring(fsroot.length);
 }
 if (fileselected.substring(0,opener.pagedir.length)==opener.pagedir) { 
  fileselected=fileselected.substring(opener.pagedir.length);
 }
 */ 
 // fileloc=opener.pagedir;

// if (fileselected.substring(0,1)!="/") {fileselected=fileloc+ fileselected;}
// /img/PICT0012.JPG~13 | /img/PICT0012
//  alert(fileselected + '\n' + fileloc + ':' + fileloc.length +' \n '+ fileselected.substring(0,fileloc.length));

// if (fileselected.substring(0,fileloc.length)==fileloc) { 
//  fileselected=fileselected.substring(fileloc.length);
// }
  
 // if (opener.pagedir!="") {fileselected=opener.pagedir + fileselected}
 //  alert(fileselected);
 // alert(actiontype);

 if (actiontype=='images'){
   window.opener.CKEDITOR.tools.callFunction(ck_callback,pathrel2page);
   window.close();
 } 
 if (actiontype=='files'){
   window.opener.CKEDITOR.tools.callFunction(ck_callback,pathrel2page);
   window.close();
 } 
  
 if (actiontype=='filenew'){
    document.ajax.action.value="filenew";
    document.ajax.data.value="";
    var thiscrc=cms_hash(document.ajax.data.value);
    document.ajax.crc.value=thiscrc;
    if (fileselected=="") {alert("No source file selected"); return 0; }
    document.ajax.srcfile.value=fileselected;
    var dff=document.newpage.destfile.value;    
    if (dff=="") {alert("No destination filename entered"); return 0; }
    if (dff.indexOf('.')<1) {dff = dff + '.'+ opener.default_extension };
    if (dff.indexOf(" ")>0) {alert("Spaces are not permitted inside Website URLs."); return 0; }
    document.ajax.destfile.value = dff
    ajax('filenew');

    if (document.ajax.response.value == "OK") {
      alert("New file created as: " + dff + "\n\n("+fsroot+dff+")");
      document.location.href=siteroot + dff;
      return true;
    } else {
      alert(document.ajax.response.value);
      return false;
    }
 } 
 
 if (actiontype=='filerestore'){
    // backup file to be overwritten by restore..     
    var dff=fileselected ;    
    document.ajax.action.value="filerestore";
    document.ajax.data.value="";
    var thiscrc=cms_hash(document.ajax.data.value);
    document.ajax.crc.value=thiscrc;
    if (fileselected=="") {alert("No previous version selected"); return 0; }
    if (dff=="") {alert("No destination filename entered"); return 0; }
    if (dff.indexOf('_unrestore')<5){
      var rsp=confirm("Do you wish to restore the earlier version saved as:" + dff);
    }else{
      var rsp=confirm("Do you wish to undo the previous file restore by recovering the version saved as:" + dff);
    }
    if (!rsp) {return 0;}
    if (dff.indexOf(" ")>0) {alert("Spaces are not permitted inside Website URLs."); return 0; }
    document.ajax.destfile.value = dff;
    document.ajax.srcfile.value = dff;

    ajax('filerestore');
    alert(document.ajax.response.value);
    return (document.ajax.response.value.indexOf("OK")==0) ;
} 

 if (actiontype=='fileopen'){
   // just replaces the selector with the selected site page. 
   document.location= fileselected;
   return true;
 } 
} 


function previewfile(fileselected) {
 previewedfile=fileselected;
 var ifrm1 = document.getElementById('ipreview1');
 if (actiontype.indexOf('file')<0){
  ifrm1.src=siteroot + fileselected; 
  return 1; 
 }
 if (fileselected.substring(0,fsroot.length)==fsroot) { 
  fileselected=fileselected.substring(fsroot.length);
 }
 if (fileselected.substring(0,1)=="/") fileselected=fileselected.substring(1);
 if (fileselected==='sitemap.php'){
   document.getElementById('ipreview1').src=siteroot + fileselected;
   return;
 }
 // fileselected = siteroot + fileselected; 
 document.ajax.action.value="getpreview";
 document.ajax.data.value="";
 var thiscrc=cms_hash(document.ajax.data.value);
 document.ajax.crc.value=thiscrc;
 document.ajax.destfile.value = fileselected;
 document.ajax.srcfile.value = fileselected;
 ajax('getpreview');
// if (document.ajax.rawresponse.value.indexOf("OK")!=0) {
// maybe put error handling here, not sure if really necessary.. 
// }
 var thisresp=document.ajax.response.value;
 if (thisresp.indexOf('file:///')==0){
   // In this case a preview file has been generated 
   thisresp=thisresp.substring(8);
   ifrm1.src=siteroot + thisresp + "?noadmin";
 }else{
  // In this case the preview data has been returned directly by ajax
  write2frame(document.ajax.response.value, 'ipreview1');
 }
// populate second frame with current version preview..
 previewedfile=fileselected; //must be global
 var slpos=previewedfile.indexOf('/')+1;
 var uspos=previewedfile.lastIndexOf('_');
 var dotpos=previewedfile.lastIndexOf('.');
 if (dotpos > uspos) {uspos=previewedfile.length} 
 latestver=previewedfile.substring(slpos,uspos);
 document.getElementById('ipreview2').src=siteroot + latestver+'?noadmin';

//sync scrolling.. 
/*
  var ifrm = document.getElementById(tgtFrame);
  ifrm = (ifrm.contentWindow) ? ifrm.contentWindow : (ifrm.contentDocument.document) ? ifrm.contentDocument.document : ifrm.contentDocument;
 var ipv1= document.getElementById('ipreview1');
 var ipv2= document.getElementById('ipreview2');
 ipv1.contentWindow.onscroll = function(e) {
    ipv2.contentWindow.scrollTop = ipv1.contentWindow.scrollTop;
    ipv2.contentWindow.scrollLeft = ipv1.contentWindow.scrollLeft;
 };
*/
} 

// Maybe move these to system.js once more fully tested, since generally useful...  

function write2frame(fdata, tgtFrame) {
    var ifrm = document.getElementById(tgtFrame);
    ifrm = (ifrm.contentWindow) ? ifrm.contentWindow : (ifrm.contentDocument.document) ? ifrm.contentDocument.document : ifrm.contentDocument;
    ifrm.document.open();
    ifrm.document.write(fdata);
    ifrm.document.close();
}

function changeFrame() {
// Test of function only.. not used.
    var oIframe = document.getElementById("ipreview1");
    var oDoc = oIframe.contentWindow || oIframe.contentDocument;
    if (oDoc.document) {
        oDoc = oDoc.document;
    }
    oDoc.body.style.backgroundColor = "#00f";
    return true;
}

function escapeHTML(text) {
  var map = {
    '&': '&amp;',
    '<': '&lt;',
    '>': '&gt;',
    '"': '&quot;',
    "'": '&#039;'
  };

  return text.replace(/[&<>"']/g, function(m) { return map[m]; });
}
</script>


</div></td></tr><tr><td valign=top>
<script>
function evcb(thisevent){
 alert('inclickfn');
 thisevent.cancelBubble=true;
}
</script>
<?php
if (get('type')=="images"){
 ?>
 <iframe sandbox name='ipreview1' id='ipreview1' src="dir.php?iframe=1&type=<?php echo $actiontype; ?>"></iframe> 
 <div class='cms_fsb_use' style='text-align:center;'>
 <form name="useimg" action="handler.php" method="POST" target="preview_frame" enctype="multipart/form-data">
   <input type='button' value='Use Selected File' name='img_submit' onClick='javascript:selectfile(previewedfile)'> 
 </form>
 </div>
 <?php
}else{
 ?>
<iframe sandbox name='ipreview1' id='ipreview1' src="dir.php?iframe=1&type=<?php echo $actiontype; ?>" width=100% height=450 border=0>Preview Area</iframe> 
 <?php
}

//  ini_set('upload_max_filesize',1200000);
//  ini_set('max_files',100);
  ini_set('max_file_uploads',100);

if (get('type')=="filenew"){
 ?>
 <div class='cms_fsb_use'>
 <form name="newpage" action="handler.php" method="POST" target="preview_frame" enctype="multipart/form-data">
   <input type = "text" style="display:none;">
   Destination Filename:&nbsp;<input type = "text" name="destfile" title="Enter a name for your new page. Webpage names must not contain spaces. The default_extension will be added if you do not specify one. You may precede the name with a subdirectory, which will be automatically created if it does not exist. All paths should be relative (no leading slash) and will automatically have the website root of <?php echo siteroot ?> added. " value="">&nbsp;
   <input type='button' value='Make Page' name='fsb_submit' onClick='javascript:selectfile(previewedfile)'> 
 </form>
 </div>
 <?php
}

if (get('type')=="files"){
 ?>
 <div class='cms_fsb_use'>
 <form name="newpage" action="handler.php" method="POST" target="preview_frame" enctype="multipart/form-data">
   <input type='button' value='Make Link' name='fsb_submit' onClick='javascript:selectfile(previewedfile)'> &nbsp; Make link to this page
 </form>
 </div>
 <?php
}

if (get('type')=="fileopen"){
 ?>
 <div class='cms_fsb_use'>
 <form name="newpage" action="handler.php" method="POST" target="preview_frame" enctype="multipart/form-data">
   <input type='button' value='Open Page in Browser' name='fsb_submit' onClick='javascript:selectfile(siteroot+previewedfile)'>
 </form>
 </div>
 <?php
}

if (get('type')=="filerestore"){
 ?>
  <div class='cms_fsb_use'>
 <form name="newpage" action="handler.php" method="POST" target="preview_frame" enctype="multipart/form-data">
   <div style='text-align:center;width:100%;margin:10px;'>Archived version above&nbsp;&nbsp;&nbsp;<input type='button' value='Restore File' name='fsb_submit' onClick='javascript:selectfile(previewedfile)'>&nbsp;&nbsp;&nbsp;Current version below</div>
 </form>
 </div>
<iframe sandbox name='ipreview2' id='ipreview2' src="" ></iframe> 
 <?php
}

if (in_array(get('type'),array('filenew','images'))):

 if (ini_get('file_uploads')!=false):
 $maxfilesize=ini_get('post_max_size');
 $maxunitsize=ini_get('upload_max_filesize');
 ?>
   <div class='cms_fsb_uploader'>
   <form name="uploader" id="uploadform" action="handler.php" method="POST" target="ipreview1" enctype="multipart/form-data">
   <table class='cms_fsb_uploader' ><tr><td> 
   <input type = "hidden" name="authenticated" value="<?php echo base64_encode(gets('authenticated'))?>">
   <?php  echo "<b>File Upload:</b> <small>( Max ". ini_get('max_file_uploads') . " files, " . $maxfilesize . "B total, <" .ini_get('upload_max_filesize'). "B each)</small>"; ?>
   <input type = "hidden" name="action" value="<?php echo base64_encode('upload');?>">
   <input type="hidden" name="MAX_FILE_SIZE" value="10485760">
   <input type = "hidden" name="type" value="<?php echo get('type');?>"> 
   <input type="file" name="files[]" multiple id="qi_fileselector" onChange="UpdateFileField(this.value)">
   <input type = "hidden" name="usr" value="<?php echo base64_encode(gets('usr'))?>">
   <input type = "hidden" name="pwd" value="<?php echo base64_encode(gets('pwd'))?>">
   <input type = "hidden" name="authenticated" value="<?php echo base64_encode(gets('authenticated'))?>">
   </td><td>
   <?php if ($actiontype=='images') echo '<small>Smart Sizing</small>'; ?>
   </td></tr><tr><td>
   <input type="text" size="36" id="qi_fileclick" value="Click to select a file on this computer" onClick="javascript:document.getElementById('qi_fileselector').click()" title="Select files from your local disk.">
  </td><td>
  <?php if ($actiontype=='images'){ ?>
   <select name="downsizing">
   <option value="">None</option>
   <option value="1280x1024">1280x1024</option>
   <option value="1024x768">1024x768</option>
   <option value="800x600" selected>800x600</option>
   <option value="640x480" >640x480</option>
   <option value="400x400">400x400</option>
   <option value="320x240">320x240</option>
   </select>
   <?php } ?>
   </td></tr><tr><td>
   <small>Upload To:</small>&nbsp;<input type = "text" name="destdir" size='25' value="<?php echo $uploaddir; ?>" title="Leave blank for site root, or enter a subdirectory to upload to. In Full View mode you can also click on the tree to assign a location." >
   </td><td>
   <input type="button" name="upload" value="Start Upload" onClick="DoUpload()">
   </td></tr></table>
  </form></div>
  <?php
 else:
  echo "This server does not allow browser file uploads. Sorry.";
 endif;
endif;
?>

</td></tr></table>
<script>
  function UpdateFileField(newval){
    var fa=document.getElementById('qi_fileselector').files;
    var numfiles=fa.length;
    // alert(numfiles)
    if (numfiles>1){
      var filelist=""; 
      var totalsize=0;
      var numoversize=0;
      var oversizers=""
      for (ct=0;ct<numfiles;ct++){
        filelist+=fa[ct].name + " "; 
        totalsize+=fa[ct].size;
        if (fa[ct].size>parseFloat('<?php echo ini_get('upload_max_filesize') ?>')*1024*1024) {numoversize++;oversizers+=fa[ct].name+", "}
//      alert(fa[ct].size +"|"+parseFloat('<?php echo ini_get('upload_max_filesize') ?>'));
      }
      if (numoversize==1){alert("File "+oversizers+" is larger than the size limit for individual images. This may fail to upload." )}
      if (numoversize>1){alert("Files "+oversizers+ "are larger than the size limit for individual images. These may fail to upload." )}
      totalsize=Math.round(totalsize/(1024*1024)*10)/10;
      if (totalsize==0){totalsize="<0.1"}
      // alert(totalsize);
      document.getElementById('qi_fileclick').value="{ " + numfiles + " Files selected, " + totalsize + "MB to upload }" 
      document.getElementById('qi_fileclick').title=filelist; 
    } else{
      if (fa[0].size>parseFloat('<?php echo ini_get('upload_max_filesize') ?>')*1024*1024) {
        alert("File "+newval+" is larger than the size limit for individual images. It may fail to upload.");
        document.getElementById('qi_fileclick').value=newval + " {Oversize}";
        return;
      }
      document.getElementById('qi_fileclick').value=newval;
    }
  }
  function DoUpload(action){
    var fa=document.getElementById('qi_fileselector').files;
    var filelist=fa
    numfiles=fa.length;
    if (numfiles==0){
      alert("No files selected, nothing to do.");
      return false;
    }  
    if (numfiles > <?php echo ini_get('max_file_uploads') ;?>){
      alert("Too many files selected. None uploaded." );
      return false;
    }  
    if (<?php echo gets('user_privelege') ;?> > 5){
      alert("Sorry. Uploads are not permitted in demo mode." );
      return false;
    }
    document.uploader.upload.value="Sending Data..";
    document.uploader.submit();
   
   // put chunking code here. 
   return true;
  }
  
  document.getElementById('divdirlist').style.height=window.innerHeight-40+'px'; 
//  document.getElementById('divdirlist').style.width=document.getElementById('tddirlist').width;
</script>
<?php require_once 'ajax.php'; ?>
</body></html>
