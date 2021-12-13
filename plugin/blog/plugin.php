<?php
//include_once codebase.'simple_html_dom.php';

function blog_catalog($blogpath="",$maxpara=200,$datatype='html'){

 $catalog="";
 $menu='';
 $tocsecsize=20;
 $mnusecsize=30;
 $tocsection=1;
 $mnusection=1;
 $mnuct=0;
 $tocct=0;
 $catct=0;
 $thisview=get('view');
 if (!isset($_GET['view'])){
    $apagefile=pathinfo(pagefile);
    if ($apagefile['filename'] == 'index') $thisview='index';
 }

 if ($thisview!='index'){
    return "";
 }
 
 $filetypes=array('php','htm','html');
 if ($blogpath==""){
   $blogpath = pathname(pagefile); 
 } else {  
   $blogpath = path($blogpath);
 }
 // echo siteroot . pathname(pagefile) . ' ~ ' . $blogpath."<br>";
 $catfile=fsroot.$blogpath.'blog.cat';
 $menufile=fsroot.'sitecfg/blog.mnu';
 // echo "<br>".$menufile."<br>";
 $rebuild=true;
 if (file_exists($catfile)){
   $lastupdate=filemtime($catfile);
   if (time()<$lastupdate+60) $rebuild=false;
   if (gets('user_privelege')>1) {
   if (isset($_GET['rebuild'])||isset($_GET['reload'])) $rebuild=true ;
   }
 }
 
if ($rebuild) { 
 $ct=0;
 $postlist[0]['catalogtime']=time();
 // echo "Rebuilding catalog... ";
 $blogpages = scandir(fsroot . $blogpath);
 if (count($blogpages)<1) {
    echo "<h2>Blog appears to be empty.</h2>";
    return;
 }    
 $ordinal=0; 
 foreach ($blogpages as $thispage) {
   if ($thispage=='sitemap.php') continue;
   if(substr($thispage,0,1)==".") continue;
   $fspage=fsroot.$blogpath.$thispage;
   $abspage=siteroot.$blogpath.$thispage;
   $relpage=$blogpath.$thispage;
   $dir=is_dir($fspage);
   if ($dir) continue;

   $athispage=pathinfo($thispage);
   // Optionally, don't list blog index page  .. 
   if (pathinfo($thispage,PATHINFO_BASENAME) == pathinfo(pagefile,PATHINFO_BASENAME)) continue;
   $thisext = strtolower(substr(strrchr($thispage, '.'), 1));
   if(!in_array($thisext,$filetypes)) continue;
   $postcreatedtime=filemtime($fspage); // filectime is not reliable. 
   $postmodifiedtime=filemtime($fspage);
   $fh = fopen($fspage, 'rb') ;
   // echo $fspage. "<br>";
   $thispost=stream_get_contents($fh);
   fclose($fh);
   $postbody=tagdata($thispost,'body', true);
  
 $callingpage=$thispost;

// get sections of page... (identical to reflex.php code)

 $ahead=tagdata($callingpage,'head', false);
 $abody=tagdata($callingpage,'body', true);
 $atitle=tagdata($ahead[2],'title', false);
 $ametas=@getmetas($ahead[2]);

 if (stripos($callingpage,"plugin('blog')")!==false) continue;

/* Title and description handling: 
   If page header has no title, or title matches theme title, 
   then use first header as title, use first para as description, or next header if no paras. */

if (strlen($atitle[2])<2 ) {
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
  $thisdesc=$pdescription[2];
  $thistitle= $pfirstheading;
} else {  
  // Title and description present, use them. 
  $thistitle= $atitle[2];
  $thisdesc=@$ametas['description']['content'];
}
  // Fixup varnames, possibly temp..
  $postfirstheading=$thistitle;
  // Don't list pages with no information..
  if ($thistitle . $thisdesc=="") continue;
  $metactime=@$ametas['creation_time']['content'];
  if ($metactime>0) $postcreatedtime=$metactime;

// Get first image for description 
   $catimg_src="";
   $catimg_class="";
   $catimg_alt="";
   $pmerr=preg_match_all('~(<img.*src\s*=.*>)~siU',$callingpage,$aimages,PREG_PATTERN_ORDER); 
   if (is_array($aimages)){
    foreach ($aimages[0] as $thisimage){
      $thisalt=cms_img_attr($thisimage,'alt');
      // echo $thisimage."<br>";
      // ignore images with no alt/caption..
      if (strlen($thisalt)>0) {
       $thisclass=cms_img_attr($thisimage,'class');
       $catimg_class=$thisclass;
       $catimg_src=siteroot . $blogpath . cms_img_attr($thisimage,'src');
       $catimg_alt=cms_img_attr($thisimage,'alt');
       // echo "<textarea>".htmlspecialchars($catimg_src.'|'.$catimg_class)."</textarea><br>";
       break;
      }
    }
   }
  $ct++;
  // Puts catalog to the top of listing.. 
  if (pathinfo($thispage,PATHINFO_BASENAME) == pathinfo(pagefile,PATHINFO_BASENAME)){
    $sortkey=time();
    $relpage.= '?view>content' ;
    $abspage.= '?view=content' ;
    $pagetype='index' ;
    $postdescription=$thisdesc;
  }else{  
    $sortkey=$postcreatedtime; 
    $pagetype='content' ;
    $postdescription=cms_truncate($thisdesc,$maxpara);
  }
  
  $pagelist[$ct]['image_class']=$catimg_class;
  $pagelist[$ct]['image_src']=$catimg_src;
  $pagelist[$ct]['image_alt']=$catimg_alt;
  $pagelist[$ct]['sortkey']=$sortkey ;
  $pagelist[$ct]['pagetype']=$pagetype ;
  $pagelist[$ct]['relpage']=$relpage ;
  $pagelist[$ct]['abspage']=$abspage ;
  $pagelist[$ct]['postcreatedtime']=$postcreatedtime ;
  $pagelist[$ct]['postfirstheading']= $postfirstheading;
  $pagelist[$ct]['postdescription']= $postdescription;
  $pagelist[$ct]['tocsection']= '';
  if ($postcreatedtime>1000000000){
    $pagelist[$ct]['datestring']=gmDate("Y-m-d\ H:i",$postcreatedtime);
  } else {
    $pagelist[$ct]['datestring']='';
  }
  // echo $pagelist[$ct]['postfirstheading'].'<br>';
 } //foreach

//  krsort($pagelist);
  $pagelist= cms_xsort($pagelist, 'sortkey', 'descending');
  
 // Build catalog menu content, and toc page sections.. 

  $menu.="+[Recent Articles\nBlog Index=".pagefile."=A list of articles, ordered by date of first posting\n";
  foreach ($pagelist as $thisindex => $thispage){
    $mnuct++;
    $tocct++;
    if ($tocct>$tocsecsize){
      $tocct=0;
      $tocsection++;
    }  
    $pagelist[$thisindex]['tocsection']=$tocsection;
    if ($mnuct>$mnusecsize){
      $mnuct=0;
      $mnusection++;
       if ($mnusection==2) {
         $menu.= "[Previous\n";
       }else{
         $menu.= "]\n";
       }
      $menu.="[To ".gmDate("Y m d",$thispage['postcreatedtime'])."\n";
    }
    $menu.=$thispage['postfirstheading'].'='.$thispage['relpage'].'='.str_replace('=',' ',$thispage['postdescription'])."\n";
  }
  if ($mnusection>1) $menu.= "]\n]\n";
  $menu.="]\n";


 // write serialized catalog out to cache file.. 
  $fh = fopen($catfile, 'w') or die("Error: can't write blog catalog file" . $catfile);
  $str_pagelist=serialize($pagelist);
  fwrite($fh, $str_pagelist);
  fclose($fh);
  $fh = fopen($menufile, 'w') or die("Error: can't write blog menu file" . $menufile);
  fwrite($fh, $menu);
  fclose($fh);
} else {
  $fh = fopen($catfile, 'rb') or die("Error: can't read catalog file" );
  $str_pagelist=stream_get_contents($fh);
  fclose($fh);
}  //if

  if ($datatype=='html'){
   $pagelist=unserialize($str_pagelist);
   $catalog.="<ul style='list-style-type:none;padding:0px;'>";
   // $catalog.='<style> div.bctable {display:table;width:100%;} div.bctr {display:table-row} div.bctd {vertical-align:top;display:table-cell;border:1px solid red;} div.bcimg{width:40%;}</style>';
   foreach ($pagelist as $thisindex => $thispage){
     if ($thispage['pagetype']!='index'){
      if ($thispage['image_src']!='') $catalog.="<a href='".$thispage['abspage']."'><img src='".$thispage['image_src']."' title='".$thispage['postfirstheading']."' style='clear:both;margin:20px;max-width:300px;max-height:150px;float:right;' ></a>";
      $catalog.="<li class='blog'><h2 class='blog'><a href='".$thispage['abspage']."'>".$thispage['postfirstheading']." </a>";
      $catalog.="<small class='blogdate'>".$thispage['datestring']."</small></h2>";
      $catalog.="<p class='blogdesc'>".$thispage['postdescription'];
      $catalog.="<a href='".$thispage['abspage']."'><small>...More</small></a></p>\n";
      $catalog.='</li>';
     }
   }
   $catalog.='</ul>';
  }else{
    $catalog=$str_pagelist;
  }
 return $catalog;
} //fn


/*
foreach($dcontents as $ordinal=>$thisitem) {
   $itemcount++; 
   $thisitem['description']="File: ". $thisitem['name']. "\nLocation: " .$thisitem['relpath']."\nUploaded: ".@date('Y/m/d', $thisitem['time'])."\nSize: ".formatBytes($thisitem['size']);
*/

function cms_xsort($inarray,$sortkey,$sort_order='ascending'){
 // Sorts an array on specified 2nd level value
 $aindex=array(); // php7;
 $outarray=array(); // php7;
 foreach($inarray as $thiskey=>$thisval){
  if (isset($thisval[$sortkey])) {
   $aindex[$thiskey]=$thisval[$sortkey];
  }else{
   $aindex[]=$thisval[$sortkey];
  }
 }
 if ($sort_order=='descending') arsort($aindex);
 if ($sort_order=='ascending') asort($aindex);
 if ($sort_order=='natural') natcasesort($aindex);
 foreach($aindex as $thiskey=>$thisval){
  $outarray[$thiskey]=$inarray[$thiskey];
 }
 return $outarray;
}


/*
(not presently used) 
function array_orderby()
{
    $args = func_get_args();
    $data = array_shift($args);
    foreach ($args as $n => $field) {
        if (is_string($field)) {
            $tmp = array();
            foreach ($data as $key => $row)
                $tmp[$key] = $row[$field];
            $args[$n] = $tmp;
            }
    }
    $args[] = &$data;
    call_user_func_array('array_multisort', &$data);
    return array_pop($args);
}
*/
?>
