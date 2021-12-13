/**
 * @license Copyright (c) 2003-2014, CKSource - Frederico Knabben. All rights reserved.
 * For licensing, see LICENSE.md or http://ckeditor.com/license
 */

( function() {
	var imageDialog = function( editor, dialogType ) {
			// Load image preview.
			var IMAGE = 1,
				LINK = 2,
				PREVIEW = 4,
				CLEANUP = 8,
				regexGetSize = /^\s*(\d+)((px)|\%)?\s*$/i,
				regexGetSizeOrEmpty = /(^\s*(\d+)((px)|\%)?\s*$)|^$/i,
				pxLengthRegex = /^\d+px$/;

			var onSizeChange = function() {
					var value = this.getValue(),
						// This = input element.
						dialog = this.getDialog(),
						aMatch = value.match( regexGetSize ); // Check value
					if ( aMatch ) {
						if ( aMatch[ 2 ] == '%' ) // % is allowed - > unlock ratio.
						switchLockRatio( dialog, false ); // Unlock.
						value = aMatch[ 1 ];
					}

					// Only if ratio is locked
					if ( dialog.lockRatio ) {
						var oImageOriginal = dialog.originalElement;
						if ( oImageOriginal.getCustomData( 'isReady' ) == 'true' ) {
							if ( this.id == 'txtHeight' ) {
								if ( value && value != '0' )
									value = Math.round( oImageOriginal.$.width * ( value / oImageOriginal.$.height ) );
								if ( !isNaN( value ) )
									dialog.setValueOf( 'info', 'txtWidth', value );
							} else //this.id = txtWidth.
							{
								if ( value && value != '0' )
									value = Math.round( oImageOriginal.$.height * ( value / oImageOriginal.$.width ) );
								if ( !isNaN( value ) )
									dialog.setValueOf( 'info', 'txtHeight', value );
							}
						}
					}
					updatePreview( dialog );
				};

			var updatePreview = function( dialog ) {
					//Don't load before onShow.
					if ( !dialog.originalElement || !dialog.preview )
						return 1;

					// Read attributes and update imagePreview;
					dialog.commitContent( PREVIEW, dialog.preview );
					return 0;
				};

			// Custom commit dialog logic, where we're intended to give inline style
			// field (txtdlgGenStyle) higher priority to avoid overwriting styles contribute
			// by other fields.
			function commitContent() {
				var args = arguments;
				var inlineStyleField = this.getContentElement( 'advanced', 'txtdlgGenStyle' );
				inlineStyleField && inlineStyleField.commit.apply( inlineStyleField, args );

				this.foreach( function( widget ) {
					if ( widget.commit && widget.id != 'txtdlgGenStyle' )
						widget.commit.apply( widget, args );
				} );
			}

			// Avoid recursions.
			var incommit;

			// Synchronous field values to other impacted fields is required, e.g. border
			// size change should alter inline-style text as well.
			function commitInternally( targetFields ) {
				if ( incommit )
					return;

				incommit = 1;

				var dialog = this.getDialog(),
					element = dialog.imageElement;
				if ( element ) {
					// Commit this field and broadcast to target fields.
					this.commit( IMAGE, element );

					targetFields = [].concat( targetFields );
					var length = targetFields.length,
						field;
					for ( var i = 0; i < length; i++ ) {
						field = dialog.getContentElement.apply( dialog, targetFields[ i ].split( ':' ) );
						// May cause recursion.
						field && field.setup( IMAGE, element );
					}
				}

				incommit = 0;
			}

			var switchLockRatio = function( dialog, value ) {
					if ( !dialog.getContentElement( 'info', 'ratioLock' ) )
						return null;

					var oImageOriginal = dialog.originalElement;

					// Dialog may already closed. (#5505)
					if ( !oImageOriginal )
						return null;

					// Check image ratio and original image ratio, but respecting user's preference.
					if ( value == 'check' ) {
						if ( !dialog.userlockRatio && oImageOriginal.getCustomData( 'isReady' ) == 'true' ) {
							var width = dialog.getValueOf( 'info', 'txtWidth' ),
								height = dialog.getValueOf( 'info', 'txtHeight' ),
								originalRatio = oImageOriginal.$.width * 1000 / oImageOriginal.$.height,
								thisRatio = width * 1000 / height;
							dialog.lockRatio = false; // Default: unlock ratio

							if ( !width && !height )
								dialog.lockRatio = true;
							else if ( !isNaN( originalRatio ) && !isNaN( thisRatio ) ) {
								if ( Math.round( originalRatio ) == Math.round( thisRatio ) )
									dialog.lockRatio = true;
							}
						}
					} else if ( value != undefined )
						dialog.lockRatio = value;
					else {
						dialog.userlockRatio = 1;
						dialog.lockRatio = !dialog.lockRatio;
					}

					var ratioButton = CKEDITOR.document.getById( btnLockSizesId );
					if ( dialog.lockRatio )
						ratioButton.removeClass( 'cke_btn_unlocked' );
					else
						ratioButton.addClass( 'cke_btn_unlocked' );

					ratioButton.setAttribute( 'aria-checked', dialog.lockRatio );

					// Ratio button hc presentation - WHITE SQUARE / BLACK SQUARE
					if ( CKEDITOR.env.hc ) {
						var icon = ratioButton.getChild( 0 );
						icon.setHtml( dialog.lockRatio ? CKEDITOR.env.ie ? '\u25A0' : '\u25A3' : CKEDITOR.env.ie ? '\u25A1' : '\u25A2' );
					}

					return dialog.lockRatio;
				};

			var resetSize = function( dialog ) {
					var oImageOriginal = dialog.originalElement;
					if ( oImageOriginal.getCustomData( 'isReady' ) == 'true' ) {
						var widthField = dialog.getContentElement( 'info', 'txtWidth' ),
							heightField = dialog.getContentElement( 'info', 'txtHeight' );
						widthField && widthField.setValue( oImageOriginal.$.width );
						heightField && heightField.setValue( oImageOriginal.$.height );
					}
					updatePreview( dialog );
				};

			var setupDimension = function( type, element ) {
					if ( type != IMAGE )
						return;

					function checkDimension( size, defaultValue ) {
						var aMatch = size.match( regexGetSize );
						if ( aMatch ) {
							if ( aMatch[ 2 ] == '%' ) // % is allowed.
							{
								aMatch[ 1 ] += '%';
								switchLockRatio( dialog, false ); // Unlock ratio
							}
							return aMatch[ 1 ];
						}
						return defaultValue;
					}

					var dialog = this.getDialog(),
						value = '',
						dimension = this.id == 'txtWidth' ? 'width' : 'height',
						size = element.getAttribute( dimension );

					if ( size )
						value = checkDimension( size, value );
					value = checkDimension( element.getStyle( dimension ), value );

					this.setValue( value );
				};

			var previewPreloader;

			var onImgLoadEvent = function() {
					// Image is ready.
					var original = this.originalElement,
						loader = CKEDITOR.document.getById( imagePreviewLoaderId );

					original.setCustomData( 'isReady', 'true' );
					original.removeListener( 'load', onImgLoadEvent );
					original.removeListener( 'error', onImgLoadErrorEvent );
					original.removeListener( 'abort', onImgLoadErrorEvent );

					// Hide loader.
					if ( loader )
						loader.setStyle( 'display', 'none' );

					// New image -> new domensions
					if ( !this.dontResetSize )
						resetSize( this );

					if ( this.firstLoad )
						CKEDITOR.tools.setTimeout( function() {
						switchLockRatio( this, 'check' );
					}, 0, this );

					this.firstLoad = false;
					this.dontResetSize = false;
				};

			var onImgLoadErrorEvent = function() {
					// Error. Image is not loaded.
					var original = this.originalElement,
						loader = CKEDITOR.document.getById( imagePreviewLoaderId );

					original.removeListener( 'load', onImgLoadEvent );
					original.removeListener( 'error', onImgLoadErrorEvent );
					original.removeListener( 'abort', onImgLoadErrorEvent );

					// Set Error image.
					var noimage = CKEDITOR.getUrl( CKEDITOR.plugins.get( 'image' ).path + 'images/noimage.png' );

					if ( this.preview )
						this.preview.setAttribute( 'src', noimage );

					// Hide loader.
					if ( loader )
						loader.setStyle( 'display', 'none' );

					switchLockRatio( this, false ); // Unlock.
				};

			var numbering = function( id ) {
					return CKEDITOR.tools.getNextId() + '_' + id;
				},
				btnLockSizesId = numbering( 'btnLockSizes' ),
				btnResetSizeId = numbering( 'btnResetSize' ),
				imagePreviewLoaderId = numbering( 'ImagePreviewLoader' ),
				previewLinkId = numbering( 'previewLink' ),
				previewImageId = numbering( 'previewImage' );

			return {
				title: editor.lang.image[ dialogType == 'image' ? 'title' : 'titleButton' ],
				minWidth: 420,
				minHeight: 360,
				onShow: function() {
					this.imageElement = false;
					this.linkElement = false;

					// Default: create a new element.
					this.imageEditMode = false;
					this.linkEditMode = false;

					this.lockRatio = true;
					this.userlockRatio = 0;
					this.dontResetSize = false;
					this.firstLoad = true;
					this.addLink = false;

					var editor = this.getParentEditor(),
						sel = editor.getSelection(),
						element = sel && sel.getSelectedElement(),
						link = element && editor.elementPath( element ).contains( 'a', 1 ),
						loader = CKEDITOR.document.getById( imagePreviewLoaderId );

					// Hide loader.
					if ( loader )
						loader.setStyle( 'display', 'none' );

					// Create the preview before setup the dialog contents.
					previewPreloader = new CKEDITOR.dom.element( 'img', editor.document );
					this.preview = CKEDITOR.document.getById( previewImageId );

					// Copy of the image
					this.originalElement = editor.document.createElement( 'img' );
					this.originalElement.setAttribute( 'alt', '' );
					this.originalElement.setCustomData( 'isReady', 'false' );

					if ( link ) {
						this.linkElement = link;
						this.linkEditMode = true;

						// Look for Image element.
						var linkChildren = link.getChildren();
						if ( linkChildren.count() == 1 ) // 1 child.
						{
							var childTagName = linkChildren.getItem( 0 ).getName();
							if ( childTagName == 'img' || childTagName == 'input' ) {
								this.imageElement = linkChildren.getItem( 0 );
								if ( this.imageElement.getName() == 'img' )
									this.imageEditMode = 'img';
								else if ( this.imageElement.getName() == 'input' )
									this.imageEditMode = 'input';
							}
						}
						// Fill out all fields.
						if ( dialogType == 'image' )
							this.setupContent( LINK, link );
					}

					// Edit given image element instead the one from selection.
					if ( this.customImageElement ) {
						this.imageEditMode = 'img';
						this.imageElement = this.customImageElement;
						delete this.customImageElement;
					}
					else if ( element && element.getName() == 'img' && !element.data( 'cke-realelement' ) ||
						element && element.getName() == 'input' && element.getAttribute( 'type' ) == 'image' ) {
						this.imageEditMode = element.getName();
						this.imageElement = element;
					}

					if ( this.imageEditMode ) {
						// Use the original element as a buffer from  since we don't want
						// temporary changes to be committed, e.g. if the dialog is canceled.
						this.cleanImageElement = this.imageElement;
						this.imageElement = this.cleanImageElement.clone( true, true );

						// Fill out all fields.
						this.setupContent( IMAGE, this.imageElement );
					} else
						this.imageElement = editor.document.createElement( 'img' );

					// Refresh LockRatio button
					switchLockRatio( this, true );

					// Dont show preview if no URL given.
					if ( !CKEDITOR.tools.trim( this.getValueOf( 'info', 'txtUrl' ) ) ) {
						this.preview.removeAttribute( 'src' );
						this.preview.setStyle( 'display', 'none' );
					}
				},
				onOk: function() {
					// Edit existing Image.
					if ( this.imageEditMode ) {
						var imgTagName = this.imageEditMode;

						// Image dialog and Input element.
						if ( dialogType == 'image' && imgTagName == 'input' && confirm( editor.lang.image.button2Img ) ) {
							// Replace INPUT-> IMG
							imgTagName = 'img';
							this.imageElement = editor.document.createElement( 'img' );
							this.imageElement.setAttribute( 'alt', '' );
							editor.insertElement( this.imageElement );
						}
						// ImageButton dialog and Image element.
						else if ( dialogType != 'image' && imgTagName == 'img' && confirm( editor.lang.image.img2Button ) ) {
							// Replace IMG -> INPUT
							imgTagName = 'input';
							this.imageElement = editor.document.createElement( 'input' );
							this.imageElement.setAttributes( {
								type: 'image',
								alt: ''
							} );
							editor.insertElement( this.imageElement );
						} else {
							// Restore the original element before all commits.
							this.imageElement = this.cleanImageElement;
							delete this.cleanImageElement;
						}
					} else // Create a new image.
					{
						// Image dialog -> create IMG element.
						if ( dialogType == 'image' )
							this.imageElement = editor.document.createElement( 'img' );
						else {
							this.imageElement = editor.document.createElement( 'input' );
							this.imageElement.setAttribute( 'type', 'image' );
						}
						this.imageElement.setAttribute( 'alt', '' );
					}

					// Create a new link.
					if ( !this.linkEditMode )
						this.linkElement = editor.document.createElement( 'a' );

					// Set attributes.
					this.commitContent( IMAGE, this.imageElement );
					this.commitContent( LINK, this.linkElement );

					// Remove empty style attribute.
					if ( !this.imageElement.getAttribute( 'style' ) )
						this.imageElement.removeAttribute( 'style' );

					// Insert a new Image.
					if ( !this.imageEditMode ) {
						if ( this.addLink ) {
							//Insert a new Link.
							if ( !this.linkEditMode ) {
								editor.insertElement( this.linkElement );
								this.linkElement.append( this.imageElement, false );
							} else //Link already exists, image not.
							editor.insertElement( this.imageElement );
						} else
							editor.insertElement( this.imageElement );
					} else // Image already exists.
					{
						//Add a new link element.
						if ( !this.linkEditMode && this.addLink ) {
							editor.insertElement( this.linkElement );
							this.imageElement.appendTo( this.linkElement );
						}
						//Remove Link, Image exists.
						else if ( this.linkEditMode && !this.addLink ) {
							editor.getSelection().selectElement( this.linkElement );
							editor.insertElement( this.imageElement );
						}
					}
				},
				onLoad: function() {
					if ( dialogType != 'image' )
						this.hidePage( 'Link' ); //Hide Link tab.
					var doc = this._.element.getDocument();

					if ( this.getContentElement( 'info', 'ratioLock' ) ) {
						this.addFocusable( doc.getById( btnResetSizeId ), 5 );
						this.addFocusable( doc.getById( btnLockSizesId ), 5 );
					}

					this.commitContent = commitContent;
				},
				onHide: function() {
					if ( this.preview )
						this.commitContent( CLEANUP, this.preview );

					if ( this.originalElement ) {
						this.originalElement.removeListener( 'load', onImgLoadEvent );
						this.originalElement.removeListener( 'error', onImgLoadErrorEvent );
						this.originalElement.removeListener( 'abort', onImgLoadErrorEvent );
						this.originalElement.remove();
						this.originalElement = false; // Dialog is closed.
					}

					delete this.imageElement;
				},
				contents: [
					{
					id: 'info',
					label: editor.lang.image.infoTab,
					accessKey: 'I',
					elements: [
						{
						type: 'vbox',
						padding: 0,
						children: [
							{
							type: 'hbox',
							widths: [ '280px', '110px' ],
							align: 'right',
							children: [
								{
								id: 'txtUrl',
								type: 'text',
								label: editor.lang.common.url,
								required: true,
								onChange: function() {
									var dialog = this.getDialog(),
										newUrl = this.getValue();

									//Update original image
									if ( newUrl.length > 0 ) //Prevent from load before onShow
									{
										dialog = this.getDialog();
										var original = dialog.originalElement;

										if ( dialog.preview ) {
											dialog.preview.removeStyle( 'display' );
										}

										original.setCustomData( 'isReady', 'false' );
										// Show loader.
										var loader = CKEDITOR.document.getById( imagePreviewLoaderId );
										if ( loader )
											loader.setStyle( 'display', '' );

										original.on( 'load', onImgLoadEvent, dialog );
										original.on( 'error', onImgLoadErrorEvent, dialog );
										original.on( 'abort', onImgLoadErrorEvent, dialog );
										original.setAttribute( 'src', newUrl );

										if ( dialog.preview ) {
											// Query the preloader to figure out the url impacted by based href.
											previewPreloader.setAttribute( 'src', newUrl );
											dialog.preview.setAttribute( 'src', previewPreloader.$.src );
											updatePreview( dialog );
										}
									}
									// Dont show preview if no URL given.
									else if ( dialog.preview ) {
										dialog.preview.removeAttribute( 'src' );
										dialog.preview.setStyle( 'display', 'none' );
									}
								},
								setup: function( type, element ) {
									if ( type == IMAGE ) {
										var url = element.data( 'cke-saved-src' ) || element.getAttribute( 'src' );
										var field = this;

										this.getDialog().dontResetSize = true;

										field.setValue( url ); // And call this.onChange()
										// Manually set the initial value.(#4191)
										field.setInitValue();
									}
								},
								commit: function( type, element ) {
									if ( type == IMAGE && ( this.getValue() || this.isChanged() ) ) {
										element.data( 'cke-saved-src', this.getValue() );
										element.setAttribute( 'src', this.getValue() );
									} else if ( type == CLEANUP ) {
										element.setAttribute( 'src', '' ); // If removeAttribute doesn't work.
										element.removeAttribute( 'src' );
									}
								},
								validate: CKEDITOR.dialog.validate.notEmpty( editor.lang.image.urlMissing )
							},
								{
								type: 'button',
								id: 'browse',
								// v-align with the 'txtUrl' field.
								// TODO: We need something better than a fixed size here.
								style: 'display:inline-block;margin-top:14px;',
								align: 'center',
								label: editor.lang.common.browseServer,
								hidden: true,
								filebrowser: 'info:txtUrl'
							}
							]
						}
						]
					},
						{
						id: 'txtAlt',
						type: 'text',
						label:  'Caption (' + editor.lang.image.alt +')',
						accessKey: 'T',
						'default': '',
						onChange: function() {
							updatePreview( this.getDialog() );
						},
						setup: function( type, element ) {
							if ( type == IMAGE )
								this.setValue( element.getAttribute( 'alt' ) );
						},
						commit: function( type, element ) {
							if ( type == IMAGE ) {
								if ( this.getValue() || this.isChanged() )
									element.setAttribute( 'alt', this.getValue() );
							} else if ( type == PREVIEW )
								element.setAttribute( 'alt', this.getValue() );
							else if ( type == CLEANUP )
								element.removeAttribute( 'alt' );

						}
					},
						{
						type: 'hbox',
						children: [
							{
							id: 'basic',
							type: 'vbox',
							children: [
								{
								type: 'hbox',
								requiredContent: 'img{width,height}',
								widths: [ '50%', '50%' ],
								children: [
									{
									type: 'vbox',
									padding: 1,
									children: [
										{
										type: 'text',
										width: '45px',
										id: 'txtWidth',
										label: editor.lang.common.width,
										onKeyUp: onSizeChange,
										onChange: function() {
											commitInternally.call( this, 'advanced:txtdlgGenStyle' );
										},
										validate: function() {
											var aMatch = this.getValue().match( regexGetSizeOrEmpty ),
												isValid = !!( aMatch && parseInt( aMatch[ 1 ], 10 ) !== 0 );
											if ( !isValid )
												alert( editor.lang.common.invalidWidth );
											return isValid;
										},
										setup: setupDimension,
										commit: function( type, element, internalCommit ) {
											var value = this.getValue();
											if ( type == IMAGE ) {
												if ( value && editor.activeFilter.check( 'img{width,height}' ) )
													element.setStyle( 'width', CKEDITOR.tools.cssLength( value ) );
												else
													element.removeStyle( 'width' );

												!internalCommit && element.removeAttribute( 'width' );
											} else if ( type == PREVIEW ) {
												var aMatch = value.match( regexGetSize );
												if ( !aMatch ) {
													var oImageOriginal = this.getDialog().originalElement;
													if ( oImageOriginal.getCustomData( 'isReady' ) == 'true' )
														element.setStyle( 'width', oImageOriginal.$.width + 'px' );
												} else
													element.setStyle( 'width', CKEDITOR.tools.cssLength( value ) );
											} else if ( type == CLEANUP ) {
												element.removeAttribute( 'width' );
												element.removeStyle( 'width' );
											}
										}
									},
										{
										type: 'text',
										id: 'txtHeight',
										width: '45px',
										label: editor.lang.common.height,
										onKeyUp: onSizeChange,
										onChange: function() {
											commitInternally.call( this, 'advanced:txtdlgGenStyle' );
										},
										validate: function() {
											var aMatch = this.getValue().match( regexGetSizeOrEmpty ),
												isValid = !!( aMatch && parseInt( aMatch[ 1 ], 10 ) !== 0 );
											if ( !isValid )
												alert( editor.lang.common.invalidHeight );
											return isValid;
										},
										setup: setupDimension,
										commit: function( type, element, internalCommit ) {
											var value = this.getValue();
											if ( type == IMAGE ) {
												if ( value && editor.activeFilter.check( 'img{width,height}' ) )
													element.setStyle( 'height', CKEDITOR.tools.cssLength( value ) );
												else
													element.removeStyle( 'height' );

												!internalCommit && element.removeAttribute( 'height' );
											} else if ( type == PREVIEW ) {
												var aMatch = value.match( regexGetSize );
												if ( !aMatch ) {
													var oImageOriginal = this.getDialog().originalElement;
													if ( oImageOriginal.getCustomData( 'isReady' ) == 'true' )
														element.setStyle( 'height', oImageOriginal.$.height + 'px' );
												} else
													element.setStyle( 'height', CKEDITOR.tools.cssLength( value ) );
											} else if ( type == CLEANUP ) {
												element.removeAttribute( 'height' );
												element.removeStyle( 'height' );
											}
										}
									}
									]
								},
									{
									id: 'ratioLock',
									type: 'html',
									style: 'margin-top:30px;width:40px;height:40px;',
									onLoad: function() {
										// Activate Reset button
										var resetButton = CKEDITOR.document.getById( btnResetSizeId ),
											ratioButton = CKEDITOR.document.getById( btnLockSizesId );
										if ( resetButton ) {
											resetButton.on( 'click', function( evt ) {
												resetSize( this );
												evt.data && evt.data.preventDefault();
											}, this.getDialog() );
											resetButton.on( 'mouseover', function() {
												this.addClass( 'cke_btn_over' );
											}, resetButton );
											resetButton.on( 'mouseout', function() {
												this.removeClass( 'cke_btn_over' );
											}, resetButton );
										}
										// Activate (Un)LockRatio button
										if ( ratioButton ) {
											ratioButton.on( 'click', function( evt ) {
												var locked = switchLockRatio( this ),
													oImageOriginal = this.originalElement,
													width = this.getValueOf( 'info', 'txtWidth' );

												if ( oImageOriginal.getCustomData( 'isReady' ) == 'true' && width ) {
													var height = oImageOriginal.$.height / oImageOriginal.$.width * width;
													if ( !isNaN( height ) ) {
														this.setValueOf( 'info', 'txtHeight', Math.round( height ) );
														updatePreview( this );
													}
												}
												evt.data && evt.data.preventDefault();
											}, this.getDialog() );
											ratioButton.on( 'mouseover', function() {
												this.addClass( 'cke_btn_over' );
											}, ratioButton );
											ratioButton.on( 'mouseout', function() {
												this.removeClass( 'cke_btn_over' );
											}, ratioButton );
										}
									},
									html: '<div>' +
										'<a href="javascript:void(0)" tabindex="-1" title="' + editor.lang.image.lockRatio +
										'" class="cke_btn_locked" id="' + btnLockSizesId + '" role="checkbox"><span class="cke_icon"></span><span class="cke_label">' + editor.lang.image.lockRatio + '</span></a>' +
										'<a href="javascript:void(0)" tabindex="-1" title="' + editor.lang.image.resetSize +
										'" class="cke_btn_reset" id="' + btnResetSizeId + '" role="button"><span class="cke_label">' + editor.lang.image.resetSize + '</span></a>' +
										'</div>'
								}
								]
							},
								{
								type: 'vbox',
								padding: 1,
								children: [
									{
									type: 'text',
									id: 'txtBorder',
									requiredContent: 'img{border-width}',
									width: '60px',
									label: editor.lang.image.border,
									'default': '',
									onKeyUp: function() {
										updatePreview( this.getDialog() );
									},
									onChange: function() {
										commitInternally.call( this, 'advanced:txtdlgGenStyle' );
									},
									validate: CKEDITOR.dialog.validate.integer( editor.lang.image.validateBorder ),
									setup: function( type, element ) {
										if ( type == IMAGE ) {
											var value,
												borderStyle = element.getStyle( 'border-width' );
											borderStyle = borderStyle && borderStyle.match( /^(\d+px)(?: \1 \1 \1)?$/ );
											value = borderStyle && parseInt( borderStyle[ 1 ], 10 );
											isNaN( parseInt( value, 10 ) ) && ( value = element.getAttribute( 'border' ) );
											this.setValue( value );
										}
									},
									commit: function( type, element, internalCommit ) {
										var value = parseInt( this.getValue(), 10 );
										if ( type == IMAGE || type == PREVIEW ) {
											if ( !isNaN( value ) ) {
												element.setStyle( 'border-width', CKEDITOR.tools.cssLength( value ) );
												element.setStyle( 'border-style', 'solid' );
											} else if ( !value && this.isChanged() )
												element.removeStyle( 'border' );

											if ( !internalCommit && type == IMAGE )
												element.removeAttribute( 'border' );
										} else if ( type == CLEANUP ) {
											element.removeAttribute( 'border' );
											element.removeStyle( 'border-width' );
											element.removeStyle( 'border-style' );
											element.removeStyle( 'border-color' );
										}
									}
								},
									{
									type: 'text',
									id: 'txtHSpace',
									requiredContent: 'img{margin-left,margin-right}',
									width: '60px',
									label: editor.lang.image.hSpace,
									'default': '',
									onKeyUp: function() {
										updatePreview( this.getDialog() );
									},
									onChange: function() {
										commitInternally.call( this, 'advanced:txtdlgGenStyle' );
									},
									validate: CKEDITOR.dialog.validate.integer( editor.lang.image.validateHSpace ),
									setup: function( type, element ) {
										if ( type == IMAGE ) {
											var value, marginLeftPx, marginRightPx,
											marginLeftStyle = element.getStyle( 'margin-left' ),
											marginRightStyle = element.getStyle( 'margin-right' );
											marginLeftStyle = marginLeftStyle && marginLeftStyle.match( pxLengthRegex );
											marginRightStyle = marginRightStyle && marginRightStyle.match( pxLengthRegex );
											marginLeftPx = parseInt( marginLeftStyle, 10 );
											marginRightPx = parseInt( marginRightStyle, 10 );
											value = ( marginLeftPx == marginRightPx ) && marginLeftPx;
// ::~  Mod to prevent clash between margin and centering.. 
                                            var dialog=this.getDialog();
                                            var dlgClass=dialog.getContentElement('advanced','txtGenClass');
                                            var txtClasses=' ' +dlgClass.getValue()+' ';
                                            if (txtClasses.indexOf(' left ')>-1){value = marginLeftPx ;}
                                            if (txtClasses.indexOf(' right ')>-1){value = marginRightPx ;}
                                            if (txtClasses.indexOf(' center ')>-1){value = 'n/a' ;}
                                            isNaN( parseInt( value, 10 ) ) && ( value = element.getAttribute( 'hspace' ) );
// ~::
											this.setValue( value );
										}
									},
									commit: function( type, element, internalCommit ) {
                                        var value = parseInt( this.getValue(), 10 );
										if ( type == IMAGE || type == PREVIEW ) {
											if ( !isNaN( value ) ) {
												element.setStyle( 'margin-left', CKEDITOR.tools.cssLength( value ) );
												element.setStyle( 'margin-right', CKEDITOR.tools.cssLength( value ) );
											} else if ( !value && this.isChanged() ) {
												element.removeStyle( 'margin-left' );
												element.removeStyle( 'margin-right' );
											}
// ::~  Remove clashing margin styles which would prevent centering by css.. 
                                            // Note: hSpace setting has to be inhibited in certain  alignment/placing situations.
                                            var dialog=this.getDialog();
                                            var dlgClass=dialog.getContentElement('advanced','txtGenClass');
                                            var txtClasses=' ' +dlgClass.getValue()+' ';
                                            
                                            if (txtClasses.indexOf(' left ')>-1){element.removeStyle( 'margin-right' );}
                                            if (txtClasses.indexOf(' right ')>-1){element.removeStyle( 'margin-left' );}
                                            if (txtClasses.indexOf(' center ')>-1){element.removeStyle( 'margin-right' );element.removeStyle( 'margin-left' );}
// ~::
											if ( !internalCommit && type == IMAGE )
												element.removeAttribute( 'hspace' );
										} else if ( type == CLEANUP ) {
											element.removeAttribute( 'hspace' );
											element.removeStyle( 'margin-left' );
											element.removeStyle( 'margin-right' );
										}
									}
								},


									{
									type: 'text',
									id: 'txtVSpace',
									requiredContent: 'img{margin-top,margin-bottom}',
									width: '60px',
									label: editor.lang.image.vSpace,
									'default': '',
									onKeyUp: function() {
										updatePreview( this.getDialog() );
									},
									onChange: function() {
										commitInternally.call( this, 'advanced:txtdlgGenStyle' );
									},
									validate: CKEDITOR.dialog.validate.integer( editor.lang.image.validateVSpace ),
									setup: function( type, element ) {
										if ( type == IMAGE ) {
											var value, marginTopPx, marginBottomPx,
												marginTopStyle = element.getStyle( 'margin-top' ),
												marginBottomStyle = element.getStyle( 'margin-bottom' );

											marginTopStyle = marginTopStyle && marginTopStyle.match( pxLengthRegex );
											marginBottomStyle = marginBottomStyle && marginBottomStyle.match( pxLengthRegex );
											marginTopPx = parseInt( marginTopStyle, 10 );
											marginBottomPx = parseInt( marginBottomStyle, 10 );

											value = ( marginTopPx == marginBottomPx ) && marginTopPx;
											isNaN( parseInt( value, 10 ) ) && ( value = element.getAttribute( 'vspace' ) );
											this.setValue( value );
										}
									},
									commit: function( type, element, internalCommit ) {
										var value = parseInt( this.getValue(), 10 );
										if ( type == IMAGE || type == PREVIEW ) {
											if ( !isNaN( value ) ) {
												element.setStyle( 'margin-top', CKEDITOR.tools.cssLength( value ) );
												element.setStyle( 'margin-bottom', CKEDITOR.tools.cssLength( value ) );
											} else if ( !value && this.isChanged() ) {
												element.removeStyle( 'margin-top' );
												element.removeStyle( 'margin-bottom' );
											}

											if ( !internalCommit && type == IMAGE )
												element.removeAttribute( 'vspace' );
										} else if ( type == CLEANUP ) {
											element.removeAttribute( 'vspace' );
											element.removeStyle( 'margin-top' );
											element.removeStyle( 'margin-bottom' );
										}
									}
								}
								]
							}
							]
						},
							{
							type: 'vbox',
							height: '250px',
							children: [
								{
								type: 'html',
								id: 'htmlPreview',
								style: 'width:95%;',
								html: '<div>' + CKEDITOR.tools.htmlEncode( editor.lang.common.preview ) + '<br>' +
									'<div id="' + imagePreviewLoaderId + '" class="ImagePreviewLoader" style="display:none"><div class="loading">&nbsp;</div></div>' +
									'<div class="ImagePreviewBox"><table><tr><td>' +
										'<a href="javascript:void(0)" target="_blank" onclick="return false;" id="' + previewLinkId + '">' +
										'<img id="' + previewImageId + '" alt="" /></a>' +
										 '</td></tr></table></div></div>'
								}
                                ]
                            }

						
						
						
						]
					},
                        {
							type: 'hbox',
                            padding:'0px',
                            widths: [ '50%', '50%' ],
							children: [
// start align **********************
{
									id: 'cmbAlign',
									type: 'select',
									widths: [ '35%', '65%' ],
									style: 'width:120px',
									label: 'Positioning',
									'default': '',
									items: [
										[ editor.lang.common.notSet, '' ],
										[ editor.lang.common.alignLeft, 'left' ],
										[ 'Float-' + editor.lang.common.alignLeft, 'float-left'],
										[ editor.lang.common.alignRight, 'right'],
										[ 'Float-' + editor.lang.common.alignRight, 'float-right'],
										[ editor.lang.common.alignCenter, 'center']
									],
									onChange: function() {
// ::~
                                       var dlgAlign=this;
                                       var txtHPosition = this.getValue();
                                       var dialog=this.getDialog();
                                       var dlgClass=dialog.getContentElement('advanced','txtGenClass');
                                       var txtClasses=' ' +dlgClass.getValue()+' ';
                                       var dlgStyles=dialog.getContentElement('advanced','txtdlgGenStyle');
                                       var txtStyles=' ' +dlgStyles.getValue()+' ';

                                       txtClasses=txtClasses.replace(' left ',' ');
                                       txtClasses=txtClasses.replace(' float-left ',' ');
                                       txtClasses=txtClasses.replace(' float-right ',' ');
                                       txtClasses=txtClasses.replace(' right ',' ');
                                       txtClasses=txtClasses.replace(' center ',' ');
                                       txtClasses=txtClasses.trim();
                                       var txtNewClasses=txtClasses + ' ' + txtHPosition;
                                       if (txtNewClasses!=txtClasses){
                                         // Remove styling set by toolbar buttons.. 
                                         if ((txtStyles.indexOf('float: left;')>-1) && (txtHPosition!='float-left')){
                                            var newStyles=txtStyles.replace('float: left;','');
                                            dlgStyles.setValue(newStyles);
                                         }
                                         if ((txtStyles.indexOf('float: right;')>-1) && (txtHPosition!='float-right')){
                                            var newStyles=txtStyles.replace('float: right;','');
                                            dlgStyles.setValue(newStyles);
                                         }
                                         dlgClass.setValue(txtNewClasses);
                                         updatePreview(dialog);
                                       }
// ~::
									},
									setup: function( type, element ) {
										if ( type == IMAGE ) {
// ::~
                                            dlgAlign=this;
                                            // determine if a horizontal position class exists for this image...
											var theseclasses = ' '+element.getAttribute( 'class' )+' ';
                                            var initval='';
                                            alegitvals=' left , float-left , right , float-right , center '.split(',');
                                            for (ct=0; ct<alegitvals.length;ct++){
                                              if (theseclasses.indexOf(alegitvals[ct])>-1){
                                                initval=alegitvals[ct].trim();
                                                break;
                                              }
                                            }
                                            // Allow toolbar styles to override class.. 
                                            if (element.getStyle('float')=='left'){
                                              initval='float-left';
                                            }
                                            if (element.getStyle('float')=='right'){
                                              initval='float-right';
                                            }

                                            dlgAlign.setValue( initval );
// ~::
                                        }
									},
								},
// end align ****************************
// start autocaptioning *********************
{
									type: 'text',
									id: 'cmbCaption',
									width: '60px',
                  label:'Captioning',
									type: 'select',
									widths: [ '35%', '65%' ],
									style: 'width:90px',
									items: [
										[ 'Alt Text', '' ],
										[ 'Below', 'caption' ],
										[ 'Above', 'caption-top' ],
									],
									setup: function( type, element ) {
										if ( type == IMAGE ) {
                                        // determine if a 'caption' class exists for this image...
											var theseclasses = ' '+element.getAttribute( 'class' )+' ';
                                            if (theseclasses.indexOf(' caption ')>-1){
											  this.setValue( 'caption' );
                                            } else if (theseclasses.indexOf(' caption-top ')>-1){
											  this.setValue( 'caption-top' );
                                            } else {
                                              this.setValue( '' );
                                            }
                                        }
									},
									onChange: function() {
                                        var txtCaption = this.getValue();
                                        var dialog=this.getDialog();
                                        var dlgClass=dialog.getContentElement('advanced','txtGenClass');
                                        var txtClasses=' ' +dlgClass.getValue()+' ';
                                        txtClasses=txtClasses.replace(' autocaption-top ',' ');
                                        txtClasses=txtClasses.replace(' autocaption ',' ');
                                        txtClasses=txtClasses.replace(' caption-top ',' ');
                                        txtClasses=txtClasses.replace(' caption ',' ');
                                        txtClasses=txtClasses.trim();
                                        var txtNewClasses=txtClasses + ' ' + txtCaption;
                                        
                                        if (txtNewClasses!=txtClasses){
                                          dlgClass.setValue(txtNewClasses);
                                          updatePreview(dialog);
                                        }
                                    }
                            },
                            
// end autocaptioning *****************                            								

// start onClick (zoom) *********************
{
									type: 'text',
									id: 'cmbOnClick',
									width: '60px',
                                    label:'onClick',
									type: 'select',
									widths: [ '35%', '65%' ],
									style: 'width:90px',
									items: [
										[ 'No Action', 'none' ],
										[ 'Zoom', 'zoom' ],
									],
									setup: function( type, element ) {
										if ( type == IMAGE ) {
                                          // determine if a 'zoom' action exists for this image...
       										var thisClick = ' '+element.getAttribute( 'data-cke-pa-onclick' )+' ';
 		                                  // alert(element.getAttribute( 'data-cke-pa-onclick'));
                                            if (thisClick.indexOf('cms_imgzoom_popup')>0){
											  this.setValue( 'zoom' );
                                            } else {
											  this.setValue( 'none' );
											}  
                                        }
									},
               						commit: function( type, element ) {
				            			if ( type == IMAGE ) {
							            	if ( this.getValue() || this.isChanged() ){
      							           var clickAction="cms_imgzoom_popup(this.src)";
								               // Mod 4.2 to change cursor on zoomable images - might be better to add class instead?
								               if (this.getValue()=='zoom') {element.setAttribute( 'data-cke-pa-onclick', clickAction );element.setStyle('cursor','all-scroll');}
								               if (this.getValue()=='none') {element.removeAttribute( 'data-cke-pa-onclick');element.removeStyle('cursor');}
							                } else if ( type == PREVIEW ) {
								               element.setAttribute( 'data-cke-pa-onclick', '');
							                } else if ( type == CLEANUP ) {
								               /// unsure when this would happen? 
								               element.removeAttribute( 'data-cke-pa-onclick' );
								               element.removeStyle('cursor');
								            }
								         }
                                    },
									onChange: function() {
                                        return;
                                    }
                            }
// end onClick *****************                            								
                            ]
                        }
					]
				},
					
					
					
					
					
					
					
					{
					id: 'Link',
					requiredContent: 'a[href]',
					label: editor.lang.image.linkTab,
					padding: 0,
					elements: [
						{
						id: 'txtUrl',
						type: 'text',
						label: editor.lang.common.url,
						style: 'width: 100%',
						'default': '',
						setup: function( type, element ) {
							if ( type == LINK ) {
								var href = element.data( 'cke-saved-href' );
								if ( !href )
									href = element.getAttribute( 'href' );
								this.setValue( href );
							}
						},
						commit: function( type, element ) {
							if ( type == LINK ) {
								if ( this.getValue() || this.isChanged() ) {
									var url = this.getValue();
									element.data( 'cke-saved-href', url );
									element.setAttribute( 'href', url );

									if ( this.getValue() || !editor.config.image_removeLinkByEmptyURL )
										this.getDialog().addLink = true;
								}
							}
						}
					},
						{
						type: 'button',
						id: 'browse',
						filebrowser: {
							action: 'Browse',
							target: 'Link:txtUrl',
							url: editor.config.filebrowserImageBrowseLinkUrl
						},
						style: 'float:right',
						hidden: true,
						label: editor.lang.common.browseServer
					},
						{
						id: 'cmbTarget',
						type: 'select',
						requiredContent: 'a[target]',
						label: editor.lang.common.target,
						'default': '',
						items: [
							[ editor.lang.common.notSet, '' ],
							[ editor.lang.common.targetNew, '_blank' ],
							[ editor.lang.common.targetTop, '_top' ],
							[ editor.lang.common.targetSelf, '_self' ],
							[ editor.lang.common.targetParent, '_parent' ]
							],
						setup: function( type, element ) {
							if ( type == LINK )
								this.setValue( element.getAttribute( 'target' ) || '' );
						},
						commit: function( type, element ) {
							if ( type == LINK ) {
								if ( this.getValue() || this.isChanged() )
									element.setAttribute( 'target', this.getValue() );
							}
						}
					}
					]
				},
				{
					id: 'Upload',
					hidden: true,
					// filebrowser: 'uploadButton',
					label: editor.lang.image.upload,
					elements: [
                    {

					id: 'txtSmartsize',
					type: 'text',
					widths: [ '35%', '65%' ],
					style: 'width:120px',
					label: 'Smart Resize',
					'default': '',

					commit: function(type, element) {
	  					 if ( type == IMAGE ) {
                            if (this.isChanged()){
                                var newsize=this.getValue();
                                var anewsize=newsize.split('x');
                                if ((newsize=='')||( anewsize.length==2 && anewsize[0]>0 && anewsize[1]>0)){
                                    element.setAttribute( 'data_smartsize', newsize );
                                }else{
                                    alert('Invalid smartsize. Must be in WidthxHeight format. Old value retained. ');
                                }
                            }
		  				 }
                    },


					setup: function( type, element, dialog ) {
						if ( type == IMAGE ) {
						var dialog = this.getDialog();

						dialog.hidePage('Upload');
                        // Not currently used ;
                        if (element.getAttribute('src').substr(0,5)=='data:'){
//                         var dlgUpload=dialog.getContentElement('Upload','txtGenClass');
                         //  dialog.showPage('Upload')   ;
                        }
                        var dlgSmartsize=this;
                        var initval='';
                        if (typeof element.getAttribute('data_smartsize')!='undefined'){
                           initval= element.getAttribute('data_smartsize');
                        }
                        dlgSmartsize.setValue( initval );
                        }

                        
					}


                },


/*
						{
						type: 'file',
						id: 'upload',
						label: 'Select a file on this computer',
						style: 'height:40px',
						size: 38
					},
*/
                    {
                    type: 'text',
                    label: 'Path and filename on webserver, to upload this local file to',  
                    id: 'destfile',
          					    setup: function( type, element ) {
                            if ( type == IMAGE ) {
                                var dlgDestfile=this;
                                var initval='';
                                if (typeof element.getAttribute('data_destfile')!='undefined'){
                                    initval= element.getAttribute('data_destfile');
                                }
                                dlgDestfile.setValue( initval );
                            }
                        },
                        
                        commit: function(type, element) {
                            if ( type == IMAGE ) {
                                if (this.isChanged()){
                                    var newpath=this.getValue();
                                    if (newpath.indexOf(' ')==-1){
                                        element.setAttribute( 'data_destfile', newpath );
                                    }else{
                                        alert('Invalid upload filename. Old value retained. ');
                                    }
                                }
                            }
                    },

					},
                    
        						{
					          	  type: 'button',
          	  					hidden: true,
					            	id: 'dataImgUploadButton',
					            	label: "Upload this image to the webserver",
          					    setup: function( type, element ) {
                            if ( type == IMAGE ) {
					             						this.on( 'click', function( evt ) {
												            // cms_imguploader('file:'+ element.getAttribute('id'));
          									      });
                            }
                        }
          					},
                    
        						{
					          	  type: 'button',
              					hidden: true,
					            	id: 'dataImgAllUploadButton',
					            	label: "Upload all local images in this page",
          					    setup: function( type, element ) {
                            if ( type == IMAGE ) {
					             						this.on( 'click', function( evt ) {
												            cms_imguploader('*');
          									      });
                            }
                        }
          					}

                    ]
				},
					{
					id: 'advanced',
					label: editor.lang.common.advancedTab,
					elements: [
						{
						type: 'hbox',
						widths: [ '50%', '25%', '25%' ],
						children: [
							{
							type: 'text',
							id: 'linkId',
							requiredContent: 'img[id]',
							label: editor.lang.common.id,
							setup: function( type, element ) {
								if ( type == IMAGE )
									this.setValue( element.getAttribute( 'id' ) );
							},
							commit: function( type, element ) {
								if ( type == IMAGE ) {
									if ( this.getValue() || this.isChanged() )
										element.setAttribute( 'id', this.getValue() );
								}
							}
						},
							{
							id: 'cmbLangDir',
							type: 'select',
							requiredContent: 'img[dir]',
							style: 'width : 100px;',
							label: editor.lang.common.langDir,
							'default': '',
							items: [
								[ editor.lang.common.notSet, '' ],
								[ editor.lang.common.langDirLtr, 'ltr' ],
								[ editor.lang.common.langDirRtl, 'rtl' ]
								],
							setup: function( type, element ) {
								if ( type == IMAGE )
									this.setValue( element.getAttribute( 'dir' ) );
							},
							commit: function( type, element ) {
								if ( type == IMAGE ) {
									if ( this.getValue() || this.isChanged() )
										element.setAttribute( 'dir', this.getValue() );
								}
							}
						},
							{
							type: 'text',
							id: 'txtLangCode',
							requiredContent: 'img[lang]',
							label: editor.lang.common.langCode,
							'default': '',
							setup: function( type, element ) {
								if ( type == IMAGE )
									this.setValue( element.getAttribute( 'lang' ) );
							},
							commit: function( type, element ) {
								if ( type == IMAGE ) {
									if ( this.getValue() || this.isChanged() )
										element.setAttribute( 'lang', this.getValue() );
								}
							}
						}
						]
					},
						{
						type: 'text',
						id: 'txtGenLongDescr',
						requiredContent: 'img[longdesc]',
						label: editor.lang.common.longDescr,
						setup: function( type, element ) {
							if ( type == IMAGE )
								this.setValue( element.getAttribute( 'longDesc' ) );
						},
						commit: function( type, element ) {
							if ( type == IMAGE ) {
								if ( this.getValue() || this.isChanged() )
									element.setAttribute( 'longDesc', this.getValue() );
							}
						}
					},
						{
						type: 'hbox',
						widths: [ '50%', '50%' ],
						children: [
							{
							type: 'text',
							id: 'txtGenClass',
							requiredContent: 'img(cke-xyz)', // Random text like 'xyz' will check if all are allowed.
							label: editor.lang.common.cssClass,
							'default': '',
							setup: function( type, element ) {
								if ( type == IMAGE )
									this.setValue( element.getAttribute( 'class' ) );
							},
							commit: function( type, element ) {
								if ( type == IMAGE ) {
									if ( this.getValue() || this.isChanged() )
										element.setAttribute( 'class', this.getValue() );
								}
							}
						},
							{
							type: 'text',
							id: 'txtGenTitle',
							requiredContent: 'img[title]',
							label: editor.lang.common.advisoryTitle,
							'default': '',
							onChange: function() {
								updatePreview( this.getDialog() );
							},
							setup: function( type, element ) {
								if ( type == IMAGE )
									this.setValue( element.getAttribute( 'title' ) );
							},
							commit: function( type, element ) {
								if ( type == IMAGE ) {
									if ( this.getValue() || this.isChanged() )
										element.setAttribute( 'title', this.getValue() );
								} else if ( type == PREVIEW )
									element.setAttribute( 'title', this.getValue() );
								else if ( type == CLEANUP )
									element.removeAttribute( 'title' );

							}
						}
						]
					},
						{
						type: 'text',
						id: 'txtdlgGenStyle',
						requiredContent: 'img{cke-xyz}', // Random text like 'xyz' will check if all are allowed.
						label: editor.lang.common.cssStyle,
						validate: CKEDITOR.dialog.validate.inlineStyle( editor.lang.common.invalidInlineStyle ),
						'default': '',
						setup: function( type, element ) {
							if ( type == IMAGE ) {
								var genStyle = element.getAttribute( 'style' );
								if ( !genStyle && element.$.style.cssText )
									genStyle = element.$.style.cssText;
								this.setValue( genStyle );

								var height = element.$.style.height,
									width = element.$.style.width,
									aMatchH = ( height ? height : '' ).match( regexGetSize ),
									aMatchW = ( width ? width : '' ).match( regexGetSize );

								this.attributesInStyle = {
									height: !!aMatchH,
									width: !!aMatchW
								};
							}
						},
						onChange: function() {
							commitInternally.call( this, [ 'info:cmbFloat', 'info:cmbAlign',
								'info:txtVSpace', 'info:txtHSpace',
								'info:txtBorder',
								'info:txtWidth', 'info:txtHeight' ] );
							updatePreview( this );
						},
						commit: function( type, element ) {
							if ( type == IMAGE && ( this.getValue() || this.isChanged() ) )
								element.setAttribute( 'style', this.getValue() );

						}
					}
					]
				}
				]
			};
		};

	CKEDITOR.dialog.add( 'image', function( editor ) {
		return imageDialog( editor, 'image' );
	} );

	CKEDITOR.dialog.add( 'imagebutton', function( editor ) {
		return imageDialog( editor, 'imagebutton' );
	} );
} )();
