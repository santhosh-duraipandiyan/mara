<html lang='en'>
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

<div id="cms_banner"  class="notranslate">
  <a href=<?php echo siteroot ?> ><h1>Mara cms blog interface</h1></a>
</div>

<div id="cms_top" class="notranslate" >
  <div id="cms_topmenu" class="menutree topmenu" >
    <?php menu('tree.mnu','dropdown');?>
    <?php menu('blog.mnu','dropdown');?>
  </div>
</div>

<div id="cms_middle" >

  <div id="cms_content" >
    <!--CONTENT-->
    <hr width=100%>
    <?php echo blog_catalog()?> 
  </div>

  <div id="cms_right" class="notranslate">
    <div id="cms_history">
      Recently&nbsp;Visited<br>
      <?php cms_history()?>
    </div>
    <div id="cms_blogroll">
      Related&nbsp;Pages<br>
      <?php menu('blogroll.mnu','');?>
    </div>  
  </div>
  

</div>
<div id="cms_bottom"  class="notranslate">
  <span>Powered by Mara cms</span>
</div>

</body>
</html>
