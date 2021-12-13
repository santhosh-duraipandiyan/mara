<html>
<head>
  <title>Mara flatfile CMS</title>
  <meta name="description" content="HTML pages, content managed">
  <meta name="keywords" content="content management, web backend, html, pages">
  <META NAME="cms_doctype" CONTENT="HTML5">
  <META NAME="cms_hide" CONTENT="">
  <meta name="Author" content="IWR Consultancy">
  <meta http-equiv="X-UA-Compatible" content="IE=11" />
  <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
  <link rel="shortcut icon" href="<?php echo themedir ?>web.ico">
  <?php include_once plugin('menu'); ?>
  <?php include_once plugin('blog'); ?>
</head>
<body>
<div class="cms_pagecontainer">

<div class="table cms_outerpagelayout"><div class="tr"><div class="td">

<div class="table cms_pagelayout">
<div class="tr"><div class="td" id="cms_banner" >
  <div style='text-align:left'><a href=<?php echo siteroot ?> ><img src='<?php echo themedir ?>logo.png' align="left"></a></div>
  <div style='text-align:right'><span class='callout'>by&nbsp;IWR&nbsp;Consultancy</span></div>
</div></div>
<div class="tr"><div class="td" id="cms_top" >
  <div class="menutree topmenu" style='text-align:left'>
  <a class="menu_single" href="<?php echo siteroot ?>sitemap.php">Site Map</a>
  <?php menu('tree.mnu','dropdown');?>
  <?php menu('blog.mnu','dropdown');?>
</div>
  <div style='text-align:right'><?php widgets();?>&nbsp;</div>
</div></div></div>

<div class="table cms_pagelayout"><div class="tr">

<div class="td" id="cms_left" >
  <div class="menutree sidemenu">
  <?php menu('blog.mnu','tree');?>
  </div>
</div>

<div class="td" id="cms_content" >
  <!--CONTENT-->
  <?php 
   $str_pagelist=blog_catalog("",200,'serialized'); 
   $apagelist=unserialize($str_pagelist);
   $out= "<table cols=3 class='blog_toc'><tr>\n";
   $page=0;
   foreach ($apagelist as $thisindex => $thispage){
     if ($thispage['pagetype']!='index'){
       $page++;
       if ($page==1):
         $out.="<td colspan=3 class='blog_masthead'>";
         $out.="<h2 ><span class='newtag'>new</span><a href='".$thispage['abspage']."'>".$thispage['postfirstheading']." </a>";
         $out.="<small class='blogdate'>".$thispage['datestring']."</small></h2>";
         $out.="<p >".$thispage['postdescription'];
                $out.="<a href='".$thispage['abspage']."' class='more'> More</a>";
         $out.="</td></tr><tr>";
       endif;
       if ($page>1 && $page<5):
         $out.="<td class='toc_banner'>";
         if ($thispage['image_src']!='') $out.="<a href='".$thispage['abspage']."'>
         <img src='".$thispage['image_src']."' title='".$thispage['postfirstheading'].
         "' ></a><br>";
         $out.="<h2 class='blog' style='text-align:center'><a href='".$thispage['abspage']."'>".$thispage['postfirstheading']." </a></h2>";
         $out.="<small class='blogdate'>".$thispage['datestring']."</small>";
         $out.="<p class='blogdesc'>".$thispage['postdescription'];
         $out.="</p>\n";
         $out.="</td>";
       endif;
       if ($page==4):
         $out.="</tr></table>\n";
         $out.="<hr width=100%><h4>Previous Articles</h4>\n";
         $out.="<ul style='list-style-type:none;padding:0px;'>\n";
       endif; 
       if ($page>4): 

       if ($thispage['image_src']!='') $out.="<a href='".$thispage['abspage']."'><img src='".$thispage['image_src']."' title='".$thispage['postfirstheading']."' style='clear:both;margin:20px;max-width:300px;max-height:150px;float:right;' ></a>";
       $out.="<li class='blog'><h2 class='blog'><a href='".$thispage['abspage']."'>".$thispage['postfirstheading']." </a>";
       $out.="<small class='blogdate'>".$thispage['datestring']."</small></h2>";
       $out.="<p class='blogdesc'>".$thispage['postdescription'];
       $out.="<a href='".$thispage['abspage']."'><small>...More</small></a></p>\n";
       $out.='</li>';
       endif;
     }
   }
     if ($page<4) $out.='</tr></table>';
//     $out.="</table>\n";
       $out.="</ul>\n";
   if ($page>0)echo $out;
  ?>
<hr width=100%>
<p><?php if (stripos('/'.pagefile,'/index.')===false) include(plugin('disqus')) ?></p>
</div>

<div class="td" id="cms_right">
Recently&nbsp;Visited<br>
<?php cms_history()?>
<br>Related&nbsp;Pages<br>
<div class='blogroll'>
<?php menu('blogroll.mnu','');?>
</div>

</div></div></div>

<div class="table cms_pagelayout">
<div class="tr" id="cms_bottom" ><div class="td">
  <span class='callout'>Powered by Mara cms</span>
</div></div></div>
<br>
</div></div></div>

</div>

</body>
</html>
