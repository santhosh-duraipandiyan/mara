<?php
include(plugin('captcha'));


function contactus_drawform($responsive=1){
  $captcha = new captcha();
  
  echo"<div id=\"cf_outer\">\n";
  
  if (isset($_POST['submit'])) { 
      setg('already_submitted','true','contactus');
      $botcheck=$captcha->validate(getr('captcha'));
      $merr=false;
      if ($botcheck==1) { 
         $merr=contactus_submitform();
      }else{
         echo "<h2 style='color:#aa0000'>Anti-robot check answered incorrectly!</h2>";
      }
      if ($merr) {
         echo "<h2 style='color:#007700'>Your enquiry has been submitted. Thank you.</h2>";
         echo "<p style='color:#007700'>".getg('postscript','','contactus')."</p>";
       } else {
         echo "<h3 style='color:#aa0000'>An error has occured whilst sending your message. <br> You may try again using the data below. <br>If unsuccessful, we suggest you phone us, or use the email contact address.</h3>";
       }
  }else{
      setg('already_submitted','false','contactus');
      ?>
      <h4><?php echo getg('preamble','Please fill in this form if you wish to make an enquiry','contactus')?></h4>
      <sub>Firstly, please prove you are human by entering the <i>number</i> described below as digits..</sub>
      <?php
  }
  $captcha->set("captcha",100,5000);
  // Use a number from 100 to 5000. A lower limit of less than 100 also allows decimals. 
  $captcha->cksum2js();
  $thispage=$_SERVER['PHP_SELF'];
  $thiscaptcha=gets('captcha');
  $post_name=getr('name');
  $post_address=getr('address');
  $post_email=getr('email');
  $post_telephone=getr('telephone');
  $post_message=getr('message');
  $post_attachments=getr('attachments');
  $ats='@';
  $our_telephone=getg('our_telephone','-','contactus');
  $direct_mail_account=getg('direct_mail_account','webform','contactus');
  $form_mail_account=getg('form_mail_account','webform','contactus');
  $domain_name=getg('domain_name','example','contactus');
  $toplevel_domain=getg('toplevel_domain','com','contactus');
  $encto=contactus_strtohex($direct_mail_account.$ats.$domain_name.'.'.$toplevel_domain);
  $fencto=contactus_strtohex($form_mail_account.$ats.$domain_name.'.'.$toplevel_domain);
  $enctel=contactus_strtohex($our_telephone);
  echo "<script>var encto='$encto'; var fencto='$fencto'; var enctel='$enctel';</script>";

  if ($responsive==1) {

echo <<<END1
    <form name="contactform" enctype="application/x-www-form-urlencoded" method="post" action="$thispage">
    <div id="cf_antirobot">
      <div class="inputboxdiv">
        <sub>Anti-robot check:</sub><br>
        <input class="inputbox" type="text" name="captcha"  size="30" maxlength="40" value="" onBlur='captcha_validate(this.value)' />*&nbsp;&nbsp;
      </div>
      <div class="captcha notranslate" >
        $thiscaptcha[1] <br /> $thiscaptcha[2]<br />&nbsp;&nbsp;&nbsp; $thiscaptcha[3]
      </div>
    </div><br clear=both>
    <div id="cf_sender">
      Name <br>
      <input class="inputbox" type="text" name="name"  size="30" maxlength="60" value="$post_name" />*<br>
      Address <br>
      <textarea class="inputbox" name="address"  cols="25" rows="4">$post_address</textarea><br>
      E-mail<br>
      <input type="text" name="email"  size="30" maxlength="60" value="$post_email" />*<br>
      Telephone<br>
      <input class="inputbox" type="text" name="telephone" size="30" maxlength="60" value="$post_telephone" /><br>
    </div>
    <div id="cf_message">
      Message<br>
      <textarea class="inputbox" name="message"  cols="50" rows="10">$post_message
      </textarea><br>&nbsp;&nbsp;<input type="submit" class="button" name="submit" onClick="return form_validator();" value="Send Message" />
    </div>
    <!-- you can put an image in here if you like -->
    <br style="clear:both;" />
    </form>
  <script>  var contactform=document.forms.contactform; contactform.captcha.focus();</script>
END1;
  } else {
echo <<<END2
    <form name="contactform" enctype="application/x-www-form-urlencoded" method="post" action="$thispage">
    <table width=100% cellspacing=10 ><tr><td colspan=3>
    <td></tr><tr><td><sub>Anti-robot check:</sub><br>
    <input class="inputbox" type="text" name="captcha"  size="30" maxlength="40" value="" onBlur='captcha_validate(this.value)' />*&nbsp;&nbsp;
    </td><td><br>
    <div class="captcha" >
      $thiscaptcha[1] <br /> $thiscaptcha[2]<br />&nbsp;&nbsp;&nbsp; $thiscaptcha[3]
    </div>
    <td></tr><tr><td>
    Name <br>
    <input class="inputbox" type="text" name="name"  size="30" maxlength="60" value="$post_name" />*<br>
    Address <br>
    <textarea class="inputbox" name="address"  cols="25" rows="4">$post_address</textarea><br>
    E-mail<br>
    <input type="text" name="email"  size="30" maxlength="60" value="$post_email" />*<br>
    Telephone<br>
    <input class="inputbox" type="text" name="telephone" size="30" maxlength="60" value="$post_telephone" /><br>
    </td><td>
    Message<br>
    <textarea class="inputbox" name="message"  cols="50" rows="10">$post_message
    </textarea><br>&nbsp;&nbsp;<input type="submit" class="button" name="submit" onClick="return form_validator();" value="Send Message" />
    </td><td>
    <!-- you can put an image in here if you like -->
    <br style="clear:both;" />
    </td><td>
    </tr></table></form>
    <script>  var contactform=document.forms.contactform; contactform.captcha.focus();</script>
END2;
  }
echo '</div>';
}

function contactus_launch_mailclient($text) {
  return "<a name='contactus_launch_email' href='javascript:contactus_launch_mailclient()'>$text</a>";
}

function contactus_show_email($text) {
  return "<a id='contactus_show_email' href='javascript:contactus_show_email(\"contactus_show_email\")'>$text</a>";
}

function contactus_show_tel($text) {
  return "<a id='contactus_show_tel' onMouseOver='javascript:contactus_show_tel(\"contactus_show_tel\")' href='javascript:contactus_show_tel(\"contactus_show_tel\")'>$text</a>";
}


function contactus_submitform(){
$captcha = new captcha();
$thiscaptcha=gets('captcha');
$at_symbol="@";
$form_mail_account=getg('form_mail_account','webform','contactus');
$domain_name=getg('domain_name','example','contactus');
$toplevel_domain=getg('toplevel_domain','com','contactus');
$subject = getg('subject','Website Enquiry','contactus');
$to = $form_mail_account . $at_symbol . $domain_name . '.' . $toplevel_domain;
$headers = "From: ". getr('email') . "\r\nX-Mailer: Mara cms Site Mailer" ;
$body = "~Website Form Enquiry~ \n\n";

foreach ($_POST as $thisid => $thisvalue){
  if (stripos("|submit|captcha|message|",$thisid)<1){
   $body.= "$thisid : $thisvalue \n";
  }
  if (stripos("|message|",$thisid)>0){
   $body.= "\n $thisvalue \n";
  }
}

// echo "<pre>$headers <hr> $body</pre>";
if ($domain_name=="example"){
  $merr=true;
  echo "<h2 style='color:#0000ff'>~ Demo mode only. No data sent ~ </h2>";
}else{
  $merr=mail($to, $subject, $body, $headers);
}

return $merr;

}



function contactus_strtohex($string)
{
    $hex='';
    for ($i=0; $i < strlen($string); $i++)
    {
        $hex .= dechex(ord($string[$i]));
    }
    return $hex;
}


function contactus_hextostr($hex)
{
    $string='';
    for ($i=0; $i < strlen($hex)-1; $i+=2)
    {
        $string .= chr(hexdec($hex[$i].$hex[$i+1]));
    }
    return $string;
}




?>
