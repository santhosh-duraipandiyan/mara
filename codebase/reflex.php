<?php
// Prevent reflex loading of webpages in system dirs, or of incorrect fileypes.. 
 $cpagename=$_SERVER['SCRIPT_NAME'];
 $apermittedexts=array('htm','html','php','asp','jsp');
 $cpagepath = pathinfo(str_replace('\\','/',$cpagename));
 if ($cpagepath['filename']=='sitemap') return ;
 // $cpagedir=substr($cpagepath['dirname'],strrpos($cpagepath['dirname'],'/')+1);
 if (strpos($cpagepath['dirname'],'/codebase/')!==false) return;
 if (substr($cpagepath['dirname'],-8)=='codebase') return;
 if (in_array($cpagepath['extension'],$apermittedexts)!=true)  return ; 

require_once 'core.php';

// Main content loader. Reads page and theme files into buffers, processes content and outputs to browser. 

// Set the working environment parameters....

if(isset($_GET[editkey]) || gets('authenticated')=='1' ):
// set admin mode if '?login' is added to URL, or session indicates a logged-in admin.  
// Note that admin functions are loaded after page, so that all forms are in place. 
// Authenticated takes '1' or '0' instead of t/f for form compatibility. 
 ipcheck();
 setg('ok2admin',true);
//echo "ok2admin=true;\n";
else:
 setg('ok2admin',false);
// echo "ok2admin=false;\n";
endif;
setg('editing_disallowed',false);
if (isset($_GET['noadmin'])) setg('ok2admin',false) ;

// end of environment setup. 

// get content of page requested into a buffer: 
if (substr(pagefile,0,5)=='theme' && substr(pagefile,-9)=='theme.php') {
 // but not if the theme is being tested directly. 
 $callingpage = "<div style='margin:5px;width:100%;height:400px;border:1px solid black;background:teal;color:white;'><p style='text-align:center;margin-top:180px;'>Page content will appear here</p></div>"; 
} else {
 if (getg('ok2admin')){
  // without executing php.. 
  $fh = fopen($_SERVER['SCRIPT_FILENAME'], 'rb') or die("Error: can't open page file" );
  $callingpage=stream_get_contents($fh);
  fclose($fh);
 }else{
  // with execution of php..     
  ob_start();
  include($_SERVER['SCRIPT_FILENAME']);
  $callingpage = ob_get_contents();
  ob_end_clean();
  chdir(dirname($_SERVER['SCRIPT_FILENAME']));
  // If this is a temporary page, delete the file after reading its contents.. 
  if (substr($_SERVER['SCRIPT_FILENAME'],-12)=='~preview.php') unlink ($_SERVER['SCRIPT_FILENAME']);
 }
}

/*
Harvesting protection is now in page save routine, to reduce overhead.
$rxemail='([\w|\.|\-|\_]+?)@([\w|\.|\-\_]+?)';
if (harvesting_protection>0 && getg('ok2admin')==false) {
 $harvestct=0;
   $callingpage = preg_replace_callback("/$rxemail/siU",
   'munge_email', $callingpage,50,$harvestct);
 if (harvesting_protection>1){
   if ($harvestct>0) $callingpage="<h1 style='color:red'>Publication blocked.</h1><br><h3>This page contains email addresses which are vulnerable to robotic harvesting. </h3><br>Web publication has been inhibited to protect these addresses from being collected by junkmail operators. Please edit the page to remove or protect the vulnerable items." ;
   setg('editing_disallowed',true);
 }
}
*/
   
$ahead=tagdata($callingpage,'head', false);
$abody=tagdata($callingpage,'body', true);
$atitle=tagdata($ahead[2],'title', false);
$ametas=@getmetas($ahead[2]);

setg('cms_editlevel',site_editlevel);
setg('cms_editors',site_editors);

if (isset($ametas['cms_editlevel']['content'])) {
 setg('cms_editlevel', $ametas['cms_editlevel']['content']);
}
if (isset($ametas['cms_editors']['content'])) {
 setg('cms_editors', $ametas['cms_editors']['content']);
}
if (isset($ametas['cms_pagetype']['content'])) {
 setg('cms_pagetype', $ametas['cms_pagetype']['content']);
}

/* Determine menu file placement for this page, if meta tag present */
$sitemenu="";
if (isset($ametas['cms_menu']['content'])) $sitemenu=$ametas['cms_menu']['content'] ;
if (get('menu')) $sitemenu=get('menu');
setg('sitemenu',$sitemenu);

/* Establish theme file, if not default.. 
  themebase is path to themes rel to site webroot, with / 
  $themename is plain themedir name sent to parser
  -in increasing order of precedence: 
  default_theme is value from siteini.php
  $stheme is override from user session var
  $ptheme is override from URL string ?theme=

*/

$themename=default_theme;
$stheme=gets('user_theme');
// Trap bad chars in theme name (75) 
if ((strstr($stheme,'<')!==false) || (strstr($stheme,'..')!==false)) $stheme=''; 
$ptheme=@$ametas['cms_theme']['content'];
if (get('theme')) $ptheme=get('theme');
if (get('pagetheme')) $ptheme=get('pagetheme');
if ($ptheme=='default'){
  $stheme='';
  $ptheme='';
  if (get('theme')) sets('user_theme','');
  }
if ($stheme){
   // prevent directory traversals.. 
   if (substr($stheme,0,1)=='/' || substr($stheme,0,1)=='\\') $stheme=substr($stheme,1,255);
   if (strpos($stheme,'..')===false) {
        $themename=$stheme;   
   }
}
if ($ptheme){
   if (substr($ptheme,0,1)=='/' || substr($ptheme,0,1)=='\\') $ptheme=substr($ptheme,1,255);
   if (strpos($ptheme,'..')===false) {
        // if (strtolower(@$ametas['cms_pagetype']['content'])!='frontpage') $themename=$ptheme;   
        $themename=$ptheme;
        if (get('theme')) sets('user_theme',$ptheme);
   }
}
if ($themename=='theme'){
 $themename = default_theme ;
}
$themefile = themebase . $themename . '/theme.php'; 
if (!file_exists($themefile)) $themefile = fsroot . $themefile;

// Load hidden theme if visible one is not present...
if (!file_exists($themefile)){
  if (substr($themename,0,1)=='.'){
    $h_themename = substr($themename,1);
  } else {
    $h_themename = '.' . $themename;
  }
  $h_themefile = fsroot . themebase . $h_themename . '/theme.php'; 
  if (file_exists($h_themefile)) {
    $themename = $h_themename;
    $themefile = $h_themefile;
  }
}
// echo theme .">". $themename.">".$themefile;
define ('themedir', siteroot . themebase . $themename . '/',1);
define ('theme', $themename,1);

// Get content of theme page... 
  if (file_exists($themefile)) {
  ob_start();
  include($themefile);
  $thistheme = ob_get_contents();
  ob_end_clean();
  chdir(dirname($_SERVER['SCRIPT_FILENAME']));
  } else {
  $thistheme='';
  }

// Show top message for missing theme:  
if (strlen($thistheme)<2):
  $thistheme="<body><b style='background:black;color:yellow;display:block;text-align:center;padding:4px;'>Specified theme not found, or incomplete. Displaying unthemed content.</b><br><!--CONTENT--></body>";
  sets('user_theme',default_theme);
  $themename=default_theme;
endif;

// Separate out head and body sections of theme requested: 
$afhead=tagdata($thistheme,'head', false);
$afbody=tagdata($thistheme,'body', true);
$aftitle=tagdata($afhead[2],'title', false);
$afmetas=@getmetas($afhead[2]);

// Base returned headsection on theme head..
$thishead=$afhead[2];

/* Title and description handling: 
   If page header has no title, or title matches theme title, 
   then use first header as title, use first para as description, or next header if no paras. */

$page_id="undefined";

if (strlen($atitle[2])<2 || $atitle[2]==$aftitle[2] ) {
  $eols=array("\r","\n"); 
  // Title nonexistent or matching, generate title AND description. 
  $pfirstheading= tagdata($abody[2],'h[\d]', false);
  // Strip any tags inside heading... 
  $pfirstheading= strip_tags($pfirstheading[2]);
  $pfirstheading=str_replace($eols,'',$pfirstheading); 
  // unlikely to be encountered, but possibly strip symbols from heading if found..
  // $pfirstheading=preg_replace("/[^a-zA-Z0-9\s-_!.]/", "", $pfirstheading);
  $pdescription= tagdata($abody[2],'p', false);
  if ($pdescription[2]=='') {
    $pdescription= tagdata($abody[2],'div', false);
  }  
  if ($pdescription[2]=='') {
    $pdescription= tagdata($abody[2],'h[23456]', false);
  }  
    $pdescription[2]=strip_tags($pdescription[2]);
    $pdescription=str_replace($eols,'',$pdescription); 
    // $pdescription[2]=preg_replace("/[^a-zA-Z0-9\s-_!.]/", "", $pdescription[2]);
  $thisdesc=cms_truncate($pdescription[2],200);
  $thistitle= "<title>" . $aftitle[2] . " : " . $pfirstheading . "</title>";
  $short_title=$pfirstheading;
} else {  
  // Title and description present, use them. 
  $thistitle= "<title>" . $aftitle[2] . " :: " . $atitle[2] . "</title>";
  $thisdesc=@$ametas['description']['content'];
  $short_title=$atitle[2];
}

$fdesctag=@$afmetas['description']['content'];
$fkeywordtag=@$afmetas['keywords']['content'];
$thiskeywordtag=@$ametas['keywords']['content'];

// Set document type. Note that the doctype constant also changes the behaviour of the editor. 
// Doctype, language and IE intranet behaviour are determined in siteini.php [site] section from v7.2 on
// A theme or page can still change the doctype, but this capability is deprecated and may be removed. 
// (The intention is to remove headsection clutter for simpler themes) 

$thisdoctype=site_doctype;
$pdoctype=@$ametas['cms_doctype']['content'];
if (@$afmetas['cms_doctype']['content']!='') {
   $thisdoctype=@$afmetas['cms_doctype']['content'];
}
if (stripos($pdoctype,"html")!==false){
   $thisdoctype=$pdoctype;
}
$thisdoctype=strtoupper($thisdoctype);
setg("doctype",$thisdoctype);

/* Determine sections to conceal in special pages */

$pinvisible=@$ametas['cms_hide']['content'];
$finvisible=@$afmetas['cms_hide']['content'];
if ($pinvisible && strncasecmp($finvisible,"none",4)!=0){
   $finvisible=$pinvisible;
}
if (get('hide')) $finvisible=get('hide');
if ($finvisible=='all') $finvisible="banner,top,left,right,bottom";
$ainvisible=explode(",",$finvisible,10);
$invis_css="";
foreach ($ainvisible as $invisible_element){
  $invisible_element=trim($invisible_element);
  if ($invisible_element !=""){
     if (substr($invisible_element,0,3)!="cms_") $invisible_element= "cms_" . $invisible_element;
     $invis_css .= "#$invisible_element {display:none;}";
  }
}

/*  Transfer informational metas from page headsection */
$htrans=array();
$themetitle=$aftitle[0];
$htrans[$themetitle]=$thistitle;
$htrans[$fdesctag]=$thisdesc;
$htrans[$fkeywordtag]=$thiskeywordtag;
// $htrans[$jsvars]=js_syspaths; // is this necessary? Nope.  
$thishead=areplace($thishead,$htrans);

cms_history_add($short_title,siteroot.pagefile);

/*   Begin HTML send to browser..  */
echo setdoctype($thisdoctype) . "\n";
$lang_string="";
// Introduced to meet FF requirement of these being in first 1k of page..  
if (strlen(site_language)>0) $lang_string=' lang="'.site_language.'"';
echo "<html".$lang_string."><head>\n";
echo "<meta http-equiv=\"x-ua-compatible\" content=\"IE=".site_msie."\">\n";
echo "<meta charset=\"" . site_charset . "\">\n";
// Prevent some searchengines from following print button links.. 
if (stripos(get('style'),"print")!==false){
 echo '<META NAME="ROBOTS" CONTENT="NOINDEX, NOFOLLOW">'."\n";
}

/* Insert standard js and css links (v4 change, keeps theme tidier) */

echo  '<link rel=stylesheet href="' .codedir. 'system.css' .jsindex. '" type="text/css">' . "\n";
echo  '<script type="text/javascript" src="' .codedir. 'system.js' .jsindex. '" ></script>' . "\n";
echo js_syspaths;
/* Set up js/css environment for browser  */
echo "\n<script>";
echo "var doctype='" . $thisdoctype . "';";
echo "var authenticated='".gets('authenticated')."';";
echo "var themefile='$themefile';";
echo "</script>\n";

/* Supply rest of head from theme.. */
echo $thishead ;
echo  '<link rel=stylesheet href="' .themedir. 'theme.css' .jsindex.  '" type="text/css">' . "\n";
echo  '<script type="text/javascript" src="' .themedir. 'theme.js' .jsindex. '" ></script>' . "\n";

if (strlen($invis_css)>0) { 
  echo "<style>$invis_css</style>" ;
}
if (site_phjscss > 0){
  $ahscripts="<!-- Scripts/css from page head section: -->\n"
  . taglist($ahead[2],'script','')
  . taglist($ahead[2],'link','stylesheet')
  . taglist($ahead[2],'style','');
  echo $ahscripts;
}
echo "</head> \n";

// echo '<textarea rows=10 cols=120>'; var_dump($sitemenu); echo '</textarea>';


if (gets('ok2admin')<1) {
  // Caption images if NOT in edit mode.. 
//  $abody[2]=cms_captioner($abody[2]); // javascript now in use. 
}

// Remove 'shebang' if present as it does not need to be sent to browser.. 
   $abody[2]=remove_shebang($abody[2]);

if (gets('ok2admin')) {
  // If we are in admin mode, load the editing interface... 

  // Convert script tags to placeholders..
  $phptags=array('<?php','?>','<script','</script');
  $edittags=array('<textarea class="cms_script" title="Inline php script. Double-click to edit" readonly="readonly"><?php','?></textarea>','<textarea class="cms_script" title="Inline js script. Double-click to edit." readonly="readonly"><script','</script></textarea');
  $abody[2]=str_ireplace($phptags,$edittags,$abody[2],$script_count); 
  // Decide if user has rights to edit page.. 
  if (gets('authenticated')){
  // cms_h1_msg content is used as a flag to javascript to control editing options. 
    $specificeditor=false;
    if (strlen(getg('cms_editors'))>0){
      $acms_editors=explode(',',getg('cms_editors'));
      foreach ($acms_editors as $thisguy) {
        if (gets('usr')==trim($thisguy)){
          $specificeditor=true;
        }
      }
      // If editor ID not found and NO edit level set, bar all except admins.     
      if (getg('cms_editlevel')<1) setg('cms_editlevel',5);
      unset($acms_editors);
    }  
    if (!$specificeditor){
     if (gets('user_privelege')< getg('cms_editlevel') ){
       setg('editing_disallowed',true);
       $abody[2]="<h1 id='cms_h1_msg_edit_lock'>-:Editing Disallowed:-</h1><b>You do not have sufficient priveleges to edit this page.</b>.<br><br>Privelege level " . getg('cms_editlevel') . " or page ownership is required.<br><br>You can view the rendered page <a href='?noadmin'> here</a>.";
     }
    }
  }
  if ($script_count>0){
   if (gets('user_privelege')< inline_scripts){
     if (gets('authenticated')){
       if (!$specificeditor){
         setg('editing_disallowed',true);
         $abody[2]="<h1 id='cms_h1_msg_edit_lock'>-:Editing Disallowed:-</h1><b>You do not have sufficient priveleges to edit pages containing scripts</b>.<br><br>You can view the rendered page <a href='?noadmin'> here</a>.";
       }
     }else{
       setg('editing_disallowed',true);
       $abody[2]="<h1 id='cms_h1_msg_edit_lock'>-:Login Required:-</h1>You need to authenticate yourself in order to see the source code of pages containing scripts. ";
     }
   }
  }

  //convert block tags if in edit mode:
  // No longer used since imports with br br pairs are less likely.
  // if (gets('ok2admin')) $abody[2]=brbr2p($abody[2]);
   // Build inline editor setup section: 
   $epreamble = '<script type="text/javascript" src="' . codedir . 'ck/ckeditor.js' . jsindex . '"></script>' . "\n";
   $epreamble .= '<div id="editable" contenteditable="false">';
   $epostfix = '</div closetag="editable">' . "\n";
  // Add to main page:
   //if (!editing_disallowed){
     // Not essential, but stops editor being loaded if  insufficient rights.. 
     $abody[2]= $epreamble . $abody[2] . $epostfix;
   //}
} 

$pagebody="<body>";
if (strlen($abody[1])>0) {$pagebody=$abody[1];}

// Send only page body if no theme or admin controls required, e.g on sitemap: 
if (stripos($finvisible,"theme")!==false){
   echo $pagebody . $abody[2] . "</body></html>"; 
   exit; //return true;
}
if (stripos(get('style'),"print")!==false){
   echo $pagebody . $abody[2] . "<script>window.print();window.close();</script></body></html>"; 
   exit; // return true;
}

// include adminbar if in admin mode: 
if (gets('ok2admin')) {
 $pagebody .=  '<div id="adminbar"><span id="hypermsg"></span></div>';
}

// Insert main page into theme body, and send to browser: 
echo $pagebody . str_replace("<!--CONTENT-->", $abody[2], $afbody[2]);

// Include admin functions after main page has been sent:
 if (gets('ok2admin')) {
   include('ajax.php');
   include('admin.php');
 } else {
   echo "<script>cms_deferredjs()</script>\n";
 }

echo '</body></html>' ;

exit;
//return true;
//}


function setdoctype($thisdoctype){
  switch ($thisdoctype) {
    case "HTML5":
        return '<!DOCTYPE html>';
        break;
    case "HTML4":
        return '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">';
        break;
    case "HTML4T":
        return '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">'; 
        break;
    case "HTML4F":
        return '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Frameset//EN" "http://www.w3.org/TR/html4/frameset.dtd">';
        break;
    case "XHTML":
        return '<!DOCTYPE html><!-- Warning: XHTML is not supported -->';
        break;
    case "XHTMLT":
        return '<!DOCTYPE html><!-- Warning: XHTML is not supported -->';
        break;
    case "XHTMLF":
        return '<!DOCTYPE html><!-- Warning: XHTML is not supported -->';
        break;
    default:
        return '<!DOCTYPE html>';
  }
}  

function xxxplugin($name){
  // no longer used
  $pluginphp = siteroot . path(pluginbase) . $name . '/' . 'plugin.php' ;
  $fspluginphp = fsroot . path(pluginbase) . $name . '/' . 'plugin.php' ;
  $fspluginjs = fsroot . path(pluginbase) . $name . '/' . 'plugin.js' ;
  $pluginjs = siteroot . path(pluginbase) . $name . '/' . 'plugin.js' ;
  $pfound=false;
  if (file_exists($fspluginjs)){
    $pfound=true;
    echo "<script type='text/javascript' src='$pluginjs'></script>";
  }
  if (file_exists($fspluginphp)){
    $pfound=true;
    return $fspluginphp;     
  }
  if ($pfound==false){
    if (debug||get('debug')) echo "<p style='color:red;'>Warning: Plugin <i>$name</i> failed to load.<p>";
  }
  return "" ;
}

function getUrlContents($url, $maximumRedirections = null, $currentRedirection = 0) {
    $result = false;
    $contents = @file_get_contents($url);
    // Check if we need to go somewhere else
    if (isset($contents) && is_string($contents))
    {
        preg_match_all('/<[\s]*meta[\s]*http-equiv="?REFRESH"?' . '[\s]*content="?[0-9]*;[\s]*URL[\s]*=[\s]*([^>"]*)"?' . '[\s]*[\/]?[\s]*>/si', $contents, $match);
        if (isset($match) && is_array($match) && count($match) == 2 && count($match[1]) == 1)
        {
            if (!isset($maximumRedirections) || $currentRedirection < $maximumRedirections)
            {
                return getUrlContents($match[1][0], $maximumRedirections, ++$currentRedirection);
            }
            $result = false;
        }
        else
        {
            $result = $contents;
        }
    }
    return $contents;
}

function areplace ($text, $replace) {
    $keys = array_keys($replace);
    $length = array_combine($keys, array_map('strlen', $keys));
    arsort($length);
   
    $array[] = $text;
    $count = 1;
    reset($length);
    while ($key = key($length)) {
        if (strpos($text, $key) !== false) {
            for ($i = 0; $i < $count; $i += 2) {
                if (($pos = strpos($array[$i], $key)) === false) continue;
                array_splice($array, $i, 1, array(substr($array[$i], 0, $pos), $replace[$key], substr($array[$i], $pos + strlen($key))));
                $count += 2;
            }
        }
        next($length);
    }
    return implode($array);
}

function cms_captioner($data="", $action='unspecified'){
    $data = preg_replace_callback('~(<img.*src\s*=.*>)~siU',
    'cb_img_caption', $data, 1024);
    return $data;
}
function cb_img_caption($matches){
    $data=$matches[0];
    $src=cms_img_attr($data,'src');
    // If bad image or no alt/title text, no need to process caption..    
    if ($src=='') { return $data;}
    $alt=cms_img_attr($data,'title');
    if ($alt=='') $alt=cms_img_attr($data,'alt');
    if ($alt=='') { return $data;}
    $title=cms_img_attr($data,'title');
    $style=cms_img_attr($data,'style');
    $class=cms_img_attr($data,'class');
    $imgwidth=cms_css_attr($style,'width');
    $containerstyle='';
    // echo $style . " width:".$imgwidth.'<br>';
    if (substr($imgwidth,-1)=='%') {
    // Apply percentage width to container, and change img width to 100% ..
    // 95% is dreadful hack to overcome css quirk ;)
      $data=str_replace('width:'.$imgwidth,'width:95%',$data);
      // echo htmlentities($data) ."|";
      // echo $style . " | width " . $imgwidth . "<br>";
      $containerstyle.=' style="width:'.$imgwidth.';" ';
    } 
    $rtn=$data;
    $sclass=' '.$class.' ';
    if (stristr($sclass," caption ")!==false){
    $class=str_ireplace("caption","",$class);
    $data=str_replace($alt, "",$data); // Do we want a caption AND an alt? Not sure. JAWS users might have advice.. 
    // Style added to outer span as fix for percentage image sizes in captioner mode. (5.3) Not a complete fix so postponed.  ***
    // Need to transfer percentage to outer span, and set image to 100%. Regex? 
    // $rtn='<span style="'.$style.'" class="'.$class.'" ><span style="display:table-row;"><span style="display:table-cell;">'.$data.'</span></span>';
    $rtn='<span class="'.$class.'"'.$containerstyle.' ><span style="display:table-row;"><span style="display:table-cell;">'.$data.'</span></span>';
    $rtn.='<span style="display:table-row;"><span class="caption-text">'.$alt.'</span></span></span>';
    }
    if (stristr($sclass," caption-top ")!==false){
    $class=str_ireplace("caption","",$class);
    $data=str_replace($alt, "",$data); 
    $rtn='<span class="'.$class.'" ><span style="display:table-row;"><span class="caption-text">'.$alt.'</span></span>';
    $rtn.='<span style="display:table-row;"><span style="display:table-cell;">'.$data.'</span></span></span>';
    }
    return $rtn;
}

function cms_img_attr($data,$attr='src'){
    // bugfix[4.1]: did not handle quotes correctly, now uses parameter for second quote so it must be same type as first
    //    $pmerr=preg_match('~'.$attr.'=\s*[\'"](.*)[\'"]~siU',$data,$pmout); 
    // 4.2- Modded to allow src without quotes -needs testing more thoroughly
    $pmerr=preg_match('~'.$attr.'=\s*(\'|")(.*)\1~siU',$data,$pmout); 
    // Deal with unquoted parameters.. (in this case, no internal spaces allowed)  
    if ($pmerr<1) $pmerr=preg_match('~'.$attr.'\s*(=)\s*(.*)[\s|>]~siU',$data,$pmout); 
    if ($pmerr<1) return "";
    $rtn=$pmout[2];
    return $rtn;
}

function cms_css_attr($data,$attr=''){
    $data= ';' . $data . ';' ;
    $pmerr=preg_match('~[;]'.$attr.':(.*)[;]~siU',$data,$pmout); 
    if ($pmerr<1) return "";
    $rtn=$pmout[1];
    return $rtn;
}

/*
Old version. Only for quoted strings. 
function cms_css_attr($data,$attr=''){
     $data="'".$data."'";
    $pmerr=preg_match('~[;\'"]'.$attr.':(.*)[;\'"]~siU',$data,$pmout); 
    if ($pmerr<1) return "";
    $rtn=$pmout[1];
    return $rtn;
}
*/

function cms_history_add($thistitle,$thispage){
  // gets last ten or so pages visited and lists them in a sidebar
  // Optionally don't add index page.. 
  // $apagefile=pathinfo($thispage);
  // if ($apagefile['filename'] == 'index') return;
  $ahistory=gets('history');
  if (is_array($ahistory)){
    $ahistory=array($thistitle=>$thispage)+$ahistory;
  }else{
    $ahistory=array($thistitle=>$thispage);  
  }
  if (count($ahistory)>9){
    $x=array_pop($ahistory);
  }
  sets('history',$ahistory);
}


function cms_history(){
  // gets last ten or so pages visited and lists them in a sidebar
  $out="";
  $ahistory=gets('history');
  foreach ($ahistory as $thistitle=>$thispage){
   $out.= "<a class=history_link href=$thispage >$thistitle</a>";
  }
  echo $out;
}

?>
