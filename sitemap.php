<?php include_once 'codebase/core.php'; ?> 
<!doctype html>
<head>
    <title>Site Map</title>
    <meta name="description" content="Direct links to site pages. Ideal for textreaders and mobile devices.">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <meta name="cms_hide" content="theme">
    <php include_once plugin('translate')?>
</head>
<body>
<link rel=stylesheet href="<?php echo codedir ?>sitemap.css" type="text/css">
<h1>Site Map</h1>
<hr align="left">
<noscript>You have been redirected to the sitemap because your browser either does not support scripting, or scripting has been turned off.<br></noscript>
<?php
function efrag($key, $thisstr, $nparts=3){
  $split=explode($key, $thisstr);
  for ($ct=0;$ct<=$nparts;$ct++) {
    if (!isset($split[$ct])) $split[$ct]="";
  }
 return $split;
}
$menufile=fsconfigdir . "tree.mnu";

$menu=file($menufile);
$indent=40;
$menulevel=0;
$inremblk=false;

foreach ($menu as $line_num => $thisline) {
  // echo "Line #<b>{$line_num}</b> : " . htmlspecialchars($line) . "<br />\n";
 $prline=''; $mnuitem=false; $sshdr=false; $eoss=false;
 $thisline=trim($thisline);
 // Remark filter added to sitemap, 4.1 
 if (stripos($thisline,';')===0) continue ; 
 if (stripos($thisline,'#')===0) continue ; 
 if (stripos($thisline,'//')===0) continue ; 
 if (stripos($thisline,'/*')===0) {$inremblk=true ; continue ;}  
 if (stripos($thisline,'*/')===0) {$inremblk=false ; continue ;} 
 if ($inremblk) continue ;

 if (stripos($thisline,"[")!==false) {
   $sshdr=true;
 } else {
   if (stripos($thisline,"=")>0) $mnuitem=true;
 }
 if (stripos($thisline,"]")!==false) $eoss=true;
 $thisline=str_replace("[","",$thisline);
 $thisline=str_replace("]","",$thisline);
 $split=frag("=",$thisline);
 $item_sections=count($split);
 $mnu_leftpad = ($indent * $menulevel) . 'px';

   if ($sshdr){
      $menulevel+=1;
      if (stristr($split[0],"+")!=false) {$split[0]=str_replace("+","",$split[0]);}
      if (stristr($split[0],"-")!=false) {$split[0]=str_replace("-","",$split[0]);}
      $prline = $prline . "<br><span class=isheader style=\"padding-left:$mnu_leftpad;\">$split[0]:  <small>$split[1]</small></span><br>\n";
   }
   if ($mnuitem){
     if ($item_sections>1){
      if (strpos('~'.$split[1],'sitemap')!=1) {
        $prline = $prline . "<a href=\"".siteroot."$split[1]\" style=\"padding-left:$mnu_leftpad;\" title=\"$split[2]\">$split[0]</a><br>";
      }
     }
   }
   if ($eoss) {
     if ($menulevel>0)$menulevel-=1;
   }
    echo $prline . "\n" ;
}


?>
</body>
</html>
