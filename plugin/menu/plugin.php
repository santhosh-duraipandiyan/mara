<?php
/*  ********* Menu Code *********** */

function sitemenu($thisposition='side', $menuplacement='default', $params=""){
 // Set up standard site menus - allows 'top' parameter to overide placement. 
if (stristr(getg('sitemenu'),"top")!==false) $menuplacement="top";
if (stristr(getg('sitemenu'),"side")!==false) $menuplacement="side";

 if ($thisposition=='top'){
   if ($menuplacement=='top' ) {
     topmenu(mainmenu,$params);
   } else {
     topmenu(quicklinkmenu,$params);
   }
 }
 if ($thisposition=='side' ){
    sidemenu(mainmenu,$params);
 }
}

// For backwards compatibility with <= v3 .. 
function sidemenu($menufile = "tree.mnu",$params="") {
  $xparams=$params . '';
  menu($menufile, $xparams);
}

function topmenu($menufile = "tree.mnu",$params="") {
  $xparams=$params . ' dropdown';
  menu($menufile, $xparams);
}

function menu_burger() {
  echo "<a class='menu_burger' href='#".siteroot."sitemap.php' onClick='return menu_burgerclick(this)'>&#8801;</a>\n";
}

function menu($menufile = "tree.mnu",$params="") {

 $isdropdown=false;
 $siteroot=siteroot; // can't use constants in echos.
 $relinc='menu_img/'; // location of icons in theme dir.
 $hdrlines='';$prlines='';

// Allow for testing of menu with target pages loading into separate iframe..
 $forcetarget=false;
 if (stripos($params,'test')!==false) {
   $forcetarget=true;
 }
 if (stripos($params,'dropdown')!==false) {
   $isdropdown=true;
 }
 echo "\n<script>topmenu_timeout=" . topmenu_timeout . " ; sidemenu_autoclose=" . sidemenu_autoclose . " ;</script>\n";

if (stripos($params,'string')===false) {
  if (!file_exists(fsconfigdir . $menufile)) {
   $menufile = fsconfigdir . $menufile; 
  }
 $menufile = substr($menufile,0,strripos($menufile,".")) . ".mnu" ;
 if (file_exists($menufile)):
  $menu=file($menufile);
 elseif (file_exists(fsconfigdir .$menufile)):
  $menu=file(fsconfigdir . $menufile);
 else:
  echo"[Menu file missing]";
  return false; 
 endif;
} else {
  // use string ajax data sent to routine instead of loading file ;
  $menu=explode("\n",$menufile);
  if (!is_array($menu)){
    echo"[Insufficient data to build a menu]";
    return false; 
  }
}    

// +=[] are special characters.;

// $menuloc = substr($menufile,0,strripos($menufile,"/")) ;

$menulink=0;
$page_found=false;
$default_visibility='none';
$mnu_stack[0]=0;
$mnu_stack['found']=false;

// echo "<div class='thismenu'>";
$menulevel=0; $menusection=""; // $mnu_activeid="";
$inremblk=false;
foreach ($menu as $line_num => $thisline) {
  // echo "Line #<b>{$line_num}</b> : " . htmlspecialchars($line) . "<br />\n";
 $prline=''; $hdrline=''; $mnuitem=false; $sshdr=false; $eoss=false; $thisaclass='menu_closed'; 
 $thisline=trim($thisline);
 if (stripos($thisline,';')===0) continue ; 
 if (stripos($thisline,'#')===0) continue ; 
 if (stripos($thisline,'//')===0) continue ; 
 if (stripos($thisline,'/*')===0) {$inremblk=true ; continue ;}  
 if (stripos($thisline,'*/')===0) {$inremblk=false ; continue ;} 
 if ($inremblk) continue ;
 if (stripos($thisline,"[")!==false) {
   $sshdr=true;
 } else {
   if (stripos($thisline,menu_separator)>0) $mnuitem=true;
 }
 if (stripos($thisline,"]")!==false) $eoss=true;
 $thisline=str_replace("[","",$thisline);
 $thisline=str_replace("]","",$thisline);
   $split=frag(menu_separator,$thisline);
   // $split=explode("=",$thisline);
 $item_sections=count($split);
 for ($ct=0;$ct<4;$ct++) {
   // prevent error on empty description..
   if (!isset($split[$ct])) $split[$ct]=="";
 }
 
 // Allow the use of > instead of = in URL params...
 if (menu_separator=='=') $split[1]=str_replace('>','=',$split[1]);
 
   if ($sshdr){
      $visibility=$default_visibility;
      // get +- visibility modifiers;
      if (stristr($split[0],"+")!=false) {$split[0]=str_replace("+","",$split[0]); $visibility="block"; $thisaclass='menu_open';}
      if (stristr($split[0],"-")!=false) {$split[0]=str_replace("-","",$split[0]); $visibility="none"; $thisaclass='menu_closed';}
      if ($visibility=="none"){
        $thisdivclass="menu_closed";
      } else {
        $thisdivclass="menu_open";
      }
      $menusection++; 
      $mnu_id='mnu_' . $menusection;
      if ($page_found==false) {
        $mnu_stack[0]+=1;
        // needs recoding as no ids now used. 
        // possibly count sections from top and use DOM node count in js? 
        $mnu_stack[$mnu_stack[0]]=$menusection;
      }
      
      // Separate level one headers here into preloader. Sections go into main part always. 
      if ($isdropdown==true && $menulevel==0) {
        $hdrline = $hdrline . "<a href='".siteroot."sitemap.php' onClick='return menu_dropdown(this)' class=menu_dropdown title=\"$split[1]\">$split[0]</a>\n";
        $prline = $prline . "<div class=menu_dropdown style='position:absolute;' >\n";
      }else{
        $prline = $prline . "<a href='".siteroot."sitemap.php' onClick='return show_menu(this)' class=$thisaclass  title=\"$split[1]\">$split[0]</a>\n";
        $prline = $prline . "<div class=$thisdivclass >\n";
      }
      $menulevel+=1;
   } else {
      // Mod (5.3) to fix out of order single topmenu links.. 
      // if ($isdropdown==true && $menulevel==0) {
        // $hdrline = $hdrline . "<a href=\"$thisurl\" class=menu_single title=\"$split[2]\">$split[0]</a>";
      // }   
   }
   
   if ($mnuitem){
     $menulink++;
     $thismenuitem='menuitem';
     //   Better method of highlighting selected item, no gets:
     $livelink=false;
     $split[1]=ltrim(trim($split[1]),'/');
     if ($split[1]==pagefile) $livelink=true;
     // Identify active menu link(s) where they point to a directory rather than a file..
     if ($split[1]=='') {
       // if menu item is null then it represents the site root 
       // which might point to index.htm or .html   
       // echo $split[1].'index'.'->>'.stripext(pagefile);
       if ($split[1].'index'==stripext(pagefile)) { 
         $livelink=true;
       }
     }
     if (substr($split[1],-1)=='/' ) {
       // if the menu item is / or str/ then it is a directory. 
      if ($split[1].'index'==stripext(pagefile)) { 
        $livelink=true;
      }
     }
     // echo $split[1].'|'. pagefile.'|'.$itemchk.'<br>';
     $thisitemidstr='';
     if ($livelink) {$page_found=true ; $thisitemid='menuitem_'.rand(); $thisitemidstr='id='.$thisitemid;  $thismenuitem='menu_selected';}
     $m_get="";
     // if ($params!="") $m_get='?ms=' . $params;
     if ($item_sections>1){
        // echo "$siteroot$split[1]$m_get<br>\n ". siteroot . pagefile ;
        $thisurl=$split[1];
        if ($forcetarget===true) {
          // in test mode, force all output to preview frame..   
          if (stripos($thisurl,"//")===false) {
            $thisurl = $siteroot.$thisurl.$m_get .  "\" target=\"target_preview_frame" ;
          } else {
             $thisurl = $thisurl .  "\" target=\"target_preview_frame" ;
          }
        } else {
          // in live mode, all links are either relative to siteroot or offsite.. 
          if (stripos($thisurl,"//")===false) {
             if (multiviews){
               $thisurl=$siteroot.stripext($thisurl).$m_get ;
             } else {
               $thisurl=$siteroot.$thisurl.$m_get ;
             }
          } else {
             $thisurl = $thisurl .  "\" target=\"_blank" ;
          }
        }
        // If we are in topline of dropdown, single items have already been handled by header list so do not repeat.
        // (Combines with 5.2 fix for out of order items) 
        if ($isdropdown==true && $menulevel==0) {
          $thismenuitem='menu_single';
          $hdrline = $hdrline . "<a href=\"$thisurl\" class=menu_single title=\"$split[2]\">$split[0]</a>";
        } else {
          $prline = $prline . "<a href=\"$thisurl\" $thisitemidstr class=$thismenuitem title=\"$split[2]\">$split[0]</a>";
          if ($thisitemidstr!='') $prline = $prline .  "<script>menu_show_active_tree('".$thisitemid."')</script>";
        }
     }
   }

   if ($eoss) {
     if ($menulevel>0)$menulevel-=1;
     $prline = $prline  . '</div>';
     if ($page_found==false) {
       $mnu_stack[0]-=1;
     }
   }

    if (strlen($hdrline)>0) $hdrlines.=$hdrline . "\n" ;
    $prlines .= $prline . "\n" ;
}  // end collation loop
   
    if (strlen($hdrlines)>0) echo $hdrlines . "\n" ;
    echo $prlines . "\n" ; 
    $hdrlines='';$prlines='';
    
}


?>
