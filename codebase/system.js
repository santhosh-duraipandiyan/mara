
function cms_deferredjs(){
 setTimeout(cms_autocaptioner,500);
} 
 
 // Prevent drops outside of editing area:
 cms_stopdrop();


// Prevent erroneous drop or paste actions causing problems..
function cms_stopdrop(){
 window.addEventListener("dragover",function(e){
  e = e || event;
  e.preventDefault();
 },false);
 window.addEventListener("drop",function(e){
  e = e || event;
  e.preventDefault();
 },false);
}

function pic_url(thisid) {
  var picURL=thisid.title;
  if (picURL.length==0) {var picURL = thisid.alt;}
  var qzParams="titlebar=0, resizable=1, scrollbars=1";   
  var qzWindow = window.open(picURL, "" ,qzParams);
  qzWindow.focus();
}


function cms_autocaptioner(){

if (typeof ok2edit != 'undefined') {
 if (ok2edit) {return}
}

var ixl=document.images.length;
 for (ct=0;ct<ixl;ct++){
   var i=document.images[ct];
   var ic=i.className;
   // Allows legacy 'caption' tag. Change to 'autocaption' if clashes arise.  
   if(ic.indexOf('caption')>-1){
     i.style.position='relative';
     var it=i.title;
     if (i.alt != ''){it=i.alt}
     if (it.length==0) {continue;}
     it=it.replace(/  /g,'<br>');
     // allow for legacy 'caption' class:
     if (ic.indexOf('autocaption')<0) {ic=ic.replace('caption','autocaption') }
     i.className=ic;
     var spanclass=i.className;
     if (ic.indexOf('autocaption-')<0) {
       if (i.style.width=='') {i.style.width=i.width + 'px';} // Deal with situation where only height is specified.
       // Caption below image :
       var is=i.getAttribute('style');
       // Border and height stay with image.. 
       is=is.replace(/border/gi,'data-border')
       is=is.replace(/height/gi,'data-height')
       // Always transfer width...
       i.style.width='100%';
       i.style.margin='0px'; 
       var capspan = '<span class="'+ spanclass +'" style="' + is + '" >' + i.outerHTML + it + '</span>';
       i.outerHTML = capspan;
     } else {
       // Caption as overlay :
       var capspan = '<span class="'+ spanclass +'"><span class="autocaption-text">'+ it +'</span></span>';
       i.outerHTML=i.outerHTML + capspan ;
     }
   }
 }
 cms_aligncaptions();
 window.onresize = function(){cms_aligncaptions()};
}

function cms_aligncaptions(){
var ixl=document.images.length;
  for (ct=0;ct<ixl;ct++){
    var i=document.images[ct];
    var ic=i.className;
    if(ic.indexOf('caption')>-1){
      var s=i.nextSibling;
      if (!s.className){continue;}
      if (s.className.indexOf('autocaption-')<0){continue;}
      var stc=s.firstChild;
      var stcl=stc.innerHTML.length;
      // caption overlays need positioning: 

      var istyle = i.currentStyle || window.getComputedStyle(i);
      // var lmargin = parseFloat(istyle.marginLeft);
      var lborder = parseFloat(istyle.borderLeftWidth);
      // var tmargin = parseFloat(istyle.marginTop);
      var tborder = parseFloat(istyle.borderTopWidth);
      var lpos=i.offsetLeft + lborder ;
      var tpos=i.offsetTop + tborder ;

      s.style.top=tpos + 'px';
      s.style.left=lpos + 'px';
      s.style.maxHeight=i.height + 'px';
      s.style.maxWidth=i.width +'px';
      s.style.height=i.height + 'px';
      s.style.width=i.width +'px';
      s.style.opacity=1;
      stc.style.fontSize = 'inherit'; 
      // Resize font if caption is too big: 
      var fill = stc.clientHeight/s.clientHeight;
      var maxfill=.5; // half height of image ;
      if ( fill > maxfill ){ 
         var stcstyle = window.getComputedStyle(stc, null); 
         if (typeof stcstyle!='undefined'){ 
           var stcfs = stcstyle.getPropertyValue("font-size");
           stcfs=stcfs.substr(0,stcfs.length-2);
           stcfs=Math.floor(stcfs/(fill/maxfill));
           if (stcfs<12){stcfs=12}
           stc.style.fontSize = stcfs + 'px'; 
         }
      }
    }
  }
}


function cms_oldautocaptioner(){
// No longer used, included for reference only. 
if (typeof ok2edit != 'undefined') {
 if (ok2edit) {return}
}

var ixl=document.images.length
 for (ct=0;ct<ixl;ct++){
   var i=document.images[ct];
   var ic=i.className;
  // Allows legacy 'caption' tag. Change to 'autocaption' if clashes arise.  
  if(ic.indexOf('caption')>-1){
   var it=i.title;
   if (i.alt != ''){it=i.alt}
   var iclick='onClick='+i.getAttribute('onClick');
   
   var ispan =  '<img src="' + i.src + '" alt="' + i.alt + '" ' + iclick + ' style="width:100% " />';
   var capspan = '<span class="autocaption-text"><span>'+ i.alt.replace(/  /g,'<br>') +'</span></span>';
  
   i.removeAttribute('alt');  
   // allow for legacy 'caption' class:
   if (ic.indexOf('autocaption')<0) {i.className=ic.replace('caption','autocaption') }
   i.removeAttribute('src');  
   var outerspan='<span ' + i.outerHTML.substr(4);
   if (outerspan.substr(-1,2)=='/>') {
     outerspan = outerspan.substr(0,outerspan.length-3) +'>';
   }
   
   if(ic.indexOf('caption-top')>-1){
     if (it.length>80){i.style.fontSize='small'}
     var nhtml=outerspan;
     nhtml+=capspan;
     nhtml+=ispan;
     nhtml+='</span>'
     i.outerHTML=nhtml;
   } else {
     var nhtml=outerspan;
     nhtml+=ispan;
     nhtml+=capspan;
     nhtml+='</span>'
     i.outerHTML=nhtml;    
   }
  }
 }
}



function cms_imgzoom_popup(img_url){

  if (typeof cms_imgzoom_layerCreated == 'undefined'){
    var zzdiv= '<div id="cms_imgzoom_zdiv" ><img  src="" onClick="return null" id="cms_imgzoom_zimage" alt="" title="" border=0></div>';
    var cms_imgzoomlayer=document.createElement('cms_imgzoomlayer');
    cms_imgzoomlayer.innerHTML=zzdiv;
    document.body.insertBefore(cms_imgzoomlayer, null);
    cms_imgzoom_layerCreated=true;
  }

  var imgchange=true;
  var thissrc=img_url;
  // allow for single-click image switch...
  if (typeof cms_prevzimgsrc!='undefined'){
   if (cms_prevzimgsrc==thissrc){imgchange=false}
  }
  cms_prevzimgsrc=thissrc;
  var zdiv = document.getElementById('cms_imgzoom_zdiv'); 
  var zimgtag='<img src="'+thissrc+'" name="" alt="" id="cms_imgzoom_zimg" onClick="cms_imgzoom_unzoom()" onLoad="cms_imgzoom_showzoom('+imgchange+')">';
  zdiv.innerHTML=zimgtag;
  var zdiv = document.getElementById('cms_imgzoom_zdiv'); 
  var zimage= document.getElementById('cms_imgzoom_zimg'); 

  // Get viewport width/height in various browsers...
  var vpWidth = 0;
  if( typeof( window.innerWidth ) == 'number' ) {
    //Non-IE
    vpWidth = window.innerWidth;
  } else if( document.documentElement && document.documentElement.clientWidth ) {
    //IE 6+ in 'standards compliant mode'
    vpWidth = document.documentElement.clientWidth;
  } else if( document.body && document.body.clientWidth ) {
    //IE 4 compatible
    vpWidth = document.body.clientWidth;
  }
  var vpHeight = 600;
  if( typeof( window.innerHeight ) == 'number' ) {
    //Non-IE
    vpHeight = window.innerHeight;
  } else if( document.documentElement && document.documentElement.clientHeight ) {
    //IE 6+ in 'standards compliant mode'
    vpHeight = document.documentElement.clientHeight;
  } else if( document.body && document.body.clientHeight ) {
    //IE 4 compatible
    vpHeight = document.body.clientHeight;
  }

  // Detect oversize image and scale down to fit screen
  var rawwidth=zimage.width; 
  var rawheight=zimage.height;
  if( typeof(zimage.naturalWidth ) == 'number' ) { rawwidth=zimage.naturalWidth}
  if( typeof(zimage.naturalHeight ) == 'number' ) { rawheight=zimage.naturalHeight}
    var zwratio=rawwidth/(vpWidth-60);
    var zhratio=rawheight/(vpHeight-60);
    var scaledwidth=rawwidth;
    var scaledheight=rawheight;
    if (zwratio>1 || zhratio>1 ){
      var scaling=zwratio;
      if (zhratio>scaling) {scaling=zhratio}
       scaledwidth=(rawwidth/scaling).toFixed(0);
       scaledheight=(rawheight/scaling).toFixed(0);
       zimage.width=scaledwidth;
       zimage.height=scaledheight;
    }
    var ztop=((vpHeight-scaledheight)/2).toFixed(0)-25;
    var zleft=((vpWidth-scaledwidth)/2).toFixed(0)-25;
   
    zdiv.style.top=ztop + "px" ; 
    zdiv.style.left=zleft + "px";
    zimage.width=scaledwidth;
    zimage.height=scaledheight;
  return false;
 
 }
 
function cms_imgzoom_showzoom(imgchange){
    var zdiv = document.getElementById('cms_imgzoom_zdiv'); 

  if (zdiv.style.visibility!='visible' || imgchange==true){
    zdiv.style.visibility = 'visible';
    zdiv.style.opacity=1;
  }else{
    zdiv.style.visibility = 'hidden';
  }
    return false;
}

 function cms_imgzoom_unzoom(){
      var zdiv = document.getElementById('cms_imgzoom_zdiv'); 
      zdiv.style.visibility = 'hidden';
 }


function oldcms_img_popup(thisimgurl){
  var qzURL=thisimgurl;
  var qzParams="titlebar=0, resizable=1, scrollbars=1";   
  var qzWindow = window.open(qzURL, "" ,qzParams);
  qzWindow.focus();
  qzWindow.onBlur=self.Close();
}

function m_showtxt(){
 m_splashimg.style.position = 'absolute';
 m_splashimg.style.left = '-4000px';
 m_splashtxt.style.position = 'relative';
 m_splashtxt.style.left = '0px';
}

function m_showimg(){
 m_splashtxt.style.position = 'absolute';
 m_splashtxt.style.left = '-4000px';
 m_splashimg.style.position = 'relative';
 m_splashimg.style.left = '0px';
}




// ------------------------------

function isset(varname) {
    return typeof varname !== 'undefined';
}

// Show/hide themes menu... 

function showThemeSelector(action){
 var fs=document.getElementById('themebutton');
 var fsl=document.getElementById('thememenu');
 if (fsl.style.display != 'inline'){
   fsl.style.display = 'inline';
   fslrect = fsl.getBoundingClientRect();
   if (fslrect.left<100) {fsl.style.left='50px';}
   fs.title='Close theme list, no change';
 }else{
   fsl.style.display='none';
   fs.title='Show available styling themes';
 }
}


// ************ Zoom image layer


function buildZoom(s_imageID) {
 if (s_image=document.getElementById(s_imageID)) {
   document.write('<div class="zoombox" id="div_' + s_imageID + '" onClick="javascript:setVisible(\'' + s_imageID + '\')">');
   document.write ('<img src="' + s_image.src + '" id="z_image" height=100% width=100% alt="' + s_image.alt + '" title="Click to close zoom view" border=0></div>');
 }
}




// *****************************************************

// Slide-in item, (c) IWR Consultancy.

function showdiv( elemID )
{
    var elem = document.getElementById( elemID );
    if( elem.style.position != 'absolute' )
    {
        elem.style.position = 'absolute';
        elem.style.left = '-4000px';
    }
    else
    {
        elem.style.position = 'relative';
        elem.style.left = '0px';
    }
}


function htmlentities(str) {
    return String(str).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
}


/* A JavaScript implementation of the Secure Hash Algorithm, SHA-256
 * Version 0.3 Copyright Angel Marin 2003-2004 - http://anmar.eu.org/
 * Distributed under the BSD License
 * Some bits taken from Paul Johnston's SHA-1 implementation
 */
var chrsz = 8;  /* bits per input character. 8 - ASCII; 16 - Unicode  */
function safe_add (x, y) {
  var lsw = (x & 0xFFFF) + (y & 0xFFFF);
  var msw = (x >> 16) + (y >> 16) + (lsw >> 16);
  return (msw << 16) | (lsw & 0xFFFF);
}
function S (X, n) {return ( X >>> n ) | (X << (32 - n));}
function R (X, n) {return ( X >>> n );}
function Ch(x, y, z) {return ((x & y) ^ ((~x) & z));}
function Maj(x, y, z) {return ((x & y) ^ (x & z) ^ (y & z));}
function Sigma0256(x) {return (S(x, 2) ^ S(x, 13) ^ S(x, 22));}
function Sigma1256(x) {return (S(x, 6) ^ S(x, 11) ^ S(x, 25));}
function Gamma0256(x) {return (S(x, 7) ^ S(x, 18) ^ R(x, 3));}
function Gamma1256(x) {return (S(x, 17) ^ S(x, 19) ^ R(x, 10));}
function core_sha256 (m, l) {
    var K = new Array(0x428A2F98,0x71374491,0xB5C0FBCF,0xE9B5DBA5,0x3956C25B,0x59F111F1,0x923F82A4,0xAB1C5ED5,0xD807AA98,0x12835B01,0x243185BE,0x550C7DC3,0x72BE5D74,0x80DEB1FE,0x9BDC06A7,0xC19BF174,0xE49B69C1,0xEFBE4786,0xFC19DC6,0x240CA1CC,0x2DE92C6F,0x4A7484AA,0x5CB0A9DC,0x76F988DA,0x983E5152,0xA831C66D,0xB00327C8,0xBF597FC7,0xC6E00BF3,0xD5A79147,0x6CA6351,0x14292967,0x27B70A85,0x2E1B2138,0x4D2C6DFC,0x53380D13,0x650A7354,0x766A0ABB,0x81C2C92E,0x92722C85,0xA2BFE8A1,0xA81A664B,0xC24B8B70,0xC76C51A3,0xD192E819,0xD6990624,0xF40E3585,0x106AA070,0x19A4C116,0x1E376C08,0x2748774C,0x34B0BCB5,0x391C0CB3,0x4ED8AA4A,0x5B9CCA4F,0x682E6FF3,0x748F82EE,0x78A5636F,0x84C87814,0x8CC70208,0x90BEFFFA,0xA4506CEB,0xBEF9A3F7,0xC67178F2);
    var HASH = new Array(0x6A09E667, 0xBB67AE85, 0x3C6EF372, 0xA54FF53A, 0x510E527F, 0x9B05688C, 0x1F83D9AB, 0x5BE0CD19);
    var W = new Array(64);
    var a, b, c, d, e, f, g, h, i, j;
    var T1, T2;
    /* append padding */
    m[l >> 5] |= 0x80 << (24 - l % 32);
    m[((l + 64 >> 9) << 4) + 15] = l;
    for ( var i = 0; i<m.length; i+=16 ) {
        a = HASH[0]; b = HASH[1]; c = HASH[2]; d = HASH[3]; e = HASH[4]; f = HASH[5]; g = HASH[6]; h = HASH[7];
        for ( var j = 0; j<64; j++) {
            if (j < 16) W[j] = m[j + i];
            else W[j] = safe_add(safe_add(safe_add(Gamma1256(W[j - 2]), W[j - 7]), Gamma0256(W[j - 15])), W[j - 16]);
            T1 = safe_add(safe_add(safe_add(safe_add(h, Sigma1256(e)), Ch(e, f, g)), K[j]), W[j]);
            T2 = safe_add(Sigma0256(a), Maj(a, b, c));
            h = g; g = f; f = e; e = safe_add(d, T1); d = c; c = b; b = a; a = safe_add(T1, T2);
        }
        HASH[0] = safe_add(a, HASH[0]); HASH[1] = safe_add(b, HASH[1]); HASH[2] = safe_add(c, HASH[2]); HASH[3] = safe_add(d, HASH[3]); HASH[4] = safe_add(e, HASH[4]); HASH[5] = safe_add(f, HASH[5]); HASH[6] = safe_add(g, HASH[6]); HASH[7] = safe_add(h, HASH[7]);
    }
    return HASH;
}

function str2binb (str) {
  var bin = Array();
  var mask = (1 << chrsz) - 1;
  for(var i = 0; i < str.length * chrsz; i += chrsz)
    bin[i>>5] |= (str.charCodeAt(i / chrsz) & mask) << (24 - i%32);
  return bin;
}
function binb2hex (binarray) {
  var hexcase = 0; /* hex output format. 0 - lowercase; 1 - uppercase */
  var hex_tab = hexcase ? "0123456789ABCDEF" : "0123456789abcdef";
  var str = "";
  for (var i = 0; i < binarray.length * 4; i++) {
    str += hex_tab.charAt((binarray[i>>2] >> ((3 - i%4)*8+4)) & 0xF) + hex_tab.charAt((binarray[i>>2] >> ((3 - i%4)*8  )) & 0xF);
  }
  return str;
}
function sha256(s){return binb2hex(core_sha256(str2binb(s),s.length * chrsz));}

function FileExists(fileURL) {
    var thisfile = new XMLHttpRequest();
    thisfile.open('HEAD', fileURL, false);
    thisfile.send();
    if (thisfile.status == "404") {
        return false;
    } else {
        return true;
    }
}

// 7.0 additions.. 

function getExtension(filename){
 var a = filename.split(".");
 if( a.length === 1 || ( a[0] === "" && a.length === 2 ) ) {
     return "";
 }
 return a.pop().toLowerCase();  
}

function getMimetype(ext) {
  switch (ext.toLowerCase()) {  
   case 'jpg': return 'image/jpeg';
   case 'jpeg': return 'image/jpeg';
   case 'png': return 'image/png';
   case 'gif': return 'image/gif';
   case 'bmp': return 'image/bmp';
   case 'svg': return 'image/svg+xml';
   
   case 'txt': return 'text/plain';
   case 'js': return  'text/javascript';
   case 'css': return  'text/css';

   case 'mp3': return 'audio/mp3';
   case 'mp4': return 'video/mp4';
   case 'webm': return 'video/webm';

   case 'zip': return 'application/x-compressed';
   case 'doc': return 'application/msword';
   case 'xls': return 'application/excel';
   default: return 'application/octet-stream';
  }
}


function cms_getcss(elem){
    cssObj = window.getComputedStyle(elem, null)
    var txt=''
    for (i = 0; i < cssObj.length; i++) { 
        cssObjProp = cssObj.item(i)
        txt += cssObjProp + " = " + cssObj.getPropertyValue(cssObjProp) + "\n";
    }
    alert(txt);
}



function basename(str)
{
   var base = new String(str).substring(str.lastIndexOf('/') + 1); 
    if(base.lastIndexOf(".") != -1)       
        base = base.substring(0, base.lastIndexOf("."));
   return base;
}

function getExtension(filename){
 var a = filename.split(".");
 if( a.length === 1 || ( a[0] === "" && a.length === 2 ) ) {
     return "";
 }
 return a.pop().toLowerCase();  
}

function RandomInt(min, max) {
    return Math.floor(Math.random() * (max - min + 1)) + min;
}

function cms_popup_autoclose() {
 // Closes popup if it remains defocused for >30s.
 if (typeof window.staleness=='undefined'){
   window.staleness=0; 
  } else {
   if (document.hasFocus()) {
     window.staleness=0;
   } else {
     window.staleness++;
     if (window.staleness > 6){
       window.staleness = 0;
       window.close();
     }  
   }
 }
 window.setTimeout(function(){cms_popup_autoclose()}, 10000 ); 
 return;
}