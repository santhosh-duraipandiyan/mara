<html lang="en">
 <head>
   <title>Mara CMS</title>
   <meta name="description" content="HTML pages, content managed">
   <meta name="keywords" content="content management, web backend, html, pages">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <META NAME="cms_doctype" CONTENT="HTML">
   <meta name="Author" content="IWR Consultancy">
   <meta http-equiv="X-UA-Compatible" content="IE=11" />
   <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
   <link rel="shortcut icon" href="<?php echo themedir ?>web.ico">
   <?php include_once plugin('menu'); ?>
  </head>

 <body>
   <div id="cms_banner"  class="notranslate">
     <div id="cms_widgets"><?php widgets();?>&nbsp;</div>
     <h1>Mara flatfile CMS</h1>
   </div>
   <div id="cms_top"  class="notranslate">
     <div id="cms_topmenu" class="menutree topmenu" ><?php menu('tree.mnu','dropdown');?></div>
   </div>
     <div id="cms_content" >
       <!--CONTENT--> 
     </div>
   <div id="cms_bottom"  class="notranslate">
      Powered by Mara cms <br>
   </div>
 </body>
</html>
