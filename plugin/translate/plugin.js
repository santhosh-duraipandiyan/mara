// Check if translation has already been turned on for this site, and initialize on new page if it has..
var googtrans=trans_getCookie('googtrans');
if ( (googtrans.length>0) || (location.search.indexOf('translate')>=0 ) ) {
  // alert(googtrans.length + " " + location.search.indexOf('translate'))
  cms_translate()
}

setTimeout(trans_setwidget,1000);

function trans_setwidget(){
 var wdiv= document.getElementById('cms_widgets');
 var wdt=wdiv.innerHTML;  
 var wt="<button title='Translate this page' onClick='cms_translate()' class='widget' style='border:1px solid blue;font-weight:bold;vertical-align:middle;margin:0px 2px 3px 2px;border-radius:3px'>T</button>";
 wdiv.innerHTML=wt + wdt; 
}

function trans_scope(){
 // These determine which areas of the page will be translated. 
 // By deafult only the page content is translated, since translating menus etc may give rise to readability issues.   
 // If you know that is not the case for the languages you will offer, then comment-out with // any which you wish to be translated.  
    trans_addclass('cms_banner','notranslate');
    trans_addclass('cms_top','notranslate');
    trans_addclass('cms_left','notranslate');
    trans_addclass('cms_right','notranslate');
    trans_addclass('cms_bottom','notranslate');

 // You will probably also want to set auto right-to-left text for any translated region..
    document.getElementById('cms_content').setAttribute('dir','auto'); 
 // document.getElementById('cms_banner').setAttribute('dir','auto'); 
 // document.getElementById('cms_top').setAttribute('dir','auto'); 
 // document.getElementById('cms_left').setAttribute('dir','auto'); 
 // document.getElementById('cms_right').setAttribute('dir','auto'); 
 // document.getElementById('cms_bottom').setAttribute('dir','auto'); 
}


// Probably best not to change anything below here.. 

function trans_addclass(thiselement,thisclass) {
 if (document.getElementById(thiselement)){
    var cn = document.getElementById(thiselement).className;
    document.getElementById(thiselement).className = cn + ' ' + thisclass;
 } 
}

// Deals with race hazard on js startup in some browsers.. 
function trans_init_delay(){
  cms_translate();
}

// This is the main function called when the Translate button is pressed.
// It adds a div to the page body, sets up the conditioms, then calls the googleTranslateElementInit() function.  
function cms_translate(){
  if (typeof ok2edit!='undefined' && ok2edit) {
    alert('Translation plugin is disabled when an editor is logged-in. \nThis is to prevent translated content from being inadvertently saved over the page original.  ');
    return;
  }
  
  if (!document.getElementsByTagName('body').item(0)) {
    setTimeout(trans_init_delay,500);
    return;
  }
  trans_scope();
  if (!document.getElementById('google_translate_element')) {
    var jdiv = document.createElement('div');
  	jdiv.setAttribute('id','google_translate_element');
  	jdiv.setAttribute('style','display:block;');
 	  jdiv.innerHTML="";
    var html_doc = document.getElementsByTagName('body').item(0);
	  html_doc.appendChild(jdiv);
    // alert(document.getElementById('google_translate_element').innerHTML)
  }

  var is_init = trans_include_once('//translate.google.com/translate_a/element.js?cb=googleTranslateElementInit')
  if (!is_init) {setTimeout(trans_clicker,100)}
}

// This is the callback function which is activated once the Google code has been loaded.. 
function googleTranslateElementInit() {
  new google.translate.TranslateElement({pageLanguage: 'en', layout: google.translate.TranslateElement.InlineLayout.SIMPLE, autoDisplay: false}, 'google_translate_element');
  // These two lines are added to autmatically open the language chooser (Not sure why they don't do that anyway) 
  var googtrans=trans_getCookie('googtrans');
  if (googtrans.length==0){setTimeout(trans_clicker,500)}
}

function trans_clicker() {
  var oe =document.getElementById('google_translate_element')
  var ie=oe.firstChild;
  var se=ie.firstChild;
  trans_eventFire(se,'click');
}

// Could be lib functions, maybe transfer to system.js at some point...

function trans_eventFire(el, etype){
  if (el.fireEvent) {
    el.fireEvent('on' + etype);
  } else {
    var evObj = document.createEvent('Events');
    evObj.initEvent(etype, true, false);
    el.dispatchEvent(evObj);
  }
}

 function trans_include_dom(script_filename) {
     var html_doc = document.getElementsByTagName('body').item(0);
     var js = document.createElement('script');
     js.setAttribute('language', 'javascript');
     js.setAttribute('type', 'text/javascript');
     js.setAttribute('src', script_filename);
     html_doc.appendChild(js);
     return false;
 }

 var included_files = new Array();

 function trans_include_once(script_filename) {
     if (!trans_in_array(script_filename, included_files)) {
         included_files[included_files.length] = script_filename;
         trans_include_dom(script_filename);
         return true;
     }
 return false;
 }

 function trans_in_array(needle, haystack) {
     for (var i = 0; i < haystack.length; i++) {
         if (haystack[i] == needle) {
             return true;
         }
     }
     return false;
 }


function trans_getCookie(cname) {
    var name = cname + "=";
    var decodedCookie = decodeURIComponent(document.cookie);
    var ca = decodedCookie.split(';');
    for(var i = 0; i <ca.length; i++) {
        var c = ca[i];
        while (c.charAt(0) == ' ') {
            c = c.substring(1);
        }
        if (c.indexOf(name) == 0) {
            return c.substring(name.length, c.length);
        }
    }
    return "";
} 

var transQueryString = function () {
  // This function is anonymous, is executed immediately and 
  // the return value is assigned to QueryString!
  var query_string = {};
  var query = window.location.search.substring(1);
  var vars = query.split("&");
  for (var i=0;i<vars.length;i++) {
    var pair = vars[i].split("=");
        // If first entry with this name
    if (typeof query_string[pair[0]] === "undefined") {
      query_string[pair[0]] = decodeURIComponent(pair[1]);
        // If second entry with this name
    } else if (typeof query_string[pair[0]] === "string") {
      var arr = [ query_string[pair[0]],decodeURIComponent(pair[1]) ];
      query_string[pair[0]] = arr;
        // If third or later entry with this name
    } else {
      query_string[pair[0]].push(decodeURIComponent(pair[1]));
    }
  } 
  return query_string;
}();