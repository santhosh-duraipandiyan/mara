<?php include_once 'codebase/reflex.php' ?>
<html>
<head>
  <title>Example Contact Page</title>
  <meta name="description" content="A contact form with harvesting protection">
</head>
<body>
<h1>Contact Form</h1>

<?php

// set the delivery parameters to suit your email system:
// Note that the '@' and the '.' between 'example' and 'com' are added automatically.  
// syntax is setg('variable','value','context') -so you change the middle item. 

setg('preamble','Please fill in this form if you wish to make an enquiry or request a quote for specific work.','contactus');
setg('postscript','We typically expect to reply to email enquiries within one working day. <br>If you require more urgent help with a mission-critical problem, please telephone us.','contactus');
setg('our_telephone','01 1234 567','contactus');
setg('domain_name','example','contactus');
setg('toplevel_domain','com','contactus');
setg('form_mail_account','webform','contactus');
setg('direct_mail_account','webform','contactus');
setg('subject','Website Enquiry','contactus');

include(plugin('contactus'));
contactus_drawform();
echo "<hr style='margin:25px 0px;'>Alternatively you can " . contactus_launch_mailclient('email us') ;
echo " directly on " . contactus_show_email('this address') . " or call us on " . contactus_show_tel('this number') . " if you prefer.<br>" ;
?>

<p>Not that the contact form plugin will appear as a placeholder whilst you are in editing mode. To see and set the form parameters, press the View Page Source button. (Second from right on top row of editor toolbar) The instructions are on the plugin page of maracms.com.</p>

</body>
</html>