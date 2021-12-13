
(function()
{
	var hBreakCmd =
	{
		modes : { wysiwyg:1, source:1 },
		readOnly : 1,
		exec : function( editor )
		{
          CKEDITOR.instances.editable.insertHtml('<br clear="all">');
		}
	};

	var hQuickImageCmd =
	{
		modes : { wysiwyg:1, source:1 },
		readOnly : 1,
		exec : function( editor )
		{
          QuickImage();
		}
	};

	var hBlobUploadCmd =
	{
		modes : { wysiwyg:1, source:1 },
		readOnly : 1,
		exec : function( editor )
		{
          cms_uploader('button');
		}
	};

	var hPreviewPageCmd =
	{
		modes : { wysiwyg:1, source:1 },
		readOnly : 1,
		exec : function( editor )
		{
           PreviewPage();
		}
	};

	var hSrcEditCmd =
	{
		modes : { wysiwyg:1, source:0 },
		readOnly : 1,
		exec : function( editor )
		{
          // alert(this.path);
          // CKEDITOR.instances.editable.hdSrcEdit.exec();
          // var dialogObj = new CKEDITOR.dialog( editable, 'hSrcEditDlg' );
          // CKEDITOR.instances.editable.h( 'hSrcEditDlg' );
          // document.ajax.data.value = CKEDITOR.instances.editable.getData();
          EditSource();
		}
	};

	var hSaveCmd =
	{
		modes : { wysiwyg:1, source:1 },
		readOnly : 1,
		exec : function( editor )
		{
          document.ajax.data.value = CKEDITOR.instances.editable.getData();
          SaveFile();
		}
	};

	var pluginName = 'mara';
	CKEDITOR.instances.editable.addCommand( 'hdSrcEdit', new CKEDITOR.dialogCommand( 'hSrcEditDlg' ) );

	// Register a plugin named "mara".
	CKEDITOR.plugins.add( pluginName,
	{
		init : function( editor )
		{
   			var command = editor.addCommand( 'hSave', hSaveCmd );
			command.modes = { wysiwyg:1, source:1 };

			editor.ui.addButton( 'hSave',
				{
                 icon: 'save',
					label : 'Save Document to Webspace',
					command : 'hSave'
				});

		    var command = editor.addCommand( 'hSrcEdit', hSrcEditCmd );
		    // editor.addCommand( 'hdSrcEdit', new CKEDITOR.dialogCommand( 'hSrcEditDlg' ) );
			command.modes = { wysiwyg:1, source:0 };
			editor.ui.addButton( 'hSrcEdit',
				{
                 icon: 'source',
					label : 'Edit HTML Source',
					command : 'hSrcEdit'
				});

			var command = editor.addCommand( 'hBreak', hBreakCmd );
			command.modes = { wysiwyg:1, source:1 };

			editor.ui.addButton( 'hBreak',
				{
                 icon: 'pagebreak',
					label : 'Insert Break',
					command : 'hBreak'
				});
				
			var command = editor.addCommand( 'hPreviewPage', hPreviewPageCmd );
			command.modes = { wysiwyg:1, source:1 };

			editor.ui.addButton( 'hPreviewPage',
				{
                 icon: 'preview',
					label : 'Preview changes, without saving',
					command : 'hPreviewPage'
				});
				
			var command = editor.addCommand( 'hQuickImage', hQuickImageCmd );
			command.modes = { wysiwyg:1, source:1 };

			editor.ui.addButton( 'hQuickImage',
				{
                 icon: 'plugins/mara/images/hQuickImage.png',
					label : 'Image Manager',
					command : 'hQuickImage'
				});

			var command = editor.addCommand( 'hBlobUpload', hBlobUploadCmd );
			command.modes = { wysiwyg:1, source:1 };

			editor.ui.addButton( 'hUpload',
				{
                 icon: 'plugins/mara/images/hBlobUpload.png',
					label : 'Start pending uploads',
					command : 'hBlobUpload'
				});
				
    // CKEDITOR.dialog.add( 'hSrcEditDlg', this.path + 'dialogs/hSrcEdit.js' );
		}
	});
})();
