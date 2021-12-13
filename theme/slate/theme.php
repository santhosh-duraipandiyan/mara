<html lang="en">
<head>
  <title>Mara flatfile CMS</title>
  <meta name="description" content="HTML pages, content managed">
  <meta name="keywords" content="content management, web backend, html, pages">
  <META NAME="cms_doctype" CONTENT="HTML5">
  <meta name="Author" content="IWR Consultancy">
  <meta http-equiv="X-UA-Compatible" content="IE=11" />
  <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
  <link rel="shortcut icon" href="<?php echo themedir ?>web.ico">
  <?php include_once plugin('menu'); ?>
</head>
<body>

<div id="cms_pagecontainer">
  <div id="cms_banner"  class="notranslate">
    <h1>Mara flatfile CMS</h1>
  </div>
  <div id="cms_top"  class="notranslate">
    <div class="menutree topmenu" ><?php menu('tree.mnu','dropdown');?></div>
    <div id="cms_widgets" ><?php widgets();?>&nbsp;</div>
  </div>
  <div id="cms_midsection">
    <div id="cms_left"  class="notranslate">
     <img  alt="" src="<?php echo themedir ?>sidelogo.png">
    </div>
    <div id="cms_content" >
      <!--CONTENT--> 
    </div>
  </div>
  <div id="cms_bottom"  class="notranslate">
   Powered by Mara cms <br>
  </div>
  &nbsp;  
</div>

</body>
</html>
