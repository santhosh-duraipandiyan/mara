<!doctype html><html><head><title>Mara Page Source Editor</title></head><body onLoad='src_loadsrc()'>
<link rel="stylesheet" href="system.css" type="text/css"> 

<?php
require_once 'core.php';
if (gets('authenticated')<1) {echo "<h2 style='color:red'>Access denied: Not logged in.</h2>"; exit; }
if (gets('user_privelege') < sourcecode_editlevel) {echo "<h2> Sorry. You have insufficient priveleges to edit sourcecode. If you think you need this privelege, ask your webmaster.</h2>"; exit; }
require_once 'ajax.php';
echo js_syspaths;
?>

<form name="srceditfrm" enctype="multipart/form-data" method="post" style="display:block;text-align:center">
<textarea name="headtxt" id="headtxt" style="width:100%" title="Document HEAD section" ></textarea>
<textarea name="srctxt" id="srctxt" style="width:100%" title="Document BODY section"></textarea>
<input type="button" class="button" value="Quit" onClick="window.close()">
<input type="button" class="button" value="Revert to Start" onClick="src_revert()">
<input type="button" class="button" value="Reload from Editor" onClick="src_reload()">
<input type="button" class="button" value="Write Back"  onClick="src_writeback()">
<input type="button" class="button" value="Write & Quit"  onClick="src_commit()">
</form>

</body>

<script>

function src_loadsrc(){
// Get headsection from editor page if present, or from server file if not.. 
 document.srceditfrm.headtxt.value=opener.document.ajax.headsection.value;
 if (document.srceditfrm.headtxt.value=="") {
   document.ajax.srcfile.value=opener.pagefile;
   ajax('getheadsection');
   if (document.ajax.status.value=="OK"){
     document.srceditfrm.headtxt.value=document.ajax.response.value;
   }
 }
 document.srceditfrm.headtxt.style.height='100px';
 initialheadsection=document.srceditfrm.headtxt.value;

// Get page source from editor: 
 initialsrccode=opener.CKEDITOR.instances.editable.getData();
 document.ajax.data.value=opener.CKEDITOR.instances.editable.getData();     
  ajax('decode_scripts'); 
  if (document.ajax.status.value=="OK"){
//Test
    initialsrccode=document.ajax.response.value;
  }
 document.srceditfrm.srctxt.value=initialsrccode;
 document.srceditfrm.srctxt.style.height=(window.innerHeight-160)+'px';

 if (document.srceditfrm.headtxt.value.length==0){
  document.srceditfrm.headtxt.style.display='none';
  document.srceditfrm.srctxt.style.height=(window.innerHeight-50)+'px';
 }
}

function src_writeback() {
  if (initialheadsection!=document.srceditfrm.headtxt.value){
    opener.document.ajax.headsection.value=document.srceditfrm.headtxt.value;
  }else{
    opener.document.ajax.headsection.value="";
  }
  var parentsrccode=opener.CKEDITOR.instances.editable.getData();
  // alert(parentsrccode +" | "+ initialsrccode);
  if (parentsrccode!=initialsrccode){
    var ok2proceed=confirm("The page being displayed in the browser has been changed since the sourcecode editor was launched. Are you sure you want to over-write the page shown in the browser with the sourcecode in this window?")
    if (ok2proceed==false){return false}
  }
  document.ajax.data.value=document.srceditfrm.srctxt.value;     
  ajax('encode_scripts'); 
  // alert(document.ajax.status.value);
  if (document.ajax.status.value=="OK"){
  // alert(document.ajax.response.value);
  if (document.ajax.response.value=="undefined"){return false}
  opener.document.getElementById('editable').innerHTML=document.ajax.response.value;
  }
  initialsrccode=opener.CKEDITOR.instances.editable.getData();
  return true;
  if (initialheadsection!=document.srceditfrm.headtxt.value){
    opener.document.ajax.headsection=document.srceditfrm.headtxt.value;
  }
}

function src_reload() {
  document.ajax.data.value=opener.CKEDITOR.instances.editable.getData();     
  ajax('decode_scripts'); 
  if (document.ajax.status.value=="OK"){
    document.srceditfrm.srctxt.value=document.ajax.response.value;
    ajax('getheadsection');
    if (document.ajax.status.value=="OK"){
      document.srceditfrm.headtxt.value=document.ajax.response.value;
    }
  }
}

function src_revert() {
  document.forms.srceditfrm.srctxt.value=initialsrccode;
  document.srceditfrm.headtxt.value=initialheadsection;
}

function src_commit() {
 if (src_writeback()){window.close()};
}


</script>


</html>



