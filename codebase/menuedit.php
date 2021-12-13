<?php
require_once 'core.php';
?>
<!doctype html>
<html><head>
<title>Mara Menu Editor</title>
<link rel="stylesheet" href="system.css" type="text/css"> 
<link rel="stylesheet" href="dialog.css" type="text/css"> 

<meta http-equiv="Content-Type" content="text/html; charset=utf-8">

</head><body>
<div id='adminbar'><img src="<?php echo codedir ?>/img/cms_logo.gif" style="vertical-align:middle;text-align:left;" id="adminbar_logo" >
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; System Menu Editor. &nbsp;&nbsp;&nbsp; Editing file: <?php echo mainmenu; ?>
</div>


<?php
include_once 'ajax.php';

// syspaths();
// ***** Needs converting to use constants: 
      $siteroot=gets('siteroot');
      $fsroot=gets('fsroot');
// session_set(sitename,siteroot);

echo js_syspaths;

$menufile=mainmenu;
if (get("menufile")!="") $menufile=get("menufile");

if (get('action')=='testmenu'){
 testmenu();
 exit;
}

if ( gets('authenticated')!=='1' ): echo("Fail, not logged in."); exit; endif; 

$menudir= configdir;
// this runs from codebase.
if (stripos($menufile,"/")===false) $menufile=$menudir . $menufile;
$fsmenufile=$fsroot . $menufile;
if (file_exists($fsmenufile)):
 $data=file_get_contents($fsmenufile);
else:
 echo"[Menu file missing. Cannot proceed.]";
 exit; 
endif;
?>

<table border=0 cols=2 height=100$ width=98%><tr><td style='text-align:center'> 
<form name="menueditor" action="handler.php" method="POST" target="menu_preview_frame" enctype="multipart/form-data">
  <textarea name="data" id='cms_menu_data' rows='22' cols='80'><?php echo $data?></textarea><br>
  <div class='cms_menuedit_buttons'>
  <input class="inputbox" type="text" readonly="readonly" name="menufile"  size="20" maxlength="255" title="Menu file" value="<?php echo "$menufile";?>" >
  <!-- standard ajax handler fields.. though this doesn't use an ajax return mechanism.-->
  <input class="inputbox" type="hidden"  name="usr"  size="60" maxlength="60" value="<?php echo base64_encode(gets('usr'))?>" >
  <input class="inputbox" type="hidden"  name="pwd"  size="60" maxlength="60" value="<?php echo base64_encode(gets('pwd'))?>" >
  <input class="inputbox" type="hidden"  name="authenticated"  size="60" maxlength="60" value="<?php echo base64_encode(gets('authenticated'))?>" >
  <input type="hidden"  name="action"  size="60" maxlength="255" value="" >
  <input type="hidden"  name="charset"  size="60" maxlength="255" value="utf-8" >
  <input type="button" class="button" name="Cancel" value="Cancel" onClick="javascript:canceledit()">
  <input type="button" class="button" name="Test" value="Test" onClick="javascript:testmenu()">
  <input type="button" class="button" name="Save" value="Save" onClick="javascript:savemenu()">
  </div>
  <php include_once('ajax.php'); ?>
</td><td  style='text-align:center'>
<iframe name='menu_preview_frame' id='menu_preview_frame' src='javascript:' >Preview Area</iframe>
</td></tr><tr><td colspan=2 style='text-align:center'>
<iframe sandbox name='target_preview_frame' id='target_preview_frame' src='javascript:' >Preview Area</iframe>
</td></tr></table>

<script>
saved_menu_data=document.menueditor.data.value;
MenuSaveReminder('on');

function testmenu() {
 var mtxt=document.menueditor.data.value;
 document.menueditor.data.value=base64_encode(document.menueditor.data.value);
 document.menueditor.action.value=base64_encode('menutest');  
 document.menueditor.submit();  
 document.menueditor.data.value=mtxt;
}

function savemenu() {
 var mtxt=document.menueditor.data.value;
 var menufile=document.menueditor.menufile.value;
   document.ajax.data.value=mtxt;
   document.ajax.action.value="menusave";
    thiscrc=cms_hash(document.ajax.data.value);
    document.ajax.crc.value=thiscrc;
    document.ajax.srcfile.value=menufile;
    document.ajax.destfile.value=menufile; // + '.save';
    ajax('menusave');
    if (document.ajax.response.value.substring(0,2) == "OK") {
      saved_menu_data=document.menueditor.data.value;
      alert("Menu saved as: " + menufile + "\n\n("+fsroot+menufile+")");
    } else {
      alert(document.ajax.response.value);
    }
}

function canceledit(){
  window.close();
}

function MenuSaveReminder(thisaction) {
 if (typeof document.menueditor.data.value == 'undefined') {return};
 if (thisaction=='on') { 
   window.onbeforeunload = function(e)
      {
        var message='You have unsaved changes in the menu editor.';
          e = e || window.event;
              if (document.menueditor.data.value != saved_menu_data) {
                // For IE and Firefox
                if (e) {
                   e.returnValue = message;
                }
               // For Safari
               return message;
              }
         }
 } 

 if (thisaction=='off') { 
    window.onbeforeunload = function(e){}
 }
} // end saveReminder 

</script>
</body></html>
