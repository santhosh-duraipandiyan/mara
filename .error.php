<?php include_once 'codebase/reflex.php' ?>
<?php if (get('err')=="") {?>
<p><br>
<h1>Error Handler Page</h1>
<p>
<p>
This page is for use by the system. It should not be displayed or edited manually. 
<?php } ?>

<?php if (get('err')=='404') {?>
<p><br>
<h1>404: Page not found</h1>
<p>
<p>
Please check the main menu or sitemap for the content you are looking for. <br>The page you have requested may have been moved or deleted during recent site updates. 

<?php 
   if (gets('authenticated')>0){
    echo '<p style="color:red;font-weight:bold;"> Note that you cannot refresh editing previews, or change theme in a preview, because the file is temporary. </p> ';
   }
} ?>

<?php if (get('err')=='403') { ?>
<p><br>
<h1>403: Access Denied</h1>
<p><p>
Sorry. You do not have the rights needed to access this location. 
<?php } ?>

<?php if (get('err')=='500') { ?>
<p><br>
<h1>500: System Malfunction</h1>
<p><p>
An error has occurred. Please try again, opening the site from the homepage. 
<?php } ?>

<p><br>
<hr>
