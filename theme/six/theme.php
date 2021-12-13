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

<div class="table cms_outerpagelayout"><div class="tr"><div class="td">

<div class="table cms_pagelayout">
<div class="tr"><div class="td " id="cms_banner" >
  <div style='text-align:left' ><img alt="" id="cms_toplogo" src='<?php echo themedir ?>maralogo500.png' style="float:left;"></div>
  <div style='text-align:right' id='cms_iwrc' ><span class='callout'>by&nbsp;IWR&nbsp;Consultancy</span></div>
</div></div>
<div class="tr"><div class="td " id="cms_top" >
  <div class="menutree topmenu" id="cms_topmenu" style='text-align:left'>
    <?php menu('tree.mnu', 'dropdown');?>
  </div>
  <div class="menutree topmenu" id="cms_quicklinks">
    <?php sitemenu('top');?>
  </div>
  <div id="cms_widgets"><?php widgets();?></div>
</div></div></div>

<div class="table cms_pagelayout"><div class="tr">
  <div class="td " id="cms_left" >
    <div class="menutree sidemenu">
      <?php sitemenu('side')?>
    </div>

    <div id="socsites">
    <img  alt="Facebook" src='<?php echo themedir ?>facebook.gif' title="http://facebook.com/" onClick="pic_url(this)" >&nbsp;
    <img  alt="Twitter" src='<?php echo themedir ?>twitter.gif' title="http://twitter.com"  onClick="pic_url(this)"><br>
    <img  alt="HTML5" src='<?php echo themedir ?>html5-30.png' style='margin-top:30px;margin-right:10px;' title="http://www.w3.org/html/logo/"  onClick="pic_url(this)">
    <img  alt="CSS3" src='<?php echo themedir ?>css3-30.png' style='margin-top:30px;' title="http://www.w3.org/Style/"  onClick="pic_url(this)"><br>
    <img  alt="Funding" src='<?php echo themedir ?>funding.png' style='margin-top:30px;' title="https://iwrconsultancy.co.uk/funding"  onClick="pic_url(this)">
    </div>
  </div>

  <div class="td" id="cms_content" >
  <!--CONTENT--> 
  </div>

  <div class="td " id="cms_right">
      <!-- A righthand vertical bar, if used  -->
      <div id='rhslinks'>
        Permalinks 
        <a href="https://maracms.com" target="_blank">Help</a>
        <a href="https://iwrconsultancy.co.uk" target="_blank">Main site</a>
        <a href="https://iwrconsultancy.co.uk/download" target="_blank">Downloads</a>
        <a href="https://sf.net/projects/maracms" target="_blank">Sourceforge</a>
        <a href="https://iwrconsultancy.co.uk/funding" target="_blank">Contribute</a>
       </div> 
       <div id="rhsbanner">
         <img  alt="" src="<?php echo themedir ?>rhsbanner.png">
       </div>
  </div>

</div></div>

<div class="table cms_pagelayout">
<div class="tr" id="cms_bottom" ><div class="td ">
  <span class='callout'>Powered by Mara cms</span>
</div></div></div>
<br>
</div></div></div>

</div>

</body>
</html>
