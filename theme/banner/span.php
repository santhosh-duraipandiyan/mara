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
  <link rel=stylesheet href="<?php echo codedir ?>system.css" type="text/css">
  <link rel=stylesheet href="<?php echo themedir ?>theme.css" type="text/css">
  <link rel="shortcut icon" href="<?php echo themedir ?>hf.ico">
  <script language=javascript src="<?php echo codedir ?>system.js"></script>
  <script language=javascript src="<?php echo themedir ?>theme.js"></script>
</head>
<body>

<span class="cms_pagecontainer">

<span class="table cms_outerpagelayout"><span class="tr"><span class="td">

<span class="table cms_pagelayout">
<span class="tr"><span class="td" id="cms_banner" >
  <div float='left' align='left'><img src='<?php echo themedir ?>hylogo.png' align="left"></div>
  <div float='right' align='right'><span class='callout'>by IWR Consultancy</span></div>
</span></span>
<span class="tr"><span class="td" id="cms_top" >
  <div float='left' align='left'><?php sitemenu('top');?></div>
  <div float='right' align='right'><?php printbutton();?>&nbsp;</div>
</span></span></span>

<span class="table cms_pagelayout"><span class="tr">

<span class="td" id="cms_left">
  <div class="menutree">
    <?php sitemenu('side')?>
  </div>
  <hr width=90%>
</span>

<span class="td" id="cms_content" >
  <!--CONTENT--> 
</span>

<span class="td" id="cms_right">
</span></span></span>

<span class="table cms_pagelayout">
<span class="tr" id="cms_bottom" ><span class="td">
  <p class='callout'>Powered by Mara cms</p>
</span></span></span>

</span></span></span>

</span>

</body>
</html>
