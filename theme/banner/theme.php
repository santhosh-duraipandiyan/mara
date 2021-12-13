<html>
<head>
  <title>Mara flatfile CMS</title>
  <meta name="description" content="HTML pages, content managed">
  <meta name="keywords" content="content management, web backend, html, pages">
  <META NAME="cms_doctype" CONTENT="HTML5">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="Author" content="IWR Consultancy">
  <meta http-equiv="X-UA-Compatible" content="IE=11" />
  <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
  <link rel="shortcut icon" href="<?php echo themedir ?>web.ico">
  <?php include_once plugin('menu'); ?>
</head>
<body>
<div class="cms_pagecontainer">
  <div id="cms_banner" >
    <div class="menutree topmenu" >
     <!--a class="menu_single" href="<?php echo siteroot ?>sitemap.php">Site Map</a-->
     <?php menu('tree.mnu','dropdown');?>
    </div>
    <div id="cms_banner_splash">
     <img src='<?php echo themedir ?>large_logo.png'><br clear=both>
     <span><a  href="#cms_content" title="Scroll down to page content">This&nbsp;Package</a></span>
     <span><a  href="http://maracms.com" title='Mara CMS website, with manual pages' target="_blank">User&nbsp;Manual</a></span>
    </div>
    <div id="cms_widgets"><?php widgets();?></div>
  </div>
  <div id="cms_top" >
  </div>
    <div name="cms_content" id="cms_content" >
      <!--CONTENT--> 
    </div>
  <div id="cms_bottom" >
    <br>
  </div>
  &nbsp;  
</div>

</body>
</html>
