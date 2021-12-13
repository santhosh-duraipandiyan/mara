
 function ckinline(activated, cktoolbar) {
  if (typeof cktoolbar=='undefined'){
    var cktoolbar='Mara'
    var vpWidth = 800;
    // Use smaller toolbar on narrow screens.. 
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
    if (vpWidth < 600){cktoolbar='Mobile'}
  }
  SaveReminder('off');
  if ("undefined"==typeof CKEDITOR){
   ok2edit=false;
   var ckdiv=document.getElementById('editable');
   ckdiv.contentEditable=false;
   return;
  }
  CKEDITOR.disableAutoInline = true;
  var ckdiv=document.getElementById('editable');
    if (ckdiv.contentEditable) {
      if(typeof CKEDITOR.instances.editable != 'undefined') {
        CKEDITOR.instances.editable.destroy();
      }
   	  ckdiv.contentEditable=false;
    }
  ok2edit=false;
  if (!activated) {
    return;
  }
  ckdiv.contentEditable=true;
  
if (doctype.indexOf("HTML4")<0) {
//  alert(doctype);
  var editor = CKEDITOR.inline( 'editable' , {
    toolbar:cktoolbar,
    // replace standard config with mara-specific definition.
    customConfig : '../ck_config.js',
    // Extra plugins are now loaded from ck_config.js in codebase.
	   
	contentsCss: 'body {color:#000; background-color#:FFF;}',
    docType: '<!DOCTYPE HTML>'

  });

} else {
//  alert(doctype);

  var editor = CKEDITOR.inline( 'editable' , {
    toolbar:cktoolbar,
    // replace standard config with mara-specific definition.
    customConfig : '../ck_config.js',

    // Extra plugins are now loaded from ck_config.js in codebase.
    // extraPlugins: 'htmlwriter,mara',
    // filebrowserBrowseUrl : window.codedir + 'dir.php?type=files',
    // filebrowserImageBrowseUrl : window.codedir + 'dir.php?type=images',
    // filebrowserImageBrowseLinkUrl : window.codedir + 'dir.php?type=files',
    // filebrowserUploadUrl : '',
    // filebrowserImageUploadUrl :'',
    // filebrowserWindowWidth : '800',
    // filebrowserWindowHeight : '650',
        
	contentsCss: 'body {color:#000; background-color#:FFF;}',
	docType: '<!DOCTYPE HTML>',

	/*
	 * Core styles, to use conventional HTML4 tags instead of HTML5.
	 */
	coreStyles_bold: { element: 'b' },
	coreStyles_italic: { element: 'i' },
	coreStyles_underline: { element: 'u' },
	coreStyles_strike: { element: 'strike' },

	/*
	 * Font face.
	 */

	// Define the way font elements will be applied to the document.
	// The "font" element will be used.
  // Note that in Mara we disallow the filling of pages with font tags regardless of doctype, so this has no effect. 
	font_style: {
		element: 'font',
		attributes: { 'face': '#(family)' }
	},

	/*
	 * Font sizes.
	 */
	fontSize_sizes: 'xx-small/1;x-small/2;small/3;medium/4;large/5;x-large/6;xx-large/7',
	fontSize_style: {
		element: 'font',
		attributes: { 'size': '#(size)' }
	} ,

	/*
	 * Font colors.
	 */
	colorButton_enableMore: true,

	colorButton_foreStyle: {
		element: 'font',
		attributes: { 'color': '#(color)' }
	},

	colorButton_backStyle: {
		element: 'font',
		styles: { 'background-color': '#(color)' }
	},

	/*
	 * Styles combo.
	 */
	stylesSet: [
		{ name: 'Computer Code', element: 'code' },
		{ name: 'Keyboard Phrase', element: 'kbd' },
		{ name: 'Sample Text', element: 'samp' },
		{ name: 'Variable', element: 'var' },
		{ name: 'Deleted Text', element: 'del' },
		{ name: 'Inserted Text', element: 'ins' },
		{ name: 'Cited Work', element: 'cite' },
		{ name: 'Inline Quotation', element: 'q' }
	],

	on: { 'instanceReady': configureHtmlOutput }
});

}

/*
 * Adjust the behavior of the dataProcessor to avoid styles
 * and make it look like FCKeditor HTML output.
 */
function configureHtmlOutput( ev ) {
	var editor = ev.editor,
		dataProcessor = editor.dataProcessor,
		htmlFilter = dataProcessor && dataProcessor.htmlFilter;

    // Change indent to spaces
    dataProcessor.writer.indentationChars = '  '; 

	// Out self closing tags the HTML4 way, like <br>.
	dataProcessor.writer.selfClosingEnd = '>';

	// Make output formatting behave similar to FCKeditor
	var dtd = CKEDITOR.dtd;
	for ( var e in CKEDITOR.tools.extend( {}, dtd.$nonBodyContent, dtd.$block, dtd.$listItem, dtd.$tableContent ) ) {
		dataProcessor.writer.setRules( e, {
			indent: true,
			breakBeforeOpen: true,
			breakAfterOpen: false,
			breakBeforeClose: !dtd[ e ][ '#' ],
			breakAfterClose: true
		});
	}

	// Output properties as attributes, not styles.
	htmlFilter.addRules( {
		elements: {
			$: function( element ) {
				// Output dimensions of images as width and height
				if ( element.name == 'img' ) {
					var style = element.attributes.style;

					if ( style ) {
						// Get the width from the style.
						var match = ( /(?:^|\s)width\s*:\s*(\d+)px/i ).exec( style ),
							width = match && match[ 1 ];

						// Get the height from the style.
						match = ( /(?:^|\s)height\s*:\s*(\d+)px/i ).exec( style );
						var height = match && match[ 1 ];

						if ( width ) {
							element.attributes.style = element.attributes.style.replace( /(?:^|\s)width\s*:\s*(\d+)px;?/i , '' );
							element.attributes.width = width;
						}

						if ( height ) {
							element.attributes.style = element.attributes.style.replace( /(?:^|\s)height\s*:\s*(\d+)px;?/i , '' );
							element.attributes.height = height;
						}
					}
				}

				// Output alignment of paragraphs using align
				if ( element.name == 'p' ) {
					style = element.attributes.style;

					if ( style ) {
						// Get the align from the style.
						match = ( /(?:^|\s)text-align\s*:\s*(\w*);/i ).exec( style );
						var align = match && match[ 1 ];

						if ( align ) {
							element.attributes.style = element.attributes.style.replace( /(?:^|\s)text-align\s*:\s*(\w*);?/i , '' );
							element.attributes.align = align;
						}
					}
				}

				if ( !element.attributes.style )
					delete element.attributes.style;

				return element;
			}
		},

		attributes: {
			style: function( value, element ) {
				// Return #RGB for background and border colors
				return CKEDITOR.tools.convertRgbToHex( value );
			}
		}
	});
}

// Define blob array as will be used for varous upload sources.. 
if (typeof window.uploadlist=='undefined' || window.uploadlist=='') {window.uploadlist={}; window.uploadlist['count']=0;}

ok2edit=true;
ckdiv.focus();
SaveReminder('on');


function add2list(theid,thefile){
  var upload ={};
  upload.id=theid;
  upload.file=thefile;
  window.uploadlist.push(upload);
}

function getahref(indata) {
  indata=indata.toLowerCase();
  var astart=indata.indexOf('<a');
  var subdata=indata.substr(astart);
  var aend=subdata.indexOf('>');
  var subdata=subdata.substr(0,aend+1);
  return subdata;  
}

function register_videobox() {
  if (typeof register_videobox.registered == 'undefined'){
       register_videobox.registered = true;
       editor.widgets.add( 'videobox', {
       upcast: function( element ) {
        // Defines which elements will become widgets.
        if ( element.hasClass( 'videobox' ) )
            return true;
        },
          init: function() {
            // ...
        }
       } );
   }
}

CKEDITOR.instances.editable.on( 'paste', function( evt ) {
 
 // evt.cancelBubble=true; 
  // We only concern ourselves with external transfers.. 
  if (evt.data.dataTransfer.getTransferType()==2){ return;}
 
 if (user_privelege<2){
   ckumsg("Please note: You do not have sufficient rights to upload dropped material.","warning") 
   evt.stop();
   return;
 }

 /*
 if (typeof window.ddp_busy=='undefined'){window.ddp_busy=false;}
 if (window.ddp_busy==true){
   evt.data='';
   evt.stop();
   evt.cancel();
   setTimeout(function(){window.ddp_busy=false;},5000);
   return;
 }
 */
 if (evt.data.dataValue.length>20000000) {ckumsg('Too much data for paste operation. Please resize your image or select a smaller area','warning');evt.stop;return;}
 
 var thislink = getahref(evt.data.dataValue)

 if (typeof window.uploadlist=='undefined') window.uploadlist={};
 var wul=window.uploadlist;
 var datatype='';

// Note that most drop actions are handled as files because that's the way the browser sees them... 
if (evt.data.dataTransfer.getFilesCount()>0) {
   processfiles(evt);
   evt.stop();
   return;
   
   // Need to establish type from filename as no mimetype sent. 
   // Note: item can still be text.
   // Some files may be non image types, add handlers for these in due course. 
   // For the moment, limit to img extensions.  

} else {

   var startmime=evt.data.dataValue.indexOf('src="data:')+10;
   var endmime=evt.data.dataValue.indexOf(';',startmime);
   var mimetype=evt.data.dataValue.substring(startmime,endmime);
   // var notification2 = editor.showNotification( 'Mimetype:' + mimetype , 'info' );


// If the data is a URL and it's an absolute path to a local file, correct this. 

if (evt.data.method=='drop'){

// YouTube dropped link results in embedded video.. 
 if (thislink.indexOf('www.youtube.com/watch')>0){
  register_videobox();
  var initytlink='<span id=yttemp>'+evt.data.dataValue+'</span>'
    CKEDITOR.instances.editable.insertHtml(initytlink);
  var thishref=document.getElementById('yttemp').firstChild.href;
  var thiseref=thishref.substr(6);
  var thiseref=thiseref.replace('/watch?v=', /embed/);
  var ytlink='<p class="videobox"><iframe allowfullscreen="true" allowscriptaccess="always" frameborder="10" height="349" scrolling="no" src="'+thiseref +'" width="425"></iframe></p>'
  document.getElementById('yttemp').outerHTML=ytlink;
  evt.stop();
  return;
 }

// No specific action when it's an internal drag and drop action, UNLESS it's an absolute URL in which case it needs fixing.. 


 if (evt.data.dataValue.substr(0,4)=="<img") {
  // Deal with absolute URLs in images dropped from file manager.. 
  var pagelocation=location.origin + pagedir
  // Don't filter relative URLs or blobs, they are same-page drop events..
  if (evt.data.dataValue.indexOf(pagelocation)==-1) {return}
  if (evt.data.dataValue.indexOf(' src="blob:http')>0) {return}
  var initqilink='<span id=cms_ddqitemp>'+evt.data.dataValue+'</span>'
  CKEDITOR.instances.editable.insertHtml(initqilink);
  var thisimg=document.getElementById('cms_ddqitemp').firstChild;
  var orightml=thisimg.outerHTML;
  var thissrc=thisimg.src;
  // (allows for default (index.htm) files whereas href does not)
  var absurl=false;
  // Fix absolute URLs..
  if (thissrc.substr(0, pagelocation.length)==pagelocation){
    absurl=true; 
    thisimg.src=thissrc.substr(pagelocation.length);
    thisimg.setAttribute('data-cke-saved-src',thissrc.substr(pagelocation.length));
  // Clean up additional attributes which probably should not be copied from a non-local image.. 
    thisimg.removeAttribute('name');
    thisimg.removeAttribute('id');
    thisimg.removeAttribute('title');
    thisimg.removeAttribute('style');
    thisimg.removeAttribute('width');
    thisimg.removeAttribute('height');
    thisimg.setAttribute('alt','');
    thisimg.setAttribute('class',upload_align);
  }
  document.getElementById('cms_ddqitemp').outerHTML=thisimg.outerHTML;
  evt.stop();
  return;
 } // img filter

 if (evt.data.dataValue.substr(0,3)=="<a ") {
  // Requirements differ from images! 
  // Deal with absolute URLs in links dropped from browser.. 
  var initqilink='<span id=cms_ddqitemp>'+evt.data.dataValue+'</span>'
  CKEDITOR.instances.editable.insertHtml(initqilink);
  var thislink=document.getElementById('cms_ddqitemp').firstChild;
  var orightml=thislink.outerHTML;
  var thishref=thislink.href;
  var pagelocation=location.origin + pagedir
  // (allows for default (index.htm) files whereas href does not)
  var absurl=false;
  // Fix absolute URLs..
 
  if (thishref.substr(0, pagelocation.length)==pagelocation){
    absurl=true;
    thislink.href=thishref.substr(pagelocation.length);
    thislink.setAttribute('data-cke-saved-href',thishref.substr(pagelocation.length));
  // Clean up additional attributes which probably should not be copied from a non-local image.. 
    thislink.removeAttribute('name');
    thislink.removeAttribute('id');
    thislink.removeAttribute('title');
    // thislink.removeAttribute('style');
    // thislink.removeAttribute('class');
  }
  if (thislink.href.toLowerCase().indexOf('javascript:')==0) {
    ckumsg('Note: This is a script link, and may not work outside of its original context.','warning'); 
  }
  document.getElementById('cms_ddqitemp').outerHTML=' ' + thislink.outerHTML + ' ';
  evt.stop();
  return;
 } // anchor filter


/*
 // Deal with absolute URLs in locally dropped links.. 
 if (evt.data.dataValue.substr(0,9)=='<a href="') {
   var thislink=evt.data.dataValue;
   alert(thislink.toLowerCase().indexOf('<a href="javascript:'))
   if (thislink.toLowerCase().indexOf('href="javascript:')>0) {
      ckumsg('This is a script link, and may not work outside of its original context.','warning'); 
   }
   var pagelocation=location.origin + pagedir;
   if (thislink.indexOf(pagelocation)==9){
     evt.data.dataValue=thislink.replace(pagelocation,'');
   }
   return;
 }
*/

} // end data drop section 

// If it's a paste we need to determine what kind it is.. 

 if (evt.data.method=='paste'){
  // if plaintext, just insert into document. 
 }
}

  var isimgdataurl=false;
  if (evt.data.dataValue.indexOf('src="data:image')==0){
    isimgdataurl=true;
  //  var notification2 = editor.showNotification( 'data url involved', 'info' );
  }
  // var notification2 = editor.showNotification( evt.data.dataTransfer.getFilesCount() + ' files involved', 'info' );
  if (evt.data.method=='drop' && evt.data.dataTransfer.getFilesCount()==0 ){return}
 // Other events are a data paste from a selection so handle them here.. 

    var randomID=RandomInt(1,999999).toString();
    var thisid = 'pending-upload-' + randomID ;
    // On pastes we don't get a file extension but we do get a mimetype so we convert.. 
    var thisext='jpg';  
    var isimg=false;
    // We should really tidy this up so that all mimetypes are handled in system.js ***
    if (mimetype.indexOf('image/jpeg')==0) {thisext='jpg'; isimg=true;}
    else if (mimetype.indexOf('image/jpg')==0) {thisext='jpg' ; isimg=true;}
    else if (mimetype.indexOf('image/png')==0) {thisext='png' ; isimg=true;}
    else if (mimetype.indexOf('image/gif')==0) {thisext='gif' ; isimg=true;}
    else if (mimetype.indexOf('image/bmp')==0) {thisext='bmp' ; isimg=true;}
    else if (mimetype.indexOf('image/svg+xml')==0) {thisext='svg' ; isimg=true;} // added v7.5
  // We only process pasted images here, other pasted content goes straight to the editor.  
  if (isimg){
    var insertion='<img id="' + thisid + '"' ;
    insertion += ' title="img/'+basename(pagefile)+'_'+'ImageClip' + randomID + '.' + thisext +'"';
    // Allow a predetermined page position for pasted images instead of the (rather useless) HTML default.. 
    insertion += ' class="' + upload_align +' inline"';
    insertion += ' data_destfile="img/'+basename(pagefile)+'_'+'ImageClip' + randomID + '.' + thisext +'"';
    insertion += ' data_overwrite="' + upload_overwrite  + '"' ;
    insertion += ' data_smartsize="' + upload_smartsize  + '"' ; 
    insertion += ' data_mimetype="'  + mimetype  + '"' ;
  
    //var pastedata = insertion + evt.data.dataValue.substr(4)
    
    // Better method, overcomes race hazard... 
    var startdat=evt.data.dataValue.indexOf('src="data:')+5;
    var enddat=evt.data.dataValue.indexOf('"',startdat);
    var thisblob=data2blob(evt.data.dataValue.substring(startdat,enddat));
  
    window.uploadlist[thisid]=thisblob;
    window.uploadlist.count++;
//  Convert pasted image selection to blob, to avoid browser memory issues. 
    var thisbloburl=URL.createObjectURL(thisblob);
    insertion += ' src="'  + thisbloburl  + '" >' ;
    CKEDITOR.instances.editable.insertHtml(insertion);

    if (ctyimg=document.getElementById(thisid)){ cms_jsresize(ctyimg);}

    evt.stop();
    evt.cancel();
    return;
   } 
   
} );


 function processfiles(evt) { 

   if (typeof window.uploadlist=='undefined' || window.uploadlist=='') {window.uploadlist={}; window.uploadlist['count']=0;}
   if (typeof evt!='undefined') { 
      processfiles.evt=evt; 
      processfiles.ct=0;
      processfiles.numfiles=evt.data.dataTransfer.getFilesCount();
   } else {
      processfiles.ct++;
      if (processfiles.ct>=processfiles.numfiles){
        if (processfiles.numfiles>0){
          // setTimeout(function(){delay_uploader();},5000); // no longer used    
        }
        processfiles.numfiles=0;
        processfiles.filect=0;
        processfiles.evt='';
        return;
      }
   }

   var thisfilename=processfiles.evt.data.dataTransfer.getFile(processfiles.ct).name; 
   var thisfile=processfiles.evt.data.dataTransfer.getFile(processfiles.ct);

   if (thisfile.size==0){
   // A zero length file is likely a directory. In which case we do nothing. 
      ckumsg('Folder or null file "' + thisfilename + '" not processed.','warning' );
      setTimeout(processfiles,1000);
      return;          
   }

   var thissrc = window.URL.createObjectURL(thisfile);
    var bnpf=basename(pagefile);
    var bnpfl=basename(pagefile).length;
    // Stops pagename being multiply prepended.. 
    if (thisfilename.substr(0,bnpfl)==bnpf) {
      var uploadedname= thisfilename;
    } else {
      var uploadedname=bnpf + '_' + thisfilename;
    }
    
    var mimetype='';
    var filext = getExtension(thisfilename);
    var mimetype=getMimetype(filext);
    var randomID=RandomInt(1,999999).toString();
    var thisid = 'pending-upload-' + randomID ;
    
    if (mimetype.indexOf('image')==0) {     
     // For images, put temporary local URL into page.. 
     var insertion='<img id="' + thisid  + '"' ;
     insertion += ' alt="" title="Pending upload:\nimg/' + uploadedname +'"';
     insertion += ' class="' + upload_align +' inline"';
     insertion += ' data_destfile="img/'+ uploadedname +'"';
     insertion += ' data_overwrite="' + upload_overwrite  + '"' ;
     insertion += ' data_smartsize="' + upload_smartsize  + '"' ; 
     insertion += ' src="' + thissrc +'"'; 
     insertion += ' data_mimetype="' + mimetype  + '"' ; 
     insertion += '  >';
     window.uploadlist[thisid]=thisfile;
     window.uploadlist.count++;
        

    } else if (mimetype.indexOf('video')==0){

     register_videobox();
          
     var insertion='<video controls="" class="'+ upload_align + ' videobox" >';
     insertion += ' <source id="' + thisid  + '"' ;
     insertion += ' title="Pending upload:\nimg/' + uploadedname +'"';
     insertion += ' data_destfile="media/'+ uploadedname +'"';
     insertion += ' data_overwrite="' + upload_overwrite  + '"' ;
     insertion += ' data_smartsize=""' ; 
     insertion += ' src="' + thissrc +'"'; 
     insertion += ' type="' + mimetype  + '"' ; 
     insertion += ' data_mimetype="' + mimetype  + '"' ; 
     insertion += ' >';
     // insertion +='Sorry. Your browser does not support HTML5 video playback.';
     insertion +='</video>'; 
     window.uploadlist[thisid]=thisfile;
     window.uploadlist.count++;
    } else {

     // make link for other file types. 
     var insertion=' <a id="' + thisid  + '"' ;
     insertion += ' class="' +' inline"';
//     insertion += ' class="' + upload_align +' inline"';
     insertion += ' data_destfile="download/'+ uploadedname +'"';
     insertion += ' data_overwrite="' + upload_overwrite  + '"' ;
     insertion += ' href="' + thissrc +'"'; 
     insertion += ' data_mimetype="' + mimetype  + '"' ; 
     insertion += ' >';
     insertion +=  thisfilename + '</a > ';
     window.uploadlist[thisid]=thisfile;
     window.uploadlist.count++;
    }
    
    CKEDITOR.instances.editable.insertHtml(insertion);

    var ctyimg=document.getElementById(thisid)
 
    var resized_ok=cms_jsresize(ctyimg); 

    var insertion='';

    setTimeout(processfiles,1000); // Sets processing interval for dropped files in ms - Playing safe here, you could shorten on a powerful PC. 
 return;
 }
 

function pathname(inpath,levels){
if (typeof levels=='undefined'){var levels=1}
var thispath=rtrim(inpath,'/');
var ct=levels;
 while (ct>0) {
  ct--;
  var lastfs=thispath.lastIndexOf("/");
  thispath=thispath.substr(0,lastfs);  
 }
 if ((inpath.substr(0,1)=='/') && (thispath.substr(0,1)!='/')) thispath= '/' + thispath;
 if (thispath!="" && thispath.substr(-1,1)!="/") thispath = thispath + '/';
 return thispath;  
}

function rtrim(str, chars) {
	chars = chars || "\s";
	return str.replace(new RegExp("[" + chars + "]+$", "g"), "");
}

/*
// now in system
function basename(str)
{
   var base = new String(str).substring(str.lastIndexOf('/') + 1); 
    if(base.lastIndexOf(".") != -1)       
        base = base.substring(0, base.lastIndexOf("."));
   return base;
}
*/

function info(evt,ct){
    msg='';
    msg+= '\n name:' + evt.data.dataTransfer.getFile(ct).name;
    msg+= '\n lm:' + evt.data.dataTransfer.getFile(ct).lastModified;
    msg+= '\n lmd:' + evt.data.dataTransfer.getFile(ct).lastModifiedDate;
    msg+= '\n slice:' + evt.data.dataTransfer.getFile(ct).slice;
    msg+= '\n size:' + evt.data.dataTransfer.getFile(ct).size;
    msg+= '\n type:' + evt.data.dataTransfer.getFile(ct).type;
    alert (msg);
}


} //end wrapper function ********************************************************
	

function SaveReminder(thisaction) {
 if (typeof CKEDITOR.instances.editable != 'object') {return};
 if (thisaction=='on') { 
   cms_last_page_state=false;
   window.onbeforeunload = function(e)
      {
        var message='You have unsaved changes in the page editor.';
          e = e || window.event;
              if (typeof CKEDITOR.instances.editable == 'object') {cms_last_page_state=CKEDITOR.instances.editable.checkDirty()}
              if (cms_last_page_state) {
                // For IE and Firefox
                if (e) {
                   e.returnValue = message;
                }
               // For Safari
               return message;
              }
         }
 } 

 if (thisaction=='off') { 
  // If editor is turned off with unsaved data, store last state of page until editor is turned back on, or page exit.
        cms_last_page_state=CKEDITOR.instances.editable.checkDirty() 
 }
} // end saveReminder 


function cms_jsresize(ctximg) {

// Requires an image object with additional property of data_smartsize
  if (typeof cms_jsresize.loopct=='undefined'){cms_jsresize.loopct=0}
    if (!ctximg.complete){ 
     // More js race-hazard protection...
     cms_jsresize.loopct+=1;
     if (cms_jsresize.loopct<10){ 
       setTimeout(function(){cms_jsresize(ctximg)},500)
     } else {
       cms_jsresize.loopct=0;
     }
     return; 
  }
  cms_jsresize.loopct=0;
  ctximg.cmd='processing';
  var cms_canvas=document.getElementById('cms_canvas')
  if (!cms_canvas) {
    var cms_canvas = document.createElement('canvas');
    cms_canvas.id='cms_canvas';
    cms_canvas.style.display='none';
    document.body.appendChild(cms_canvas);
  }else{
    var cms_canvas=document.getElementById('cms_canvas');
  }
    var type = 'image/jpeg';
    if (ctximg.getAttribute('data_mimetype')!='undefined') {type = ctximg.getAttribute('data_mimetype')}; 
    var quality = 0.92;
   // var smartsize=document.uploader.downsizing.value;
   //   var smartsize="400x400"; // temp test hack
   var smartsize=ctximg.getAttribute('data_smartsize').split('x');
    if (smartsize.length==2) {
      var maxwidth=smartsize[0];
      var maxheight= smartsize[1];
    }
    if (typeof maxheight=='undefined') {var maxheight=10240};
    if (typeof maxwidth=='undefined') {var maxwidth=12800};
    // alert(maxwidth + " "+ maxheight)
    var width=ctximg.naturalWidth; 
    var height=ctximg.naturalHeight;
     // alert (width +"  " + height)
    var wscale=width/maxwidth;
    var hscale=height/maxheight;
    var scaling=1;
    if (wscale>=1.01) {scaling=wscale};
    if (hscale>scaling) {scaling=hscale};
    // alert (wscale + " "+ hscale +" " + scaling)

    if (scaling>1.01) {
        width=Math.round(width/scaling);
        height=Math.round(height/scaling);
        cms_canvas.width=width;
        cms_canvas.height=height;
        var ctx = cms_canvas.getContext("2d");
        ctx.drawImage(ctximg,0,0,cms_canvas.width,cms_canvas.height);
        cms_canvas.toBlob(function(blob){
           var bloburl = URL.createObjectURL(blob);
           if (true){ // add check for upload resizing here. 
            if (typeof window.uploadlist=='object'){
             if (typeof window.uploadlist[ctximg.id]=='object'){
              window.uploadlist[ctximg.id]=blob;  
             }
            }
           } 
           URL.revokeObjectURL(ctximg.src);
           ctximg.src=bloburl;
           ctximg.setAttribute('data-cke-saved-src',bloburl);
           cms_canvas.restore;
           ctximg.resized='yes';
        }, type, quality );
    }else{
        ctximg.resized='no';  
    }
    ctximg.cmd='display';
    return ;
}

// *************************************************************************


function RandomInt(min, max) {
    return Math.floor(Math.random() * (max - min + 1)) + min;
}


function getfirstblob(tag,attr){
    var wu=window.upload;
    var aitems=document.getElementById('editable').getElementsByTagName(tag);
    var ct=0;
 //  alert(tag + ' '+ attr+' ' + aitems.length)
      while (ct<aitems.length) {
        if (typeof aitems[ct].getAttribute(attr) != 'undefined') {
          if (aitems[ct].getAttribute('data_status') != 'error') {
            if (aitems[ct].getAttribute(attr).substr(0,5).toLowerCase()=='blob:') {
               // aitems[ct].setAttribute('data_blob',aitems[ct].getAttribute(attr));
               if (typeof aitems[ct].id == 'undefined'){
                 aitems[ct].setAttribute('id') = RandomInt(1,999999).toString();
               }
               wu.attribute=attr;
               wu.blob=aitems[ct].getAttribute(attr);
               wu.current=aitems[ct];
               wu.id=aitems[ct].id;
               return true;
            }
          }
        }
        ct++; 
     }
     return false ;
}


function cms_uploader(action){
 // these come from cms.. 
 // pagefile='';
 // pagedir='';
 // codedir='';
 // shalevel=2;

 if (user_privelege<2){
   ckumsg("Please note: Embedded content cannot be uploaded since you have insufficient rights.","warning") 
   return;
 }

 if (user_privelege>5){
   ckumsg("Please note: Embedded content cannot be uploaded in demo mode." ,"warning") 
   return;
 }


 var fn=this;

 if (typeof window.upload=='undefined'){
  window.upload={};
  window.upload.status='idle';
  window.upload.inactivity=0;
  window.upload.progress=0;
  window.upload.oldprogress=0;
  window.upload.inactivitylimit=40; // Tolerate connection outage of up to polltimer * inactivitylimit milliseconds.
  window.upload.polltimer=500; // Pause in ms between processing each item. 
  window.upload.current='';
  window.upload.blob='';
  window.upload.pointer=0;
  window.upload.chunksize=100000;
  window.upload.ajaxrtn='';
  window.upload.errorcount=0;
  window.upload.maxerrors=5;
  window.upload.progress=0;
  
//  window.upload.localfile='';
//  window.upload.destfile='';
 }

var wu=window.upload;

if (typeof window.uploadlist=='undefined' || window.uploadlist=='') {window.uploadlist={}; window.uploadlist['count']=0;}


if (action=='button' || action=='save') {
 if (wu.status=='abort'){wu.status='idle'}
 wu.oldprogress=-1; // Reset watchdog when upload is restarted
}
if (action=='abort') {wu.status='abort'; return;}

// Loop to detect busy condition and keep waiting as long as progress is indicated by increasing chunk count.... 
if ( wu.status!='idle' && wu.status!='alldone' ) {

  if (action=='button' || action=='save'){
    alert("Uploader is already running. Press Esc to cancel the existing process, or wait for completion.")
    return 'busy';
  }
 
  if (wu.progress==wu.oldprogress){
    wu.inactivity++
    if (wu.inactivity > wu.inactivitylimit){
      ckumsg("Inactivity limit exceeded. There may be a problem with file uploads.", 'error', 3 )
      wu.status='alldone';   
      return 'error';
    }
  } else {
   wu.oldprogress==wu.progress
   wu.inactivity=0;
  }

  setTimeout(function(){cms_uploader()},wu.polltimer);
  return;
}

if (wu.status=='abort') {return;}

 // Get the next file or blob from editable section to process.. 

 var itemfound=false; 

 if (!itemfound) itemfound = getfirstblob('img','src');   
 if (!itemfound) itemfound = getfirstblob('a','href');   
 if (!itemfound) itemfound = getfirstblob('source','src');

 if (!itemfound) {
   if (action=='button') {
     ckumsg('All inline media uploads are complete.','success',3)
   }
   wu.status='alldone';      
   return;
 }   

 wu.status='opening';

 envvars='';
 wu.pointer=0;
 wu.errorcount=0;  
 // ckmessage( 'Uploading' , 'info' );
 // alert('in file')
 ckumsg('Uploading: ' + wu.current.getAttribute('data_destfile'));
 cms_uploader_chunker();

 setTimeout(function(){cms_uploader()},wu.polltimer);
 return;

}


function cms_uploader_closeout() {
// close out link data here if upload is successful.
// thisitem is an html element in the page editable area. Usually img, a or source.      

 var fn=this;
 var wu=window.upload;
 var thisitem=wu.current;

 if (thisitem.tagName.toLowerCase() == 'img'){
    thisitem.src=thisitem.getAttribute('data_destfile');
    thisitem.setAttribute('data-cke-saved-src',thisitem.getAttribute('data_destfile'));
    /// Possibly remove title? 
    thisitem.title=thisitem.getAttribute('data_destfile');
 }
 if (thisitem.tagName.toLowerCase() == 'a'){
    thisitem.href=thisitem.getAttribute('data_destfile');
    thisitem.setAttribute('data-cke-saved-href',thisitem.getAttribute('data_destfile'));
 }
 if (thisitem.tagName.toLowerCase() == 'source'){
    thisitem.src=thisitem.getAttribute('data_destfile');
    thisitem.setAttribute('data-cke-saved-src',thisitem.getAttribute('data_destfile'));
 }
    thisitem.removeAttribute('data_destfile');
    thisitem.removeAttribute('data_overwrite');
    thisitem.removeAttribute('data_smartsize');
    thisitem.removeAttribute('data_mimetype');
    thisitem.classList.remove('inline');
    thisitem.removeAttribute('id');
    wu.status='idle';
return;
}


function cms_uploader_chunker() {

var fn=this;
var wu=window.upload;
 if (wu.errorcount>wu.maxerrors) {wu.status='idle'; return; }  

 if (window.ckmessage.uploader.isVisible()==false){
   wu.status='abort';
   ckumsg('Uploads cancelled', 'warning' );
   return;
 }

 var envvars=wu.envvars;
 var handler_tasks='';
 if (wu.pointer==0){
   wu.status='opening';
   handler_tasks='open, add';
 }else{
   handler_tasks='add';
   wu.status='adding';
 }

// alert('pointer:' + wu.pointer + 'chunksize' + wu.chunksize + 'blob:' + wu.blob.size  )

  var thisitem=wu.current;
  var sepchar='&';
  var envvars = "usr" + "=" + base64_encode("<?php echo gets('usr');?>");
  envvars = envvars + sepchar + "pagefile" + "=" + base64_encode(pagefile);
  envvars = envvars + sepchar + "pagedir" + "=" + base64_encode(pagedir);
  envvars = envvars + sepchar + "action" + "=" + base64_encode('ckupload');
  envvars = envvars + sepchar + "overwrite" + "=" + base64_encode(thisitem.getAttribute('data_overwrite'));
  envvars = envvars + sepchar + "smartsize" + "=" + base64_encode(thisitem.getAttribute('data_smartsize'));
  envvars = envvars + sepchar + "destfile" + "=" + base64_encode(thisitem.getAttribute('data_destfile'));
  envvars = envvars + sepchar + "datatype" + "=" + base64_encode('blob');  
  envvars = envvars + sepchar + "tasks" + "=" + base64_encode(handler_tasks);  


  var reader = new window.FileReader();
        reader.onload = function() {
          var thischunk = (reader.result.split(','));
          thischunk=thischunk[1];
          if (thischunk.length>0){
             envvars = envvars + sepchar + "chunk" + "=" + thischunk;
             envvars = envvars + sepchar + "ccrc" + "=" + base64_encode(cms_hash(thischunk));
          }
          envvars = envvars + sepchar + "tasks" + "=" + base64_encode(handler_tasks);
            var nocache = Math.random();  
            var Ajax2Request = new XMLHttpRequest();
            Ajax2Request.open('post',codedir + 'ckhandler.php',true);
            Ajax2Request.setRequestHeader("User-Agent",'MaraCMS');
            Ajax2Request.onreadystatechange = cms_uploader_cb;
            Ajax2Request.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
            Ajax2Request.setRequestHeader("Content-length", envvars.length);
            Ajax2Request.setRequestHeader("Connection", "close");
            Ajax2Request.send(envvars);
  };

  var thisfile=window.uploadlist[wu.id];
  if (typeof thisfile=='object') {
    wu.progress=wu.pointer/thisfile.size;
    if (wu.pointer + wu.chunksize > thisfile.size){
      handler_tasks=handler_tasks + ',close';
      wu.status='closing';
    }
    // Use original file if possible.... 
    reader.readAsDataURL(thisfile.slice(wu.pointer,wu.pointer+wu.chunksize)); 
  }else{

   ckumsg('No binary data available for '+ wu.current.title,'warning',10);
   // Best get rid of item or it will cause problems...
   cms_uploader_closeout();
  }
 

}

function cms_uploader_cb() {
 var fn=this;
 var wu=window.upload;
 var rawrtns='';
 var status='';
 var returns={};
 if (wu.status=='abort'){return;}
 
 if(this.readyState == 4){
   if (this.status == 200) { 
     rawrtns=this.responseText;
     status='OK';
   } else {
     status='Data transmission error';
   }

  // Protect against garbage at start of callback data.. 
  var arawrtns=rawrtns.split('~::~');
   if (arawrtns.length==3){
       returns=base64_decode(arawrtns[1]);
       arawrtns='';
   }else{
     returns=rawrtns;
   }
  
  var response='';
  var returned_destfile='';
  var aresponse=returns.split(':');
  if (aresponse.length==2) {
    response=aresponse[0].toLowerCase();
    returned_destfile=aresponse[1];
  }else{
    response=returns.toLowerCase();
  }
  wu.ajaxrtn=response;

// alert(response);

  if (response=='ok') {
    wu.errorcount=0;

    if (window.ckmessage.uploader.isVisible()==false){
     wu.status='abort';
     ckumsg('Uploads cancelled', 'warning' );
     return;
    }

    ckumsg_progress(wu.progress);
    if (wu.pointer==0 && returned_destfile.length>4){
      wu.current.setAttribute('data_destfile',returned_destfile);
    }

     if (wu.status=='closing'){
      ckumsg( 'Uploaded ' + wu.current.getAttribute('data_destfile') ,'success',5); 
      cms_uploader_closeout()
      return;
    }
    wu.pointer=wu.pointer+wu.chunksize;
  } else {
    wu.errorcount++;
  }
  if (wu.status!='abort'){cms_imguploader_callback_timer=setTimeout(function(){cms_uploader_chunker();},100);}
 }

}


function ckumsg(msginfo, msgtype, msgduration){
  if (typeof window.ckmessage=='undefined'){window.ckmessage={};}
  if (typeof window.ckmessage.uploader=='undefined'){
     window.ckmessage.uploader = new CKEDITOR.plugins.notification( CKEDITOR.instances.editable, {
        id: 'sdfg',
        message: ' ',
        type: 'info',
        progress:0
    } );

   // window.ckmessage.uploader.addEventListener('click',abort_uploads)
  }
 if (typeof msgduration=='undefined'){
   msgduration=5000
  }else{
   msgduration=msgduration*5000;
  }
 if (typeof msginfo=='undefined') {msginfo=' '}
 if (typeof msgtype=='undefined'){msgtype='info'}
 window.ckmessage.uploader.update( {message:msginfo, type:msgtype, duration:msgduration} ); 
   if (!window.ckmessage.uploader.isVisible()){window.ckmessage.uploader.show()};
 } 
 function ckumsg_progress(msgprogress){
   if (typeof window.ckmessage.uploader=='undefined'){return;}
   window.ckmessage.uploader.update( {type:'progress', progress:msgprogress} );
   if (!window.ckmessage.uploader.isVisible()){window.ckmessage.uploader.show()};
 }
 function ckumsg_hide(){
   if (typeof window.ckmessage.uploader=='undefined'){return;}
   window.ckmessage.uploader.hide();
 }

 
function data2blob(dataURI) {
    // MIT license, graingert,https://github.com/graingert/datauritoblob/blob/master/dataURItoBlob.js
    // convert base64 to raw binary data held in a string
    // doesn't handle URLEncoded DataURIs - see SO answer #6850276 for code that does this
    var byteString = atob(dataURI.split(',')[1]);
    // separate out the mime component
    var mimeString = dataURI.split(',')[0].split(':')[1].split(';')[0];
    // write the bytes of the string to an ArrayBuffer
    var ab = new ArrayBuffer(byteString.length);
    var dw = new DataView(ab);
    for(var i = 0; i < byteString.length; i++) {
        dw.setUint8(i, byteString.charCodeAt(i));
    }
    // write the ArrayBuffer to a blob, and you're done
    return new Blob([ab], {type: mimeString});
}

function flashuploadbutton(setting){
 if (typeof setting=='undefined'){var setting=true}
 var aubuttons=document.getElementsByClassName('cke_button__hupload_icon')
 if (aubuttons.length==0) {return;}
 var aubutton=aubuttons[0];
 var thisbg=aubutton.style.backgroundImage;
 if (thisbg.indexOf('hBlobUploadR.png')==-1){
   aubutton.style.backgroundImage=thisbg.replace('hBlobUpload.png','hBlobUploadR.png');
   aubutton.title="You have pending media uploads.\n Remember to process them before closing your browser."
 }else{
   aubutton.style.backgroundImage=thisbg.replace('hBlobUploadR.png','hBlobUpload.png');
 }
 if (setting==false){
  clearTimeout(flashuploadbutton.timer);
  aubutton.style.backgroundImage=thisbg.replace('hBlobUploadR.png','hBlobUpload.png');
 }
 if (typeof flashuploadbutton.timer!='undefined'){clearTimeout(flashuploadbutton.timer)} 
 flashuploadbutton.timer=setTimeout(flashuploadbutton,1000);
}


//**********************************************************************************************

function getProperties(o) {
  var seenobj = new Set();
  var seenprop = new Set();
  function _proto(obj) {
    return obj instanceof Object ?
           Object.getPrototypeOf(obj) :
           obj.constructor.prototype;
  }
  function _properties(obj) {
    var ret = [];
    if (obj === null || seenobj.has(obj)) { return ret; }
    seenobj.add(obj);
    if (obj instanceof Object) {
      var ps = Object.getOwnPropertyNames(obj);
      for (var i = 0; i < ps.length; ++i) {
        if (!seenprop.has(ps[i])) {
          ret.push(ps[i]);
          seenprop.add(ps[i]);
        }
      }
    }
    return ret.concat(_properties(_proto(obj)));
  }
  return _properties(o);
}

// ****************************** Auto image downsizing functions 

function getimginfo() {
  // Get image dimensions IF they have been set in CK. 
  var ed=document.getElementById('editable')
  var aimg=ed.getElementsByTagName('img')
  var aimgdat=[];
  for (var ct=0;ct<aimg.length;ct++){
    var i=aimg[ct];

    if (i.style.width.substr(-1)=='%' || i.style.height.substr(-1)=='%'){continue;}

    var iwidth=i.getAttribute('width');
    if (iwidth==null){ 
      var isw=i.style.width;
      if (isw.indexOf('px')>-1){
         iwidth=isw.substr(0,(isw.length-2));
      }
    }     
  
    var iheight=i.getAttribute('height');
    if (iheight==null){ 
      var ish=i.style.height;
      if (ish.indexOf('px')>-1){
         iheight=ish.substr(0,(ish.length-2));
      }
    }     
    
    if (iwidth==i.naturalWidth && iheight==i.naturalHeight){continue;}
    if (iwidth==null && iheight==null){ continue; }
     
    var thisdat=[];
    thisdat[0]=i.getAttribute('src');
    thisdat[1]=iwidth;
    thisdat[2]=iheight;

    aimgdat[aimgdat.length]=thisdat;
  }
  if (aimgdat.length>0){
   return array2json(aimgdat);
  }
}

/**
 * Converts the given data structure to a JSON string.
 * http://www.openjs.com/scripts/data/json_encode.php
 */
 
function array2json(arr) {
    var parts = [];
    var is_list = (Object.prototype.toString.apply(arr) === '[object Array]');
    for(var key in arr) {
    	var value = arr[key];
        if(typeof value == "object") { //Custom handling for arrays
            if(is_list) parts.push(array2json(value)); /* :RECURSION: */
            else parts.push('"' + key + '":' + array2json(value)); /* :RECURSION: */
            //else parts[key] = array2json(value); /* :RECURSION: */
            
        } else {
            var str = "";
            if(!is_list) str = '"' + key + '":';

            //Custom handling for multiple data types
            if(typeof value == "number") str += value; //Numbers
            else if(value === false) str += 'false'; //The booleans
            else if(value === true) str += 'true';
            else str += '"' + value + '"'; //All other things
            // :TODO: Is there any more datatype we should be in the lookout for? (Functions?)

            parts.push(str);
        }
    }
    var json = parts.join(",");
    
    if(is_list) return '[' + json + ']';//Return numerical JSON
    return '{' + json + '}';//Return associative JSON
}


