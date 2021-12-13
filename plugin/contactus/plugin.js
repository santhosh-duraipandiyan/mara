captcha_validated=false;
contactform=document.forms.contactform;

function carryover_gets(){
// Insert variables carried over from product page ;
   document.$_GET = [];
   var urlHalves = String(document.location).split('?');
   if(urlHalves[1]){
      var urlVars = urlHalves[1].split('&');
      for(var i=0; i<=(urlVars.length); i++){
         if(urlVars[i]){
            var urlVarPair = urlVars[i].split('=');
            document.write('<input type="hidden" name="' + htmlentities(urlVarPair[0]) + '" size="20" maxlength="64" value="' + htmlentities(urlVarPair[1]) +  '" >');
            if (urlVarPair[0]=="item_number"){ 
              document.forms.contactform.message.value="I am interested in product " + urlVarPair[1] + " and need custom sizing or other adjustments, as follows: ";
            }
         }
      }
   }
}   


function captcha_validate(captcha_response){
  var contactform=document.forms.contactform;
  if (captcha_response>0 && captcha_cksum == sha256(captcha_response).substring(1,3)){
    captcha_validated=true;
    contactform.captcha.style.background='#bbffaa';
  }else{
    contactform.captcha.style.background='red';
    captcha_validated=false;
  }
}

function form_validator(){
//return true;
  var contactform=document.forms.contactform;
  var valid=true;
  var index;
  var nonblank='|captcha|name|email|message|';
  for (index = 0; index < contactform.length; ++index) {
    if (nonblank.indexOf(contactform[index].name)>0){
      if (contactform[index].value.length>3){
        contactform[index].style.background='';
      }else{  
        contactform[index].style.background='red';
        // contactform[index].style.background='-moz-linear-gradient(top left ,  #c00, #fff ,#fff , #fff)'; 
      }
    }
  }
    if (contactform.email.value.indexOf('@')<1){ valid=false;contactform.email.style.background='red'; };
    if (valid==false){alert("Please correct the highlighted items.\nYour message has NOT been sent." );};
    return valid;
}

function contactus_launch_mailclient(){
  if (captcha_validated){
    var mt='6d61696c746f3a'
    document.location=contactus_hextostr(mt+fencto);
  }else{
    alert("Please respond to the anti-robot check in order to unlock the email interface.")
  }
}

function contactus_show_email(thisdiv){
  if (captcha_validated){
//     alert(thisdiv.text)
     thisdivid=document.getElementById(thisdiv);
     thisdivid.outerHTML='<strong>' + contactus_hextostr(encto) + '</strong>' ;
  }else{
     alert("Please respond to the anti-robot check in order to unlock the email interface.")
     // thisdiv.text="~-~";
  }
}

function contactus_show_tel(thisdiv){
  if (captcha_validated+1){
//     alert(thisdiv.text)
     thisdivid=document.getElementById(thisdiv);
     thisdivid.outerHTML='<strong>' + contactus_hextostr(enctel) + '</strong>' ;
  }else{
     alert("Please respond to the anti-robot check in order to show the telephone number.")
     // thisdiv.text="~-~";
  }
}

function contactus_hextostr(hex)
{
    var cdl='';
    for (i=0; i < hex.length -1; i=i+2)
    {
        // if (i>0) {cdl = cdl + ','};
        cdl = cdl + String.fromCharCode('0x' + hex.substr(i,2));
    }
    return cdl;
}
 

