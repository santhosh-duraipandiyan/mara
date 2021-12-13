<html>
<head>
  <title>Mara flatfile CMS</title>
  <meta http-equiv="X-UA-Compatible" content="IE=11" />
  <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
  <meta name="description" content="HTML pages, content managed">
  <meta name="keywords" content="content management, web backend, html, pages">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="Generator" content="Mara CMS">
  <meta name="Author" content="IWR Consultancy">
  <link rel="icon" href="<?php echo themedir ?>web.ico">
  <?php include_once plugin('menu'); ?>
  <?php include_once plugin('translate');?>
</head>
<body>

<div class="cms_pagecontainer">
  <div id="cms_top" >
    <div class="topmenu" id="cms_topmenu">
      <?php menu('tree.mnu', 'dropdown');?> 
    </div>
    <div id="cms_widgets">
      <?php widgets();?>
    </div>
  </div>

  <div id="cms_banner" >
    <img alt="" src='<?php echo themedir ?>maralogo500.png' class='center'>
  </div>

  <div id="cms_midrow">
    <div id="cms_left">
    </div>
    <div id="cms_content">
      <!--CONTENT--> 
    </div>
    <div id="cms_right">
    </div>
  </div>

  <div id="cms_bottom" >
    <span class='callout'>Powered by Mara cms</span>
  </div>

</div>


</body>
</html>
