<?php

// Functions loaded when in administrative mode (but not necesarily logged on) 
// - this file is only loaded in admin mode... so no need for adminmode test here. 
// global ok2admin=true can be assumed.


$echoline= '<script type="text/javascript" src="' . codedir . 'enc.js' . jsindex . '"></script>';
echo $echoline;

echo "<script>shash='".shash."';debug=".debug.";ok2edit=true;user_privelege=\"".gets('user_privelege')."\";editing_disallowed=\"".gets('editing_disallowed')."\";</script>";

?>

<div id='ajaxdiv' style='margin:0px;padding:20px;display:none;width:100%;background:grey;color:white'>
  <form name="ajax" enctype="application/x-www-form-urlencoded" method="post" action="" >
  Ajax Data Transfers:- Inputs: <br>
  <input class="inputbox" type="text"  name="usr"  size="60" maxlength="60" value="" >usr<br>
  <input class="inputbox" type="text"  name="hash"  size="60" maxlength="60" value="" >hash<br>
  <input class="inputbox" type="text"  name="pwd"  size="60" maxlength="60" value="" >salted_hash<br>
  <input class="inputbox" type="text"  name="authenticated"  size="60" maxlength="60" value="0" >auth<br>
  <input class="inputbox" type="text"  name="action"  size="60" maxlength="20" value="" >action<br>
  <textarea name="headsection" id="headsection" rows=5 cols=100 wrap="off"></textarea><br>
  <textarea name="data" id="ajaxdata" rows=10 cols=100 wrap="off"></textarea><br>
  <input class="inputbox" type="text"  name="crc"  size="60" maxlength="255" value="" >crc<br>
  <input class="inputbox" type="text"  name="srcfile"  size="60" maxlength="255" value="" >srcfile<br>
  <input class="inputbox" type="text"  name="destfile"  size="60" maxlength="255" value="" >destfile<br>
   Outputs: <br>
  <input class="inputbox" type="text"  name="rawresponse"  size="60" maxlength="20000" value="" >raw response<br>
  <textarea name="response" rows=5 cols=100 wrap="off"></textarea><br>
  <input class="inputbox" type="text"  name="status" size="60" maxlength="20" value="unknown" >status<br>
  <input class="inputbox" type="text"  name="error" size="60" maxlength="20" value="" >error<br>
  </form>
  <br style="clear:both;" >
</div>

<script>

if (debug) {document.getElementById('ajaxdiv').style.display='block'}
document.ajax.usr.value="<?php echo gets('usr');?>";
document.ajax.pwd.value="<?php echo gets('pwd');?>";
document.ajax.authenticated.value="<?php echo gets('authenticated');?>";


// JS Functions from this point on.... 

function ajax(action) {
 var isasync=false;
 if (action=='xxxkeepalive'){isasync=true;}
 // sends multiple data items to server, base64 encoded, and gets back a single response plus error flag. 
 document.ajax.action.value=action;   
 document.ajax.error.value="";   
 document.ajax.status.value="Sending Request";   
 document.ajax.response.value = "";   
 if (window.XMLHttpRequest) {
   AjaxRequest = new XMLHttpRequest();
 } else {
   AjaxRequest = new ActiveXObject("Microsoft.XMLHTTP");
 }
 var sepchar="";
 var envvars="";
 var fieldcount=document.ajax.elements.length; 
// alert(fieldcount)
 for (thisfield=0;thisfield<fieldcount;thisfield++) {
   var tsd=document.forms["ajax"].elements[thisfield];
   if (typeof(tsd)=='undefined') continue;
   var enctsdval=base64_encode(tsd.value);
   envvars = envvars + sepchar + tsd.name + "=" + enctsdval;
   sepchar = "&";
   if (tsd.name=='data') {
     envvars = envvars + sepchar + "enccrc" + "=" + cms_hash(enctsdval);
   }
  }
 // document.ajax.hash.value= envvars;
 nocache = Math.random();  
 AjaxRequest.open('post',codedir + 'handler.php?nocache='+nocache,isasync);
 AjaxRequest.setRequestHeader("User-Agent",'Firefoxy');
 AjaxRequest.onreadystatechange = acallback;
 // AjaxRequest.setRequestHeader("Accept", "text/plain");
 // No particular need for unicode as all data is in base64: 
 // AjaxRequest.setRequestHeader("charset", "utf-8");
 AjaxRequest.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
 AjaxRequest.setRequestHeader("Content-length", envvars.length);
 AjaxRequest.setRequestHeader("Connection", "close");
 AjaxRequest.send(envvars);
}

function acallback() {
 // gets ajax response from server when in async mode. 
 document.ajax.status.value="Getting Reply";   
 if(AjaxRequest.readyState == 4){
   // document.ajax.status = AjaxRequest.status; 
   if (AjaxRequest.status == 200) { 
     var rawrtn=AjaxRequest.responseText
     var artn=rawrtn.split('~::~');
     if (artn.length=3){
         var rtn=base64_decode(artn[1]);
     }
     document.ajax.rawresponse.value = rawrtn;
     document.ajax.response.value = rtn;
     document.ajax.status.value="OK";   
   } else {
     document.ajax.error.value="Error contacting server-side data handler"
     document.ajax.status.value="Fail";   
   }   
 } else {
   document.ajax.error.value="Data Request failed to complete"; 
   document.ajax.status.value="Fail";   
 }
 if (!debug) {
    // Clear input values to avoid any confusion, unless testing..
    document.ajax.action.value="";   
//    document.ajax.data.value="";   
//    document.ajax.srcfile.value="";   
//    document.ajax.destfile.value="";   
    document.ajax.crc.value="";   
 }
 if (typeof kato_lastping !== 'undefined') {
    // reset ping timeout...
    var d = new Date();
    var timenow = parseInt(d.getTime()/1000);
    kato_lastping=timenow;
 }
}

  function fileinfo() {
    ajax('info');
  }
  
  function dirlist(thisdir) {
    // Not currently used, as CKEditor uses non-ajax browse from popup window.  
    ajax('setsalt');
    nacl=document.ajax.response.value;
    var user=document.ajax.usr.value;
    var $pwdhash=document.ajax.hash.value;
    var pwdshash=cms_hash($pwdhash + nacl);
    document.ajax.pwd.value=pwdshash;
    document.ajax.action.value="dirlist";
    document.ajax.srcfile.value=thisdir;
    document.ajax.destfile.value="";
    ajax('dirlist');
// maybe need some error traps here;   
    document.getElementById('filebrowser').innerHTML=document.ajax.response.value;
  }
  
    
  function SaveFile(action) {
        if (typeof action =='undefined') { var action='' ; }
    if (!ok2edit){
      alert("Editor must be ON in order to save the editable area of page.");
      return;
    }
    var savenote="";
    var filtpagestr="";
    var oorc=0;

    if (action != 'waiting') {
      var rtn=cms_uploader('save');
      if (rtn=='busy') {return;}
    }
    // Check for media that needs uploading first.  
    // Since uploader is recursive, look for idle flag as indicating completion. 
    if (typeof window.upload!='undefined') {
      var wu=window.upload;
         if (wu.status=='abort' || wu.status=='error'){
           var yesno=confirm('Media upload did not complete, therefore saved page may have some missing content.\n -Do you still wish to save the page text? ');
             if (!yesno) {
                cms_uploader('abort')
                ckumsg('Save cancelled', 'warning');
               return;
             }
      } 
      if (wu.status!='alldone' && wu.status!='abort' ){
         setTimeout(function(){SaveFile('waiting')},500)
         return;
      }
    }
    var pagestr=CKEDITOR.instances.editable.getData();
    document.ajax.data.value = pagestr;
    document.ajax.action.value="save";
    var thiscrc=cms_hash(document.ajax.data.value);
    document.ajax.crc.value=thiscrc;
    document.ajax.srcfile.value=pagefile;
    document.ajax.destfile.value=pagefile ; 
    // document.ajax.destfile.value=pagefile + '.save';
    ajax('save');
    if (document.ajax.response.value.indexOf("OK")==0) {
      if (savenote==""){
        // savenote="\n\n" + document.ajax.response.value.replace("OK:","");
      }
      CKEDITOR.instances.editable.resetDirty();
      ckumsg("Document saved as: " + document.ajax.destfile.value, 'success', 5);// + "\n\n("+fsroot+document.ajax.destfile.value+")\n\n"+savenote, 'success',7);
    } else {
      if (document.ajax.response.value.length>0){
         ckumsg(document.ajax.response.value, 'warning');
      } else {
         ckumsg("Save Failed: No reponse from server", 'warning');
      }
    }
  }


function UploadImages() {

cms_imguploader();

return;
/*
   // Old data URL method.. 
    var adataimages=document.getElementById('editable').getElementsByTagName('img');
   
    for (ct=0;ct<adataimages.length;ct++) {
      thisimg=adataimages[ct];
      var thissrc=thisimg.src ; 
      if (thissrc.indexOf('data:')>-1) {
         var parts=thissrc.split(',');
         var thisdata=parts[1];
         var prefixes=parts[0].split(':');
         var scheme=prefixes[0];
         var params=prefixes[1].split(';');
         var thisupload={}
         var athist= thisimg.title.split(':');
         if (athist.length==2){thisupload.name=athist[1]}
         thisupload.data=thisdata;
         thisupload.encoding='base64';
         thisupload.action='ckupload';
         thisupload.id=thisimg.id;
         thisupload.title=thisimg.title;
         
         thisupload.destfile=thisimg.data_destfile;
         thisupload.overwrite=thisimg.data_overwrite;
         thisupload.smartsize=thisimg.data_smartsize;
         thisupload.mimetype=thisimg.data_mimetype;
          
         Ajax2(thisupload);
         var thisupload=null;
     }
    }

return;
*/
}


function Ajax2(ajaxupload){
// alert("Ajax2 call:" + ajaxupload.action + " data: " + ajaxupload.data);
 // sends single data stream to server, base64 encoded.
 if (window.XMLHttpRequest) {
   var Ajax2Request = new XMLHttpRequest();
 } else {
   var Ajax2Request = new ActiveXObject("Microsoft.XMLHTTP");
 }
 var sepchar="&";
 var envvars = "usr" + "=" + base64_encode("<?php echo gets('usr');?>");
 // envvars = envvars + sepchar + "pwd" + "=" + base64_encode("<?php echo gets('pwd');?>");
 envvars = envvars + sepchar + "pagefile" + "=" + base64_encode(pagefile);
 envvars = envvars + sepchar + "pagedir" + "=" + base64_encode(pagedir);
 for (var item in ajaxupload) {
   if (typeof(ajaxupload[item])=='undefined') continue;
   if (item=='data') {
     // alert("Data " + ajaxupload[item].length);
     // allows for data which is already base64 encoded 
     if (ajaxupload['encoding']!='base64') {
        envvars = envvars + sepchar + item + "=" + base64_encode(ajaxupload[item]);
     } else {
        envvars = envvars + sepchar + item + "=" + ajaxupload[item];
     }
       envvars = envvars + sepchar + "enccrc" + "=" + cms_hash(ajaxupload[item]);
       envvars = envvars + sepchar + "size" + "=" + base64.encode(ajaxupload[item].length.toString());
   } else {
       envvars = envvars + sepchar + item + "=" + base64_encode(ajaxupload[item]);
   }
  }
 nocache = Math.random();
 ajaxupload.data=''; // Reduce size of callback data  
  Ajax2Request.open('post',codedir + 'handler2.php',false);
 Ajax2Request.setRequestHeader("User-Agent",'MaraCMS');
 Ajax2Request.onreadystatechange = a2callback;
 Ajax2Request.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
 Ajax2Request.setRequestHeader("Content-length", envvars.length);
 Ajax2Request.setRequestHeader("Connection", "close");
 Ajax2Request.send(envvars);
}

function a2callback() {

// alert("Callback: " + this.readyState+" | "+ this.status +" RT:"+ this.responseText);

 var rawrtns='';
 var status='';
 var returns={};
 
 if(this.readyState == 4){
   if (this.status == 200) { 
     rawrtns=this.responseText;
     status='OK';
   } else {
     status='Data transmission error';
   }
 }

// Protect against garbage at start of callback data.. 
  var arawrtns=rawrtns.split('~::~');
   if (arawrtns.length==3){
       rawrtns=arawrtns[1];
       arawrtns='';
   }else{
     // alert(rawrtns);
     return;
   }
if (status!='OK') {
  alert(status);
  return; 
}

var artns=rawrtns.split(';')

for (var ct in artns) {
 // alert('arts' + artns[ct]);
 var artn=artns[ct].split(':')
 returns[artn[0]]=base64_decode(artn[1]);
}

/*
alert('status ' + returns['status']);
alert('id ' + returns['id']);
alert('name '+ returns['name']);
alert('title ' + returns['title']);
alert('crc ' + returns['crc']);
*/
   
   var thisimg=document.getElementById(returns['id']);
   thisimg.src=returns['name'];
   thisimg.setAttribute('data-cke-saved-src',returns['name']);
   thisimg.removeAttribute('data_destfile');
   thisimg.removeAttribute('data_overwrite');
   thisimg.removeAttribute('data_smartsize');
   thisimg.removeAttribute('data_mimetype');
   thisimg.title=returns['name'];
   thisimg.id='';
}


// ********************************************************************************************





// end ajax JS functions.

</script>


