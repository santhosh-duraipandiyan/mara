<html lang="en">
<head>
  <title>Mara flatfile CMS</title>
  <meta name="description" content="HTML pages, content managed">
  <meta name="keywords" content="content management, web backend, html, pages">
  <META NAME="cms_doctype" CONTENT="HTML5">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="Author" content="IWR Consultancy">
  <link rel="shortcut icon" href="<?php echo themedir ?>web.ico">
  <?php include_once plugin('menu'); ?>
  <?php include_once plugin('translate');?>
</head>
<body>
<div class="cms_pagecontainer">
  <div id="cms_banner" >
    <div id="cms_menu_container">
      <div id="cms_menu" class="topmenu bannermenu" >
       <?php menu('tree.mnu','dropdown');?>
      </div>
    </div>
    <?php // cms_imgshow(5); ?>
    <span id='cms_faders'>
    <img src='img/frontpage/1.jpg' title='' class='cms_fader' id='cms_fader1' alt='' > 
    <img src='img/frontpage/6.png' title='' class='cms_fader' id='cms_fader2' alt='' > 
    <img src='img/frontpage/3.jpg' title='' class='cms_fader' id='cms_fader3' alt='' > 
    <img src='img/frontpage/4.jpg' title='' class='cms_fader' id='cms_fader4' alt='' > 
    <img src='img/frontpage/5.jpg' title='' class='cms_fader' id='cms_fader5' alt='' > 
    </span>
    <div id="cms_splash_container" >
      <div id="cms_splash">
        <img  alt="" src='<?php echo themedir ?>large_logo.png'><br clear=both>
        <span><a  href='https://iwrconsultancy.co.uk/download#maracms' title="Go to our main site's  download page">Download</a></span>
        <span><a  href='editing.php' title='User instructions, and online editing session'>Test&nbsp;Drive</a></span>
      </div>
    </div>
    <div id="cms_spacer">
      <div>
        &nbsp;
      </div>
    </div>
  </div>

  <div id="cms_top" >
      <div id="cms_secondmenu" class="topmenu floatmenu" >
       <?php menu('tree.mnu','dropdown');?>
      </div>
      <div id="cms_widgets">
        <?php widgets();?>
      </div>
  </div>
  
  <div name="cms_content" id="cms_content" >
     <!--CONTENT--> 
  </div>

  <div id="cms_bottom" >
    Powered by Mara cms
  </div>

  &nbsp;  
</div>

</body>
</html>

<?php
function cms_imgshow($num) { 
  $out='';
  for ($ct=1; $ct<=$num; $ct++){ 
    $out .= "<img src='img/frontpage/$ct.jpg' title='' class='cms_fader' id='cms_fader$ct' alt='' > \n";
  }
  echo $out;
}
?>