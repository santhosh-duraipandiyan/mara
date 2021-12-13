<?php

// include_once fsroot. 'codebase/core.php';
include_once fsroot . 'codebase/resizer.php';

/*
Gallery script, copyright IWR Consultancy.
Subject to Mara cms licence terms. 
*/

echo <<< EOSS
<style>
/* Mandatory styles which if removed, may cause trouble */
div#gallery_overlay
{
    opacity:0.5;
    position:fixed; 
    top:0;
    left:0;
    width:100%;
    height:100%;
    display:none;
    z-index:9900; 
}    

#cms_gallery_zdiv {
    text-align:center;
    vertical-align:middle;
    position: fixed;
    visibility: hidden;
    margin:auto;
    top:-10px;
    left:-10px;
    z-index:9999; 
}

#cms_gallery_zimage{
    z-index:9999; 
}
</style>
EOSS;

echo '<link rel=stylesheet href="'.thisfsdir(__FILE__).'gallery.css" type="text/css">';
setg('cms_gallery_itemcount',0);
gallery_imgzoom();

return; 

function gallery_browser($gallery='img/gallery',$sliderate=5,$pre_height="100px",$pre_width="250px"){

$fullrefresh=false;
if(isset($_GET['refresh'])):
    $fullrefresh=true;
endif;
$plugindir=thisfsdir(__FILE__);
// echo $plugindir;
// return;
$simulation=0;
$recursive=true;
$itemcount=0;
if ($gallery=='') $gallery=pathname(pagefile);
$gallery=path($gallery);
$thumbdir=".thumb/";
if (strlen($thumbdir)<1 || strpos($thumbdir,"..")!==false || strpos($thumbdir,"/")<2) $thumbdir=".thumb/";

$dcontents=scantree($gallery,0,$recursive,'gif,jpg,jpeg,png,avi,mkv,mov,flv,swf');
$thumb_batch=50;
$imglist='gif,jpg,jpeg,png';
$imgtypes=explode(',',$imglist);
$vidtypes=explode(',','avi,mkv,mov');
$flvtypes=explode(',','flv,swf');

echo '<center>';

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
   $thisitem['description']="File: ". $thisitem['name']. "\nLocation: " .$thisitem['relpath']."\nUploaded: ".@date('Y/m/d', $thisitem['time'])."\nSize: ".formatBytes($thisitem['size']);
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

function scantree($startdir, $include_hidden=false, $recursive=true, $extensions="",$exclusions="",$dirlimit=1000, $thumbdir='.thumb'){
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
if (!is_array($files)) $files[]=array(); // php7 ;
return $files;
}

function formatBytes($bytes, $precision = 2) { 
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
<div id="cms_gallery_zdiv" > 
<div id="cms_gallery_zzdiv" > 
<img  src="" id="cms_gallery_zimage" alt="" onClick="cms_gallery_unzoom()" title="" border=0>
</div>
<form name="cms_gallery_viewer">
<span id="cms_gallery_count"></span>
<input type="button" class="cms_gallery_button" name="first" title="Go to first image in set" onClick="cms_gallery_goto('first');" value="&lt;&lt;" />
<input type="button" class="cms_gallery_button" name="prev" title="Go to previous image in set " onClick="cms_gallery_goto('-1');" value="&lt;" />
<input type="button" class="cms_gallery_button" name="count" id="cms_ordinal" title="Image number in series" disabled value="-" />
<input type="button" class="cms_gallery_button" name="next" title="Go to next image in set" onClick="cms_gallery_goto('+1');" value="&gt;" />
<input type="button" class="cms_gallery_button" name="last" title="Go to last image in set" onClick="cms_gallery_goto('last');" value="&gt;&gt;" />
<input type="button" class="cms_gallery_button" name="random" title="Random image from set" onClick="cms_gallery_goto('random');" value="?" />
<input type="button" class="cms_gallery_button" name="slideshow" title="Automatic timed change" onClick="cms_gallery_goto('slideshow');" value="Slideshow" />
<input type="button" class="cms_gallery_button" name="slower" title="Reduce slideshow speed" onClick="cms_gallery_setrate(1)" value="S" />
<input type="button" class="cms_gallery_button" name="faster" title="Increase slideshow speed" onClick="cms_gallery_setrate(-1)" value="F" />
<input type="button" class="cms_gallery_button" name="zoom" title="Zoom out small images to fill screen" onClick="cms_gallery_togglezoom();" value="<->" />
<input type="button" class="cms_gallery_button" name="close" title="Close this viewer and return to gallery" onClick="cms_gallery_goto('exit');" value="Exit" />
<span id="cms_gallery_status"></span></form>
</div>
<div id="cms_gallery_zpdiv" style="position:fixed;top:-4000px;left:-4000px;border:none;z-index:1;" >&nbsp;</div>
<script type="text/javascript">
cms_gallery_zoomout=0;
// cms_gallery_rate=5;
 function cms_gallery_zoom(ordinal,firstload){
    var zzdiv = document.getElementById('cms_gallery_zzdiv'); 
    var zdiv = document.getElementById('cms_gallery_zdiv'); 
    var zpreload = document.getElementById('cms_gallery_zpreload'); 
    var zpdiv = document.getElementById('cms_gallery_zpdiv'); 
    var thumb=document.getElementById(ordinal); 
    var zstatus=document.getElementById('cms_gallery_status');
    if (thumb.alt.indexOf('image')<1){ zstatus.innerHTML="Not an image" ;return}
    if (firstload) {zdiv.style.visibility = 'hidden';}
    var thissrc=thumb.name;
    var pimgtag='<img src="'+thissrc+'" name="'+ordinal+'" alt="'+ordinal+'" id="cms_gallery_zpreload" onLoad="cms_gallery_showzoom('+ordinal+')">';
    zpdiv.innerHTML=pimgtag;
return false; 
 }
 
function cms_gallery_showzoom(ordinal){
    var zpreload = document.getElementById('cms_gallery_zpreload'); 
    var zdiv = document.getElementById('cms_gallery_zdiv'); 
    var zzdiv = document.getElementById('cms_gallery_zzdiv'); 
    var zimage= document.getElementById('cms_gallery_zimage'); 
    var ctspan=document.getElementById('cms_ordinal');
    var scount=document.getElementById('cms_gallery_count');

  // Get viewport width/height in various browsers...
  var vpWidth = 0;
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

  // Detect oversize image and scale down to fit screen
  // We do this at preview stage to prevent 'slow wipe' of image onto page. 
  var rawwidth=zpreload.width; 
  var rawheight=zpreload.height;
  if( typeof(zpreload.naturalWidth ) == 'number' ) { rawwidth=zpreload.naturalWidth}
  if( typeof(zpreload.naturalHeight ) == 'number' ) { rawheight=zpreload.naturalHeight}
    var zwratio=rawwidth/(vpWidth-60);
    var zhratio=rawheight/(vpHeight-60);
    var scaledwidth=rawwidth;
    var scaledheight=rawheight;
    if (zwratio>1 || zhratio>1 || cms_gallery_zoomout>0){
      var scaling=zwratio;
      if (zhratio>scaling) {scaling=zhratio}
       scaledwidth=(rawwidth/scaling).toFixed(0);
       scaledheight=(rawheight/scaling).toFixed(0);

       zpreload.width=scaledwidth;
       zpreload.height=scaledheight;
    }
    var ztop=((vpHeight-scaledheight)/2).toFixed(0)-25;
    var zleft=((vpWidth-scaledwidth)/2).toFixed(0)-25;
   
    zdiv.style.top=ztop + "px" ; 
    zdiv.style.left=zleft + "px";
    zimage.width=scaledwidth;
    zimage.height=scaledheight;
    zimage.src=zpreload.src; 
    zimage.name=ordinal;
    ctspan.value=ordinal + " of " + cms_gallery_itemcount;
    scount.innerHTML="";

    document.getElementById('gallery_overlay').style.display=('block');
    zdiv.style.visibility = 'visible';
    return false;
}

 function cms_gallery_unzoom(){
      var zdiv = document.getElementById('cms_gallery_zdiv'); 
      var zzdiv = document.getElementById('cms_gallery_zzdiv'); 
      cms_gallery_slide_event=0;
      zdiv.style.visibility = 'hidden';
      document.getElementById('gallery_overlay').style.display=('none');
      document.getElementById('cms_gallery_status').innerHTML='';
      document.cms_gallery_viewer.slideshow.value='Slideshow';
 }

function cms_gallery_goto(thisaction){
 var zimg=document.getElementById('cms_gallery_zimage');
 var thisordinal=parseInt(zimg.name,10);
 var increment=0;
 var newordinal=thisordinal;
 var zstatus=document.getElementById('cms_gallery_status');
 if (typeof cms_gallery_slide_event=='undefined'){cms_gallery_slide_event=0}

if (thisaction=='first'){newordinal=1}
if (thisaction=='last'){newordinal=cms_gallery_itemcount}
if (thisaction=='+1'){increment=1}
if (thisaction=='-1'){increment=-1}
if (thisaction=='random'){newordinal=Math.floor((Math.random()*cms_gallery_itemcount))+1 }
if (thisaction=='exit'){cms_gallery_unzoom()}
if (thisaction=='runslideshow'){
    if (cms_gallery_slide_event>0){
        increment=1;
        setTimeout("cms_gallery_goto('runslideshow')",cms_gallery_rate*1000);
    }else{
        return false;
    }
}
if (thisaction=='slideshow'){
    if (cms_gallery_slide_event<1){
        cms_gallery_slide_event=setTimeout("cms_gallery_goto('runslideshow')",cms_gallery_rate*1000);
        cms_gallery_slide_event=1;
        <?php thisfsdir(__FILE__,2)?> ;
        var downloading='<img alt="" src="'+thispage+'ajax-loader.gif" title="Download in progress" style="vertical-align:middle;">';
        zstatus.innerHTML=downloading + " <small>" ;// + cms_gallery_rate +"s<small>" 
        document.cms_gallery_viewer.slideshow.value='Stop';
    }else{   
        document.cms_gallery_viewer.slideshow.value='Slideshow';
        zstatus.innerHTML="";   
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
//    zstatus.innerHTML=newordinal;   
}
if (newordinal!=thisordinal){cms_gallery_zoom(newordinal,false)}
if (newordinal>cms_gallery_itemcount){newordinal=1}
if (newordinal<1){newordinal=cms_gallery_itemcount}
return true;
}    

function cms_gallery_togglezoom(){
    if (cms_gallery_zoomout) {
        cms_gallery_zoomout=0;
        document.cms_gallery_viewer.zoom.value="<->";
        document.cms_gallery_viewer.zoom.title="Zoom out small images to fill screen";

    }else{    
        document.cms_gallery_viewer.zoom.value=">-<";
        document.cms_gallery_viewer.zoom.title="Show small images at actual size";
        cms_gallery_zoomout=1;
    }
    cms_gallery_showzoom(document.getElementById('cms_gallery_zimage').name);
}
function cms_gallery_setrate(changerate){
    if (changerate==1){cms_gallery_rate=cms_gallery_rate*2}
    if (changerate==-1){cms_gallery_rate=cms_gallery_rate/2};
    if (cms_gallery_rate<2) {cms_gallery_rate=1}
    document.getElementById('cms_gallery_count').innerHTML= cms_gallery_rate+"s";
}

</script>
<div id='gallery_overlay' onClick="cms_gallery_unzoom()"></div>
<?php
}
?>

