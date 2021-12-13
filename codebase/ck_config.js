
CKEDITOR.editorConfig = function( config ) {
	// Define changes to default configuration here. For example:
	// config.language = 'en-gb';
	// config.uiColor = '#AADC6E';
  // Allow the use of Firefox's spell checker.. 
  config.disableNativeSpellChecker = false;
  // The moaninglisa skin has some css positioning issues with our dialogs, so.. 
  config.skin = 'moonocolor';
  // Browser settings are now here to avoid duplication in both editor doctype modes.
   // config.filebrowserBrowseUrl = '';
   // config.filebrowserImageBrowseUrl = '';
   config.filebrowserFlashBrowseUrl = '';
   config.filebrowserUploadUrl = '';
   config.filebrowserImageUploadUrl = '';
   config.filebrowserFlashUploadUrl = '';

   // For uploadimage drag n drop uploader (not used as we now have our own) 
   // config.uploadUrl = window.codedir + 'nativeckhandler.php'; 

   config.filebrowserBrowseUrl = window.codedir + 'dir.php?type=files';
   config.filebrowserImageBrowseUrl = window.codedir + 'dir.php?type=images';
   config.filebrowserImageBrowseLinkUrl = window.codedir + 'dir.php?type=files';
   // config.filebrowserUploadUrl = window.codedir + 'dragdrop.php?type=fbupurl';
   // config.filebrowserImageUploadUrl = window.codedir + 'dragdrop.php?type=fbimgupurl';
   config.filebrowserWindowWidth = '800';
   config.filebrowserWindowHeight = '650';

// By default, Mara's version of CKEditor allows any valid HTML
// regardless of declared HTML version, although it removes broken or mismatched tags. 
// Place // before the allowedContent line to use a much stricter set of filtering rules (basically valid HTML5 only) 

// Test of php readonly:
// config.protectedSource.push( /<\?[\s\S]*?\?>/g );

// Add any required CK plugins here, separated by commas. NO spaces allowed.  
//   config.extraPlugins = 'htmlwriter,mara';
     config.extraPlugins = 'htmlwriter,mara,autolink,oembed,notification,widget,uploadwidget,lineutils,texzilla';

// config.extraPlugins = 'htmlwriter,mara,autolink,oembed,notification,lineutils,dialog,widget,clipboard,mathjax';
// config.extraPlugins = 'htmlwriter,mara,,filetools,notification,notificationaggregator,uploadwidget,uploadimage';


config.allowedContent=true;
config.dataIndentationChars = '  ';
// config.toolbarCanCollapse = true;

config.stylesSet = [
	/* Block styles */
	{ name: 'Paragraph',	element: 'p' },
	{ name: 'H1',		element: 'h1' },
	{ name: 'H2',		element: 'h2' },
	{ name: 'H3',		element: 'h3' },
	{ name: 'H4',		element: 'h4' },
	{ name: 'H5',		element: 'h5' },
	{ name: 'H6',		element: 'h6' },
	{ name: 'PRE',element: 'pre' },
	{ name: 'DIV',element: 'div' },
	{ name: 'SPAN',element: 'span' },
/*
	{ name: 'Address',			element: 'address' },
	{ name: 'Quotation',	element: 'q' },
	{ name: 'Computer Code',	element: 'code' },
	{ name: 'Deleted Text',		element: 'del' },
	{ name: 'Inserted Text',	element: 'ins' },
	{ name: 'Cited Work',		element: 'cite' },
*/
];


// Toolbar is now selected in ckinline.js params to allow dynamic change 
// config.toolbar = 'Mara';

config.toolbar_Mara =
[
	{ name: 'forms', items : [ 'Form', 'Checkbox', 'Radio', 'TextField', 'Textarea', 'Select', 'Button', 'ImageButton', 'HiddenField' ] },
	{ name: 'paragraph', items : [ 'NumberedList','BulletedList','-','Outdent','Indent','-','Blockquote','CreateDiv',
	'-','JustifyLeft','JustifyCenter','JustifyRight','JustifyBlock' ] },
	{ name: 'insert', items : [ 'Table','SpecialChar','Iframe','oembed','texzilla' ] },
	{ name: 'separators', items : [ 'HorizontalRule','hBreak' ] },
	{ name: 'source', items : [ 'hSrcEdit' ] },
  { name: 'tools', items: ['ShowBlocks']},
'/',
	{ name: 'snddocument', items : [ 'hSave' , 'hPreviewPage'] },
	{ name: 'editing', items : ['Undo','Redo','Find','Replace','-','SelectAll' ] },
	{ name: 'styles', items : [ 'Styles','FontSize' , 'TextColor','BGColor' ] },
	{ name: 'basicstyles', items : [ 'Bold','Italic','Underline','Strike','Subscript','Superscript','-','RemoveFormat'] },
	{ name: 'images', items : [ 'Image', 'hQuickImage', 'hUpload' ] },
	{ name: 'links', items : [ 'Link','Unlink','Anchor' ] },
];

config.toolbar_Mobile =
[
	{ name: 'paragraph', items : [ 'BulletedList','Outdent','Indent','JustifyLeft','JustifyCenter','JustifyRight','JustifyBlock' ] },
	{ name: 'styles', items : [ 'Format' ] },
	{ name: 'separators', items : [ 'hSrcEdit' ] },
  { name: 'tools', items: ['ShowBlocks']},
'/',
	{ name: 'snddocument', items : [ 'hSave' , 'hPreviewPage'] },
	{ name: 'editing', items : ['Undo','Redo','Find','Replace' ] },
	{ name: 'images', items : [ 'Image',] },
	{ name: 'links', items : [ 'Link','Unlink'] },
	{ name: 'basicstyles', items : [ 'Bold','Italic','Underline'] }
];

config.toolbar_Full =
[
	{ name: 'snddocument', items : [ 'Source','-','Save','NewPage','DocProps','Preview','Print','-','Templates' ] },
	{ name: 'clipboard', items : [ 'Undo','Redo' ] },
	{ name: 'editing', items : [ 'Find','Replace','-','SelectAll','-','SpellChecker', 'Scayt' ] },
	{ name: 'forms', items : [ 'Form', 'Checkbox', 'Radio', 'TextField', 'Textarea', 'Select', 'Button', 'ImageButton', 
        'HiddenField' ] },
	'/',
	{ name: 'basicstyles', items : [ 'Bold','Italic','Underline','Strike','Subscript','Superscript','-','RemoveFormat' ] },
	{ name: 'paragraph', items : [ 'NumberedList','BulletedList','-','Outdent','Indent','-','Blockquote','CreateDiv',
	'-','JustifyLeft','JustifyCenter','JustifyRight','JustifyBlock','-','BidiLtr','BidiRtl' ] },
	{ name: 'links', items : [ 'Link','Unlink','Anchor' ] },
	{ name: 'insert', items : [ 'Image','Flash','Table','HorizontalRule','Smiley','SpecialChar','PageBreak','Iframe' ] },
	'/',
	{ name: 'styles', items : [ 'Styles','Format','FontSize' ] },
	{ name: 'colors', items : [ 'TextColor','BGColor' ] },
	{ name: 'tools', items : [ 'Maximize', 'ShowBlocks' ] }
];

 
config.toolbar_Basic =
[
	['Bold', 'Italic', '-', 'NumberedList', 'BulletedList', '-', 'Link', 'Unlink','-','About']
];


};
