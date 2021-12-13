<?php

/*
Mara Gallery script, copyright IWR Consultancy, 2016.
Subject to Mara CMS licence terms. 
*/

if (!defined('fsroot')) {
  // set up environment if this is a standalone instance.. 
  include_once 'codebase/core.php';
  include_once 'codebase/resizer.php';
  syspaths();
  echo js_syspaths;
  echo '<link rel=stylesheet href="'.plugindir.'plugin.css" type="text/css">'."\n";
  echo '<script type="text/javascript" src="'.plugindir.'plugin.js"></script>'."\n";
  define('mara_cms',false);
} else { 
  define('mara_cms',true);
  // if running in CMS, just make sure GDImage is loaded.. 
  include_once fsroot . 'codebase/resizer.php';
}

// Create layers for slideshow...
setg('cms_gallery_itemcount',0);
gallery_imgzoom();

// No more to do until some images are loaded, so.. 
return; 

function gallery_browser($gallery='',$sliderate=5,$pre_height="100px",$pre_width="250px"){
// Loads a batch of images and creates a contact sheet... 
$fullrefresh=false;
if(isset($_GET['refresh'])):
    $fullrefresh=true;
endif;
$plugindir=thisfsdir(__FILE__);
$simulation=0;
$recursive=true;
$itemcount=0;
if (mara_cms){
  if ($gallery=='') $gallery=pathname(pagefile);
  $gallery=path($gallery);
} else {
  $gallery=pathname(pagefile).$gallery;
}

$thumbdir=".thumb/";
if (strlen($thumbdir)<1 || strpos($thumbdir,"..")!==false || strpos($thumbdir,"/")<2) $thumbdir=".thumb/";

$dcontents=cms_gallery_scantree($gallery,0,$recursive,'gif,jpg,jpeg,png,avi,mkv,mov,flv,swf');
$thumb_batch=50;
$imglist='gif,jpg,jpeg,png';
$imgtypes=explode(',',$imglist);
$vidtypes=explode(',','avi,mkv,mov');
$flvtypes=explode(',','flv,swf');

echo '<center class="notranslate">';

if (count($dcontents)<0) {
    echo "<h2>Gallery directory appears to be empty.</h2>";
    return;
}    
$stale_ct=0;
foreach($dcontents as $ordinal=>$thisitem) {
$absordinal=getg('cms_gallery_itemcount')+$ordinal;
//    echo $thisitem['webthumbpath']."<br>";
  $mediatype='';
   if(in_array($thisitem['type'],$imgtypes)) $mediatype="image";
   if(in_array($thisitem['type'],$vidtypes)) $mediatype="video";
   if(in_array($thisitem['type'],$flvtypes)) $mediatype="flv";
   if ($mediatype==='') continue;
   $itemcount++; 
   $thisitem['description']="File: ". $thisitem['name']. "\nLocation: " .$thisitem['relpath']."\nUploaded: ".@date('Y/m/d', $thisitem['time'])."\nSize: ".cms_gallery_formatBytes($thisitem['size']);
   // Maintain thumbnails...

  
   if ($mediatype=="video"){
     $thisitem['webthumbpath']=$plugindir."video.jpg";
   }
   if ($mediatype=="flv"){
     $thisitem['webthumbpath']=$plugindir."flv.jpg";
   }
  
   if ($mediatype=="image"){
     // echo "--- ".$thisitem['relpath'];
    $thumb_stale=0;
    if ($fullrefresh) $thumb_stale=1;
     if (!file_exists($thisitem['fsthumbpath'])){
      $thumb_stale=1; 
    }else{
      if (filemtime($thisitem['fspath'])>filemtime($thisitem['fsthumbpath'])) $thumb_stale=1;
    }
    if ($thumb_stale && ($stale_ct<$thumb_batch)){
      $stale_ct++; 
      $image=new GDImage(); 
      if($image->load($thisitem['fspath'])){
        $resized=$image->setmaxsize($pre_width,$pre_height);
        if ($simulation) {
           echo "Resizing". $thisitem['name'] ."<br>";
           echo "Saved thumb:" . $thisitem['fsthumbpath'].'<br>';
           $thumb_stale=0;
        }else{
           if (!file_exists(dirname($thisitem['fsthumbpath']))) mkdir(dirname($thisitem['fsthumbpath']), 0777, true);
           $imgsaved=$image->save($thisitem['fsthumbpath']);
           // if ($imgsaved) $thumb_stale=0; **** GD lib routine seemingly doesn't return status. 
           $thumb_stale=0;
           // echo $thumb_stale;
           touch($thisitem['fsthumbpath'],filemtime($thisitem['fspath']));
        }
      }
    }
    if ($thumb_stale) $thisitem['webthumbpath']=$plugindir."nopreview.jpg";
   }

  if ($thisitem['webthumbpath']==""){
    $thisitem['webthumbpath']=$plugindir."other.jpg";
  }

  $webthumbpath=$thisitem['webthumbpath'];
  $webpath=$thisitem['webpath'];
  $description=$thisitem['description'];
  $sizing="";
  if ($mediatype=="video" || $mediatype=="flv" || $thumb_stale) $sizing="width=".$pre_height." height=".$pre_height;
  $thisaction="";
  if ($mediatype=="image") $thisaction="onClick='javascript:return cms_gallery_zoom(\"$absordinal\",true)'";
  
  $out = "<a href='$webpath' $thisaction class='cms_gallery'>";
  $out .= "<img src='$webthumbpath' title='$description' class='cms_gallery' name='$webpath' id='$absordinal' alt='$absordinal;$mediatype' $sizing> </a>\n";
      
 echo $out;
}

if ($itemcount==0) echo "<h2>No media files in this gallery.</h2>";
$thisitemcount=getg('cms_gallery_itemcount')+$itemcount;
setg('cms_gallery_itemcount',$thisitemcount);

// gallery_imgzoom(); //puts image div and buttons in place
echo "</center><script>var cms_gallery_itemcount=$thisitemcount;var cms_gallery_rate=$sliderate;</script>";

return;
}
// ****************************************

function cms_gallery_scantree($startdir, $include_hidden=false, $recursive=true, $extensions="",$exclusions="",$dirlimit=1000, $thumbdir='.thumb'){
// Returns an array of file data. 
$dirct=0;
$depth=0;
$files=array(); // php7 ;
$thumbdir=path($thumbdir);
if ($exclusions<>""){
  $direxclusions=explode(",",strtolower($exclusions)); 
}else{
  $direxclusions[]="*";
} 

if ($extensions<>""){
  $filetypes=explode(",",strtolower($extensions)); 
}else{
  $filetypes="*";
}    

// $startdir is always relative to site root. 
if (substr($startdir,0,1)=="/") $startdir=substr($startdir,1);
$scanq[]=$startdir;
$ordinal=0;
while (true) :
 // $thispath is relative to website root 
 $thispath=path(array_shift($scanq));
 if ($thispath==null) break;
 $depth=str_word_count($thispath, 0, '3');
 $dcontents = scandir(fsroot . $thispath);
 natsort($dcontents);
 $dirct++;
 
 foreach ($dcontents as $thisitem) {
   if(!$include_hidden && (substr($thisitem,0,1)==".") ) continue;
   $dir=is_dir(fsroot.$thispath.$thisitem);
   if ($dir) {
     if(!in_array(strtolower($thisitem),$direxclusions)) $scanq[]=path($thispath . $thisitem);
   } else {
//     if(substr($thisitem,-6,2)=="_t") continue; // Skip thumbs generated by IrfanView
     $thisext = strtolower(substr(strrchr($thisitem, '.'), 1));
     if(($filetypes=="*") || (in_array($thisext,$filetypes))) {
       $ordinal++; 
       $files[$ordinal]['name']=$thisitem;     
       $files[$ordinal]['relpath']=$thispath;     
       $files[$ordinal]['webpath']=siteroot.$thispath.$thisitem;     
       $files[$ordinal]['fspath']=fsroot.$thispath.$thisitem;     
       $files[$ordinal]['type']=$thisext;
       $files[$ordinal]['size']=filesize(fsroot.$thispath.$thisitem);
       $files[$ordinal]['time']=filemtime(fsroot.$thispath.$thisitem);
       $files[$ordinal]['webthumbpath']=siteroot.$thispath.$thumbdir.$thisitem;
       $files[$ordinal]['fsthumbpath']=fsroot.$thispath.$thumbdir.$thisitem;
     }
   }
 }
 if ($recursive!=true || $dirct>$dirlimit) {
    break;
 }

endwhile;
if (!is_array($files)) $files[]=array(); //php7 ;
return $files;
}

function cms_gallery_formatBytes($bytes, $precision = 2) { 
    $units = array('B', 'KB', 'MB', 'GB', 'TB'); 

    $bytes = max($bytes, 0); 
    $pow = floor(($bytes ? log($bytes) : 0) / log(1024)); 
    $pow = min($pow, count($units) - 1); 

    // Uncomment one of the following alternatives
    $bytes /= pow(1024, $pow);
    // $bytes /= (1 << (10 * $pow)); 

    return round($bytes, $precision) . ' ' . $units[$pow]; 
} 

function gallery_imgzoom(){

?>
<div id="cms_gallery_zdiv" class='dragscroll' onMouseDown="cms_gallery_imgclick(1,event.clientX,event.clientY)" onClick="cms_gallery_imgclick(0,event.clientX,event.clientY)"> 
<div id="cms_gallery_zzdiv"></div> <!-- zzdiv is necesssary for preloading -->
<img src="" id="cms_gallery_zimage" alt="" title="" border=0 >

<form name="cms_gallery_buttons" onClick='evcb(event)'>

<input type="button" class="cms_gallery_topbutton" id="cms_gallery_topzoombutton" name="topzoom" title="Zoom/Unzoom viewer" onClick="evcb(event);cms_gallery_togglezoom();" value=" " >

<!--input type="button" class="cms_gallery_topbutton" id="cms_gallery_topzoombutton" name="topmin" title="Zoom/Unzoom viewer" onClick="evcb(event);cms_gallery_togglezoom(0);" value="<>" /-->
<input type="button" class="cms_gallery_topbutton" id="cms_gallery_topclosebutton" name="topclose" title="Close viewer" onClick="evcb(event);cms_gallery_unzoom();" value="X" />
<input type="button" class="cms_gallery_overlaybutton" id="cms_gallery_prevbutton" name="xprev" title="Go to previous image in set " onClick="evcb(event);cms_gallery_goto('-1');" value="&lt;" />
<input type="button" class="cms_gallery_overlaybutton" id="cms_gallery_nextbutton" name="xnext" title="Go to next image in set " onClick="evcb(event);cms_gallery_goto('+1');" value="&gt;" />

</form>
<form name="cms_gallery_controls" id="cms_gallery_controls" onClick='evcb(event);cms_gallery_showControls("on")'>

<div style='display:inline-block;' notonMouseOver='evcb(event);cms_gallery_showControls("on")' onClick='evcb(event)'>
<img alt="" src="<?php echo thisfsdir(__FILE__);?>/buttons/first.png"  class="cms_gallery_button" name="first" title="Go to first image in set" onClick="evcb(event);cms_gallery_goto('first');"  />
<img alt="" src="<?php echo thisfsdir(__FILE__);?>/buttons/prev.png"  class="cms_gallery_button" name="prev" title="Go to previous image in set " onClick="evcb(event);cms_gallery_goto('-1');" />
<span class="cms_gallery_button" name="ordinal" id="cms_gallery_ordinal" title="Image number in series" ></span>
<img alt="" src="<?php echo thisfsdir(__FILE__);?>/buttons/next.png"  class="cms_gallery_button" name="next" title="Go to next image in set" onClick="evcb(event);cms_gallery_goto('+1');" />
<img alt="" src="<?php echo thisfsdir(__FILE__);?>/buttons/last.png"  class="cms_gallery_button" name="last" title="Go to last image in set" onClick="evcb(event);cms_gallery_goto('last');" />
<img alt="" src="<?php echo thisfsdir(__FILE__);?>/buttons/random.png" class="cms_gallery_button" name="random" title="Random image from set" onClick="evcb(event);cms_gallery_goto('random');" />
<img alt="" src="<?php echo thisfsdir(__FILE__);?>/buttons/minus.png"  class="cms_gallery_button" id="cms_gallery_slower" name="slower" title="Slower" onClick="evcb(event);cms_gallery_setrate(1)" />
<img alt="" src="<?php echo thisfsdir(__FILE__);?>/buttons/slideshow.png"  id="cms_gallery_slideshow" class="cms_gallery_button" name="slideshow" title="Slideshow" onClick="evcb(event);cms_gallery_goto('slideshow');" />
<img alt="" src="<?php echo thisfsdir(__FILE__);?>/buttons/plus.png"  class="cms_gallery_button" id="cms_gallery_faster" name="faster" title="Faster" onClick="evcb(event);cms_gallery_setrate(-1)" />
<img alt="" src="<?php echo thisfsdir(__FILE__);?>/buttons/zoom0.png"  class="cms_gallery_button" name="zoom" title="Smart Zoom" onClick="evcb(event);cms_gallery_togglezoom();" />

<!--
<select name='zoom_mode' onChange=cms_gallery_setzoom(this.value)>
  <option value=1>Actual size</option>
  <option value=2 selected>Smart sizing</option>
  <option value=3>Zoom to nearest edge</option>
  <option value=4>Zoom to fill window</option>
</select>
-->

<img src="<?php echo thisfsdir(__FILE__);?>/buttons/close.png"  class="cms_gallery_button" name="close" title="Close this viewer and return to gallery" onClick="evcb(event);cms_gallery_goto('exit');" />

</div>
</form>
</div>
<div id="cms_gallery_zpdiv" style="position:fixed;top:-4000px;left:-4000px;border:none;z-index:1;" >&nbsp;</div>
<script type="text/javascript">

// globals determining event states.. 
cms_gallery_spinner_event=0;
cms_gallery_slide_event=0;
cms_gallery_rate=10;
cms_gallery_zoomout=0;
cms_gallery_zmode=2;
cms_gallery_opacity = 1;

document.body.onkeydown=function(event) {cms_gallery_getKeystroke(event)};
cms_gallery_plugindir="<?php echo thisfsdir(__FILE__);?>";
cms_gallery_israndom=false;
 
function evcb(thisevent){
 thisevent.cancelBubble=true
}

function cms_gallery_setzoom(mode){
  cms_gallery_zmode=mode;
  // no current action 
}

function cms_gallery_imgclick(mstatus,mousex,mousey) {
   // Toggles control visibility if mouse is clicked without dragging.
   if (mstatus==1){
     
     cms_gallery_initmousex=mousex;
     cms_gallery_initmousey=mousey;
   }
   if (mstatus==0){
     if ((typeof cms_gallery_initmousex)=='undefined'){return false}

     if (Math.abs(mousex - cms_gallery_initmousex) < 10) {
       if (Math.abs(mousey - cms_gallery_initmousey) < 10) {
         cms_gallery_showControls('toggle');
       }
     }
   }
}

 function cms_gallery_zoom(ordinal,firstload){
    cms_gallery_spinner(2000);
    document.getElementById('cms_gallery_underlay').style.display=('block');
    var zdiv = document.getElementById('cms_gallery_zdiv'); 
    var zpreload = document.getElementById('cms_gallery_zpreload'); 
    var zpdiv = document.getElementById('cms_gallery_zpdiv'); 
    var thumb=document.getElementById(ordinal); 
    var ctstatus=document.getElementById('cms_gallery_ordinal');
    var timerstatus=document.getElementById('cms_gallery_slideshow');
    if (thumb.alt.indexOf('image')<1){ ctstatus.innerHTML="Not an image" ;return}
    if (firstload) {zdiv.style.visibility = 'hidden';}
    var thissrc=thumb.name;
    if (cms_gallery_israndom) {
      document.cms_gallery_controls.random.src=cms_gallery_plugindir + 'buttons/random-on.png';
    }else{
      document.cms_gallery_controls.random.src=cms_gallery_plugindir + 'buttons/random.png';
    }
    var pimgtag='<img src="'+thissrc+'" name="'+ordinal+'" alt="'+ordinal+'" id="cms_gallery_zpreload" onLoad="cms_gallery_showzoom('+ordinal+')">';
    zpdiv.innerHTML=pimgtag;
 return false; 
 }

function cms_gallery_spinner(action) {
  clearTimeout(cms_gallery_spinner_event);
  cms_gallery_spinner_event=0;
  if (action > 0) {
    cms_gallery_spinner_event = setTimeout("cms_gallery_spinner_timeout()",action);
  }else{
    document.getElementById('cms_gallery_spinner').style.visibility=('hidden');
    clearTimeout(cms_gallery_spinner_event);
    cms_gallery_spinner_event=0;
  }
}

function cms_gallery_spinner_timeout() {
    // Prevents spinner remaining onscreen indefinitely if there are download probs..
    document.getElementById('cms_gallery_spinner').style.visibility=('visible');
    cms_gallery_spinner_event = setTimeout("cms_gallery_spinner(0)",15000);
}

function cms_gallery_showControls(action) {
    var zdv = document.getElementById("cms_gallery_zdiv"); 
    var cti = document.getElementById("cms_gallery_controls"); 
    var thisvis=cti.style.opacity;

    if (thisvis != 1) {
      thisvis=1;
    }  else {
      thisvis=0;
    }
    if (action=='on') {
        thisvis=1 ;
      } 
    if (action=='off') {
        thisvis=0 ;
    }       
    cti.style.opacity=thisvis;    

   $abuttons=document.getElementsByClassName('cms_gallery_overlaybutton');  
   for ($ct=0; $ct<$abuttons.length; $ct++){
    $abuttons[$ct].style.opacity=thisvis;
   }
}


function cms_gallery_showzoom(ordinal){
 /* Transfers preloaded image to viewport. Triggered by preload completion.*/
    var zpreload = document.getElementById('cms_gallery_zpreload'); 
    var zdiv = document.getElementById('cms_gallery_zdiv'); 
    var zimage= document.getElementById('cms_gallery_zimage'); 
    var ctspan=document.getElementById('cms_gallery_ordinal');

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

  var nsMargin=10; var zoMargin=40; var minScaling=0.5; var minFill=1; min2Fill=.6;
  // We do this at preload stage to prevent 'slow wipe' of image onto page. 
  var rawwidth=zpreload.width; 
  var rawheight=zpreload.height;
  if( typeof(zpreload.naturalWidth ) == 'number' ) { rawwidth=zpreload.naturalWidth}
  if( typeof(zpreload.naturalHeight ) == 'number' ) { rawheight=zpreload.naturalHeight}
    var zwratio=rawwidth/(vpWidth-nsMargin);
    var zhratio=rawheight/(vpHeight-nsMargin);
    var scaledwidth=rawwidth;
    var scaledheight=rawheight;
    // if image is larger than viewport OR we have set zoom on... 
    if (zwratio>1 || zhratio>1 || cms_gallery_zoomout>0){
      // needs zoom values 1-3 adding if selectable zoom type allowed..
      var scaling=zwratio;
      if (zhratio>scaling) {scaling=zhratio}
      scaledwidth=(rawwidth/scaling).toFixed(0);
      scaledheight=(rawheight/scaling).toFixed(0);
      // Increase scaling if a literal viewport-fit would result in a narrow image..
      var wFill = scaledwidth/(vpWidth-nsMargin);      
      var hFill = scaledheight/(vpHeight-nsMargin);      
      if(cms_gallery_zoomout==1){
        // Touch inner dimension
        if ( hFill > wFill ) {
          if ( hFill < minFill ) {scaling=rawheight/(vpHeight-zoMargin)};
        } else {
          if ( wFill < minFill ) {scaling=rawwidth/(vpWidth-zoMargin)};
        }
      }
      if(cms_gallery_zoomout>1){
        // Touch outer dimension
        if ( hFill < wFill ) {
          if ( hFill < minFill ) {scaling=rawheight/(vpHeight-zoMargin)};
        } else {
          if ( wFill < minFill ) {scaling=rawwidth/(vpWidth-zoMargin)};
        }
      }
      if(cms_gallery_zoomout==3){
        var wFill = rawwidth/(vpWidth-nsMargin);      
        var hFill = rawheight/(vpHeight-nsMargin);      
        // Allow huge images actual size
        if ( hFill > 1 && wFill > 1 ) {
          scaling=1;
        }
      }

      // Limit scaling to prevent excessive pixellation on lo-res images.. 
      if (scaling < minScaling) {scaling=minScaling}
      scaledwidth=(rawwidth/scaling).toFixed(0);
      scaledheight=(rawheight/scaling).toFixed(0);
      zpreload.style.width=(scaledwidth)+'px';
      zpreload.style.height=(scaledheight)+'px';
    }
    if (scaledwidth>vpWidth+10){ 
      // Translate scroll wheel action on wide image..
      ActivateScroll(true);
    } else {
      ActivateScroll(false);
    }
    zdiv.style.height=(vpHeight)+'px'; 
    zdiv.style.width=(vpWidth)+'px';
    zimage.style.maxWidth='none';
    zimage.style.maxHeight='none';
    zimage.style.width=(scaledwidth)+'px';
    zimage.style.height=(scaledheight)+'px';
//  cms_gallery_fade('out'); // decided better without. 
    zimage.src=zpreload.src; 
    zimage.name=ordinal;
    var ztop=(vpHeight-scaledheight)/2;
    //fix for downspaced zoomed images.. 
    if (vpHeight-ztop < scaledheight) ztop=0;
    zimage.style.marginTop=ztop +'px';
    ctspan.innerHTML=ordinal + "/" + cms_gallery_itemcount;
    document.getElementById('cms_gallery_underlay').style.display=('block');
    cms_gallery_spinner(0);
    // Remove scrollbars from underlying page.. 
    document.body.style.overflow='hidden';
    zdiv.style.visibility = 'visible';
    zdiv.style.overflow='auto';
    zimage.focus();
    cms_gallery_slide_reinit();
    return false;
}

 function cms_gallery_unzoom(){
      cms_gallery_spinner(0);
      var zdiv = document.getElementById('cms_gallery_zdiv'); 
      ActivateScroll(false);
      cms_gallery_slide_event=0;
      zdiv.style.visibility = 'hidden';
      document.cms_gallery_controls.slideshow.src=cms_gallery_plugindir + 'buttons/slideshow.png';
      document.getElementById('cms_gallery_underlay').style.display='none';
      document.body.style.overflow='auto';
 }

function cms_gallery_fade(action){
 // Not currently used, large images load better directly.  
  if (typeof cms_gallery_opacity=='undefined'){cms_gallery_opacity=1;}
  if (typeof cms_gallery_fade_event=='undefined'){cms_gallery_fade_event=0;}
  if (typeof cms_gallery_fade_element=='undefined'){cms_gallery_fade_element=document.getElementById('cms_gallery_zdiv');}
  if (action=='out') {
    cms_gallery_opacity=.3;
    cms_gallery_fade_element.style.opacity=cms_gallery_opacity ; 
    cms_gallery_fade_event=setTimeout("cms_gallery_fade('in')",15);
  } else {
    if (cms_gallery_opacity < 1){
      cms_gallery_opacity+=.4;
      cms_gallery_fade_event=setTimeout("cms_gallery_fade('in')",1);
      cms_gallery_fade_element.style.opacity=cms_gallery_opacity ;
   } else {
      cms_gallery_fade_element.style.opacity=1;
      clearTimeout(cms_gallery_fade_event);
   }
  }
}

function cms_gallery_goto(thisaction){
 var zimg=document.getElementById('cms_gallery_zimage');
 var thisordinal=parseInt(zimg.name,10);
 var increment=0;
 var newordinal=thisordinal;
 var timerstatus=document.getElementById('cms_gallery_ordinal');
 if (typeof cms_gallery_slide_event=='undefined'){cms_gallery_slide_event=0}

 if (thisaction=='first'){newordinal=1;cms_gallery_slide_reinit();}
 if (thisaction=='last'){newordinal=cms_gallery_itemcount;cms_gallery_slide_reinit();}
 if (thisaction=='+1'){increment=1;cms_gallery_slide_reinit();}
 if (thisaction=='-1'){increment=-1;cms_gallery_slide_reinit();}
 if (thisaction=='random'){

    if (cms_gallery_slide_event!=0) { 
      if (cms_gallery_israndom) {
        cms_gallery_israndom=false;
      } else {
        cms_gallery_israndom=true;
      } 
    } else {
      newordinal=Math.floor((Math.random()*cms_gallery_itemcount))+1 ;
    }
    if (cms_gallery_israndom) {
      document.cms_gallery_controls.random.src=cms_gallery_plugindir + 'buttons/random-on.png';
    }else{
      document.cms_gallery_controls.random.src=cms_gallery_plugindir + 'buttons/random.png';
    }
}

if (thisaction=='exit'){cms_gallery_unzoom()}

if (thisaction=='runslideshow'){
    if (cms_gallery_slide_event>0){
        if (cms_gallery_israndom) {
          newordinal=Math.floor((Math.random()*cms_gallery_itemcount))+1 ;
        } else {
          increment=1;
        }
        cms_gallery_slide_event=setTimeout("cms_gallery_goto('runslideshow')",cms_gallery_rate*1000);
    }else{
        return false;
    }
}

if (thisaction=='slideshow'){
    if (cms_gallery_slide_event<1){
        cms_gallery_slide_event=setTimeout("cms_gallery_goto('runslideshow')",cms_gallery_rate*1000);
        <?php thisfsdir(__FILE__,2)?> ;
        timerstatus.innerHTML=cms_gallery_rate +"s";
        document.cms_gallery_controls.slideshow.src=cms_gallery_plugindir + 'buttons/slideshow-on.png';
        if (cms_gallery_israndom) {
           document.cms_gallery_controls.random.src=cms_gallery_plugindir + 'buttons/random-on.png';
        }else{
           document.cms_gallery_controls.random.src=cms_gallery_plugindir + 'buttons/random.png';
        }
    }else{   
        document.cms_gallery_controls.slideshow.src=cms_gallery_plugindir + 'buttons/slideshow.png';
        document.cms_gallery_controls.random.src=cms_gallery_plugindir + 'buttons/random.png';
        clearTimeout(cms_gallery_slide_event);
        cms_gallery_slide_event=0;
    }
    return true;
}

if (thisaction=='manual'){
    if (cms_gallery_slide_event>0){
        document.cms_gallery_controls.slideshow.src=cms_gallery_plugindir + 'buttons/slideshow.png';
        document.cms_gallery_controls.random.src=cms_gallery_plugindir + 'buttons/random.png';
        clearTimeout(cms_gallery_slide_event);
        cms_gallery_slide_event=0;
    }
    return true;
}

if (increment!=0) newordinal=Number(thisordinal)+increment;
var direction=increment; if (direction==0){direction=1};
    // Skip non-image files..  
for (ct=1;ct<100;ct++){
    if (newordinal>cms_gallery_itemcount){newordinal=1}
    if (newordinal<1){newordinal=cms_gallery_itemcount}
    var thumb=document.getElementById(newordinal); 
    if (thumb.alt.indexOf('image')>=0) {break}
    newordinal = Number(newordinal) +direction;
    // zstatus.innerHTML=newordinal;   
}
if (newordinal!=thisordinal){cms_gallery_zoom(newordinal,false)}
if (newordinal>cms_gallery_itemcount){newordinal=1}
if (newordinal<1){newordinal=cms_gallery_itemcount}
return true;
}    

function cms_gallery_togglezoom(action){
  var maxZoomMode=3;
  var lastaction=cms_gallery_zoomout;
  if (typeof action=='undefined') {
     cms_gallery_zoomout+=1;
  } else {
     cms_gallery_zoomout=action;
  }
  if ( cms_gallery_zoomout > maxZoomMode ) cms_gallery_zoomout=0;
  
  // Rotate zoom spinner ..
  document.cms_gallery_controls.zoom.src=cms_gallery_plugindir + 'buttons/zoom' + cms_gallery_zoomout.toString() + '.png';
  var bgloc='url(' + cms_gallery_plugindir + 'buttons/zoom'+ cms_gallery_zoomout.toString() + '.png)';
  document.cms_gallery_buttons.topzoom.style.backgroundImage=bgloc;

  if ( cms_gallery_zoomout ==0 ) {
     document.cms_gallery_controls.zoom.title="Select Zoom Mode";
  }
  if ( cms_gallery_zoomout ==1 ) {
     document.cms_gallery_controls.zoom.title="Max size within screen boundaries";
  }
  if ( cms_gallery_zoomout ==2 ) {
     document.cms_gallery_controls.zoom.title="Fill screen where possible";
  }
  if ( cms_gallery_zoomout ==3 ) {
     document.cms_gallery_controls.zoom.title="Actual size where larger than screen";
  }
  cms_gallery_showzoom(document.getElementById('cms_gallery_zimage').name);
}

function cms_gallery_slide_reinit(){ 
    // alert('reinit');
    if (cms_gallery_slide_event>0){
        clearTimeout(cms_gallery_slide_event);
        cms_gallery_slide_event=setTimeout("cms_gallery_goto('runslideshow')",cms_gallery_rate*1000);
    }
}

function cms_gallery_setrate(changerate){
    if (changerate==1){cms_gallery_rate=cms_gallery_rate*2}
    if (changerate==-1){cms_gallery_rate=Math.round(cms_gallery_rate/2)};
    if (cms_gallery_rate<5) {cms_gallery_rate=5}
    if (cms_gallery_rate>3600) {cms_gallery_rate=3600}
    document.getElementById('cms_gallery_ordinal').innerHTML= cms_gallery_rate+"s";
    cms_gallery_slide_reinit();
}

</script>
<div id='cms_gallery_underlay' onClick="cms_gallery_unzoom()" ></div>
<div id="cms_gallery_spinner"><img alt="loading.." src="<?php echo thisfsdir(__FILE__);?>/buttons/spinner.gif" ></div>
<?php
}
?>

