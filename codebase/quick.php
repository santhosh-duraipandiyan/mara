<?php
require_once 'core.php';
include_once 'scantree.php';
if (gets('authenticated')<1) {echo "<h2 style='color:red'>Access denied: Not logged in.</h2>"; exit; }
?><!doctype html><html><body>
<?php echo  '<script type="text/javascript" src="system.js" ></script>' . "\n";?>
<script>cms_stopdrop();</script>
<?php

if (get('mode')!=''){
  echo'<div id="q_preview1" >';
  $ilocation= 'img';
  $hostpage=get('hostpage');
  $hostpagename=stripext(basename($hostpage));
  $hostpagepath=pathname($hostpage,1);
  if (strlen($hostpagepath)>0) $ilocation=$hostpagepath . 'img';
  $ilocation=path($ilocation);
  // echo $hostpagepath . '<br>';
  if (get('mode')=='this_page') cms_imglist($ilocation, 360, 120,0, $hostpagename);
  if (get('mode')=='full_list') cms_imglist($ilocation, 360, 120,0);
  echo '</div></body></html>';
  exit;  
}

$direxclusions = array( 'cgi-bin', '.', '..', 'codebase', 'archive' ,'menu','theme','material','undo','tmp','log','.thumb');
$ck_callback=get('CKEditorFuncNum');
$actiontype=get('type');
$uploaddir="img";
$flatfile=false;
$treemode="standard";
if (gets('user_privelege')<3) $flatfile=true;
$caption="" ;
$filetypes='';
$treelist=true;

$actiontype='images';
$caption="Media Browser";
$filetypes=array('jpg','gif','png','jpeg','svg');
$initdir="img";
$uploaddir="~";
// $uploaddir needs to reflect html filename
$treelist=false;
  
?>

<link rel=stylesheet href="system.css" type="text/css">
<link rel=stylesheet href="dialog.css" type="text/css">
<?php // echo  '<script type="text/javascript" src="' .codedir. 'system.js' .jsindex. '" ></script>' . "\n";?>
<div id='adminbar'><img src="<?php echo codedir ?>/img/cms_logo.gif" style="vertical-align:middle;text-align:left;" id="adminbar_logo" >
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; Media Browser - Files in this site level
</div>
<?php
echo "<script>var actiontype = '$actiontype'; var ck_callback = '$ck_callback' </script>";
echo js_syspaths;
  foreach($_REQUEST AS $ptkey => $ptvalue) {
   ${$ptkey} = rawurldecode($ptvalue);
   //  echo "$ptkey:<input type='text' name='$ptkey' value='$ptvalue'>";
  }
?>  


<script>
mode='single';
hostpage = opener.pagefile;
</script>

<div class='cms_tab' >
<script>
function setiframetabs(thistab) {
 document.getElementById('cms_ift1').style.background='#444444';
 document.getElementById('cms_ift2').style.background='#444444';
 document.getElementById('cms_ift1').style.color='#aaaa88';
 document.getElementById('cms_ift2').style.color='#aaaa88';
 thistab.style.background='#ffe';
 thistab.style.color='#220';
 thistab.style.borderBottomWidth='0px';
}
document.write('<a href="quick.php?hostpage=' + hostpage + '&mode=this_page"  id="cms_ift1" onClick=setiframetabs(this) title="Show only those image files uploaded specifically for this page" target="qi_preview">Uploaded for this page</a>');
document.write('<a href="quick.php?hostpage=' + hostpage + '&mode=full_list"  id="cms_ift2" onClick=setiframetabs(this) title="Show all image files in this directory level of the website" target="qi_preview">All in this site section</a>');
setiframetabs(document.getElementById('cms_ift1'));
</script></div>

<iframe name='qi_preview' width="100%" height="400" id='qi_preview' src="" >Preview Area</iframe> 

 <form name="uploader" id="uploadform" action="" method="POST" target="qi_preview" enctype="multipart/form-data">
 <table border=0 class="cms_quickimg_upload" ><tr class='qi_sectiontop'><td colspan=1> 
 <label for="qi_fileclick">Local files to Add: </label><input type="file" name="files[]" id="qi_fileselector" multiple=true onChange="UpdateFileField(this.value)">
 </td><td rowspan=2 style='border:1px solid gray;text-align:center;' >Or drop a file here... </td>
 </tr><tr><td colspan=1>
 <input type="text" size="40" id="qi_fileclick" value="Click to select a file on this computer" onClick="javascript:document.getElementById('qi_fileselector').click()" title="Select one or more files from your local computer's hard disk or USB memory.">
   <input type="hidden" name="MAX_FILE_SIZE" value="10485760">
   </td></tr><tr><td><small>Smart Size Limiting:</small> <select name="downsizing" title="Sets the maximum pixel dimensions for the image.  Images smaller than the stated size will be unaffected. Larger images will be downscaled. Choose the largest you are likely to need, but preferably not larger than the maximum width of your webpage. You will be able to downsize further once placed in the page.">
   <option value="">None</option>
   <option value="1280x1024">1280x1024</option>
   <option value="1024x768">1024x768</option>
   <option value="800x600" selected>800x600</option>
   <option value="640x480" >640x480</option>
   <option value="400x400">400x400</option>
   <option value="320x240">320x240</option>
   <option value="240x200">240x200</option>
   <option value="180x120">180x120</option>
   <option value="120x80">120x80</option>
   <option value="80x80">80x80</option>
   </select> 
  </td><td>
  <input type="checkbox" id="qi_overwrite" name="overwrite" title="Check this box if the file you are uploading REPLACES an existing picture, for example where you are uploading an improved version of the same image. If it is unchecked AND the same file already exists on the server, your upload -and the link to it in the document- will be given a new filename to avoid the naming conflict." ><small>&nbsp;<label for="qi_overwrite">Allow over-writes</label></small> 

   </td></tr><tr><td>
   Align: <select name="alignment" title="Choose the placing of the image, and whether text may flow around it." >
   <option value="plain" selected>Document Default</option>
   <option value="left" >Left, in own space</option>
   <option value="float-left" >Left, with text flowing around</option>
   <option value="center" >Centrally, in own space</option>
   <option value="right" >Right, in own space</option>
   <option value="float-right" >Right, with text flowing around</option>
   <option value="" >Unstyled (HTML default)</option>
   </select>

   </td><td><input type="button" class="cms_button" name="upload" value="Add to Page" onClick="processfiles(document.uploader.qi_fileselector)" title="Press here to add the selected images to your page as pending uploads. Once you have positioned them as required, press the Save or Upload buttons on the editor toolbar.  If you change your mind, just delete the image from the page and it will not be uploaded." >

 </td></tr></table>     
 </form>  

<div style=overflow:auto;height:220px;width:99%>
<table id='pending' class='cms_pending_upload' style='border:0px solid gray;margin:10px;width:98%;' >
</table>
<div>

<script>

function setup_pendingtable(){
  var ptable=document.getElementById('pending');
  var ptext='';
  var ia=opener.document.images;
  var ed=opener.document.getElementById('editable')
  var numfiles=ia.length;
  if (numfiles==0) {return;}
  var ulct=0;
  for (var ct=0;ct<ia.length;ct++){
    if (ia[ct].src.indexOf('blob:')==0){
      ulct++;
      var iid=ia[ct].id;
      var isize=ia[ct].naturalWidth+'x'+ia[ct].naturalHeight;
      var status='not cached' ;
      if (typeof opener.uploadlist[iid]=='object'){status='cached'}

      ptext+= "<tr><td>" + ia[ct].title.substr(15) + "</td><td>" + isize + "</td><td>" + status + "</td><tr>";

    }
  }

  var aa=ed.getElementsByTagName('a')
  for (var ct=0;ct<aa.length;ct++){
    if (aa[ct].href.indexOf('blob:')==0){
      ulct++;
      var linkid=aa[ct].id;
      // var isize=aa[ct].naturalWidth+'x'+aa[ct].naturalHeight;
      var status='not cached' ;
      if (typeof opener.uploadlist[linkid]=='object'){status='cached'}
        var destfile=aa[ct].getAttribute('data_destfile');
        var abytes=opener.uploadlist[linkid].size
        var iabytes=Math.round(abytes/1024);
        if (iabytes > 1024){
          iabytes=Math.round(abytes/(1024*1024))+'MB'
        } else {
          iabytes=iabytes+'KB';
        }
        ptext+= "<tr><td>" + destfile + "</td><td>" +  iabytes + "</td><td>" + status + "</td><tr>";
    }
    
  }

  ptable.innerHTML="<tr><th>Pending Uploads</th><th>Size</th><th>Status</th></tr>" + ptext;
}

setup_pendingtable();

</script>
   
<script>

  // Accept dropped files on the form..
 document.uploader.addEventListener("drop",function(e){
  e = e || event;
  // alert(e.dataTransfer.files[0].name)
  processfiles(e.dataTransfer);
  e.preventDefault();
 },false);


  document.uploader.downsizing.value=upload_smartsize;

  function UpdateFileField(newval){
    var fa=document.getElementById('qi_fileselector').files;
    
    if (fa.length==1){
    var fsnum=fa[0].size;
    var filext=fa[0].name.split('.').pop();
    filext=filext.toLowerCase();
    if ("|jpg|jpeg|png|gif|svg|".indexOf(filext)<1){
      alert("This file does not appear to be an image of a type suitable for webpages.")
      document.getElementById('qi_fileclick').value=newval + " {Unsuitable file type}"
      return
    }
    var filesize=Math.round(fsnum/(1024*1024)*10)/10;
    if (filesize>0){
      document.getElementById('qi_fileclick').value=newval + " {"+ filesize + "MB}" 
    }else{
      document.getElementById('qi_fileclick').value=newval + " {"+ fa[0].size + " bytes}" 
    }
    }else{
      document.getElementById('qi_fileclick').value=fa.length + ' files selected';
    }
    // if (fsnum > parseFloat('<?php echo ini_get('upload_max_filesize')?>')*1024*1024){ alert('This file may be too large for upload')}
  }

 function processfiles(evt) { 
   var wul=opener.uploadlist;
   var upload_overwrite='0';
   if (document.uploader.overwrite.value=='on') {var upload_overwrite='1'}
   var upload_smartsize=document.uploader.downsizing.value;
   var upload_align=document.uploader.alignment.value; 

// var wu=opener.uploadlist;
   if (typeof wu=='undefined' || wu=='') {wu={}; wu['count']=0;}
   if (typeof evt!='undefined') { 
      processfiles.evt=evt; 
      processfiles.ct=0;
      processfiles.numfiles=evt.files.length;
   } else {
      processfiles.ct++;
      if (processfiles.ct>=processfiles.numfiles){
        processfiles.numfiles=0;
        processfiles.filect=0;
        processfiles.evt='';
        window.close();
        return;
      }
   }
   
   if (processfiles.numfiles==0){
     // No local files selcted so assume user wants selected server file.  
     alert('No local files selected');
     return;
   }
   
   
   var thisfilename=processfiles.evt.files[processfiles.ct].name; 
   var thisfile=processfiles.evt.files[processfiles.ct];
   var thissrc = window.URL.createObjectURL(thisfile);
    var bnpf=basename(hostpage);
    var bnpfl=basename(hostpage).length;
    // Stops pagename being multiply prepended.. 
    if (thisfilename.substr(0,bnpfl)==bnpf) {
      var uploadedname= thisfilename;
    } else {
      var uploadedname=bnpf + '_' + thisfilename;
    }
    
    var mimetype='';
    var filext = getExtension(thisfilename);
    var mimetype=getMimetype(filext);
    var randomID=RandomInt(1,999999).toString();
    var thisid = 'pending-upload-' + randomID ;
    
    if (mimetype.indexOf('image')==0) {     
     // For images, put temporary local URL into page.. 
     var insertion='<img id="' + thisid +'"';
     insertion += ' alt="" title="Pending upload:\nimg/' + uploadedname +'"';
     insertion += ' class="' + upload_align +' inline"';
     insertion += ' data_destfile="img/'+ uploadedname +'"';
     insertion += ' data_overwrite="' + upload_overwrite +'"';
     insertion += ' data_smartsize="' + upload_smartsize +'"'; 
     insertion += ' src="' + thissrc +'"'; 
     insertion += ' data_mimetype=' + mimetype ; 
     insertion += '  >';
     wul[thisid]=thisfile;
     wul.count++;

    } else if (mimetype.indexOf('video')==0){

     register_videobox();
          
     var insertion='<video controls="" class="'+ upload_align + ' videobox" >';
     insertion += ' <source id=' + thisid ;
     insertion += ' title="Pending upload:\nimg/' + uploadedname +'"';
     insertion += ' data_destfile="media/'+ uploadedname +'"';
     insertion += ' data_overwrite=' + upload_overwrite ;
     insertion += ' data_smartsize=""' ; 
     insertion += ' src="' + thissrc +'"'; 
     insertion += ' type=' + mimetype ; 
     insertion += ' data_mimetype=' + mimetype ; 
     insertion += ' >';
     // insertion +='Sorry. Your browser does not support HTML5 video playback.';
     insertion +='</video>'; 
     wul[thisid]=thisfile;
     wul.count++;
    } else {

     // make link for other file types. 
     var insertion=' <a id=' + thisid ;
     insertion += ' class="' + upload_align +' inline"';
     insertion += ' data_destfile="download/'+ uploadedname +'"';
     insertion += ' data_overwrite=' + upload_overwrite ;
     insertion += ' href="' + thissrc +'"'; 
     insertion += ' data_mimetype=' + mimetype ; 
     insertion += ' >';
     insertion +=  thisfilename + '</a > ';
     wul[thisid]=thisfile;
     wul.count++;
    }
   
    var doctarget = opener.CKEDITOR.instances.editable;
    doctarget.insertHtml(insertion);
  
   var ctyimg=opener.document.getElementById(thisid)

   resized_ok=opener.cms_jsresize(ctyimg); 

   var insertion='';

   setTimeout(processfiles,1000);
 return;
 }

  function DoUpload(action){
   return true;
  }

</script>

<script>
function imgPreview(){
  var imgurl = document.getElementById('fileselector').value;
  imgurl=imgurl.replace('\\','/');
  document.getElementById('qi_preview').src=imgurl;
}

function pasteImgURL(){
 // var img_url=document.uploader.webpathname.value;
  if (img_url=="" || img_url=='undefined'){
    alert ("You must upload an image, or select an existing image from the list before inserting a link to it in your webpage.");
    return false;
  }
  var caption=document.uploader.caption.value;
  var caption_class=document.uploader.caption_style.value;
  var alignment=document.uploader.alignment.value;
  var clickaction="";
  if (document.uploader.linkfullsize.checked){clickaction=' onClick="cms_imgzoom_popup(this.src)" '}
  
  // Determine image placement..
  // display=block prevents untidy text line, float=l/r allows text wraparound, margin=auto centers.    
  var img_alt="";
  var img_title="";
  var img_class=alignment;
  if (caption_class!="" && caption_class!='caption-title'){img_class += ' ' + caption_class; }
  if (caption_class=="caption-title"){img_title = caption; 
  }else{
    img_alt=caption;
  }
  var img_tag="<img src='"+img_url+"'  alt='' >";

  // alert(img_tag);
  var doctarget = opener.CKEDITOR.instances.editable;
  doctarget.insertHtml(img_tag);
  if (document.uploader.autoclose.checked==true){ window.close(); }
}

document.getElementById('qi_preview').src='quick.php?hostpage=' + hostpage + '&mode=this_page';

cms_stopdrop();
cms_popup_autoclose();

</script>

<?php 
?>
</body></html>
