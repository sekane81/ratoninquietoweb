(function() {
    tinymce.PluginManager.add('TT_tc_button', function( editor, url ) {
	tinymce.plugins.sympleShortcodeMce.theurl = url;
	editor.addButton( 'TT_tc_button', {
		title: "T20 - Shortcodes",
	            type: 'menubutton',
	            icon: 'wp_page',
		menu: [{
			text: 'Column',
			icon: 'redo',
			onclick: function() {
				editor.windowManager.open( {
					title: 'Add Column',
					body: [{
						type: 'listbox', 
						name: 'position', 
						label: 'Position', 
						'values': [
							{text: 'First', value: 'first'},
							{text: 'Last', value: 'last'}
						]
					}, {
						type: 'listbox', 
						name: 'size', 
						label: 'Size', 
						'values': [
							{text: 'one-half', value: 'one-half'},
							{text: 'one-third', value: 'one-third'},
							{text: 'one-fourth', value: 'one-fourth'},
							{text: 'one-fifth', value: 'one-fifth'},
							{text: 'one-sixth', value: 'one-sixth'},
							{text: 'two-third', value: 'two-third'},
							{text: 'three-fourth', value: 'three-fourth'},
							{text: 'two-fifth', value: 'two-fifth'},
							{text: 'two-three', value: 'three-fifth'},
							{text: 'two-four', value: 'four-fifth'},
							{text: 'five-sixth', value: 'five-sixth'}
						]
					}, {
						type: 'textbox',
						name: 'content',
						label: 'Content'
					}, {
						type: 'textbox',
						name: 'class',
						label: 'Custom Class'
					}],
					onsubmit: function( e ) {
						editor.insertContent( '[symple_column size="' + e.data.size + '" position="' + e.data.position + '" class="' + e.data.class + '"]' + e.data.content + '[/symple_column]');
					}
				});
			}
                        }, {
			text: 'Button',
			icon: 'redo',
			onclick: function() {
				editor.windowManager.open( {
					title: 'Add Button',
					body: [{
						type: 'listbox', 
						name: 'color', 
						label: 'Color', 
						'values': [
							{text: 'gray', value: 'gray'},
							{text: 'blue', value: 'blue'},
							{text: 'black', value: 'black'},
							{text: 'red', value: 'red'},
							{text: 'orange', value: 'orange'},
							{text: 'rosy', value: 'rosy'},
							{text: 'pink', value: 'pink'},
							{text: 'green', value: 'green'},
							{text: 'brown', value: 'brown'},
							{text: 'purple', value: 'purple'},
							{text: 'gold', value: 'gold'},
							{text: 'teal', value: 'teal'}
						]
					}, {
						type: 'listbox', 
						name: 'target', 
						label: 'Target', 
						'values': [
							{text: '_blank', value: 'blank'},
							{text: '_self', value: 'self'}
						]
					}, {
						type: 'textbox',
						name: 'title',
						label: 'Title'
					}, {
						type: 'textbox',
						name: 'text',
						label: 'Text'
					}, {
						type: 'textbox',
						name: 'url',
						label: 'URL'
					}, {
						type: 'textbox',
						name: 'radius',
						label: 'Border Radius - eg: 10px'
					}],
					onsubmit: function( e ) {
						editor.insertContent( '[symple_button color="' + e.data.color + '" url="' + e.data.url + '" title="' + e.data.title + '" target="' + e.data.target + '" border_radius="' + e.data.radius + '"]' + e.data.text + '[/symple_button]');
					}
				});
			}
		}, {
			text: 'Google Map',
			icon: 'redo',
			onclick: function() {
				editor.windowManager.open( {
					title: 'Add Googlemap',
					body: [{
						type: 'textbox',
						name: 'title',
						label: 'Title'
					}, {
						type: 'textbox',
						name: 'location',
						label: 'Location/Address'
					}, {
						type: 'textbox',
						name: 'zoom',
						label: 'Zoom from 1-19'
					}, {
						type: 'textbox',
						name: 'height',
						label: 'Height - eg: 300'
					}],
					onsubmit: function( e ) {
						editor.insertContent( '[symple_googlemap title="' + e.data.title + '" location="' + e.data.location + '" zoom="' + e.data.zoom + '" height=' + e.data.height + ']');
					}
				});
			}
		}, {
			text: 'Callout',
			icon: 'redo',
			onclick: function() {
				editor.windowManager.open( {
					title: 'Add Callout',
					body: [{
						type: 'listbox', 
						name: 'color', 
						label: 'Button Color', 
						'values': [
							{text: 'gray', value: 'gray'},
							{text: 'blue', value: 'blue'},
							{text: 'black', value: 'black'},
							{text: 'red', value: 'red'},
							{text: 'orange', value: 'orange'},
							{text: 'rosy', value: 'rosy'},
							{text: 'pink', value: 'pink'},
							{text: 'green', value: 'green'},
							{text: 'brown', value: 'brown'},
							{text: 'purple', value: 'purple'},
							{text: 'gold', value: 'gold'},
							{text: 'teal', value: 'teal'}
						]
					}, {
						type: 'listbox', 
						name: 'rel', 
						label: 'Button rel', 
						'values': [
							{text: 'nofollow', value: 'nofollow'},
							{text: 'follow', value: 'follow'}
						]
					}, {
						type: 'textbox',
						name: 'text',
						label: 'Button text'
					}, {
						type: 'textbox',
						name: 'url',
						label: 'Button url'
					}, {
						type: 'textbox',
						name: 'content',
						label: 'Content/Description'
					}],
					onsubmit: function( e ) {
						editor.insertContent( '[symple_callout button_text="' + e.data.text + '" button_color="' + e.data.color + '" button_url="' + e.data.url + '" button_rel="' + e.data.rel + '"]' + e.data.content + '[/symple_callout]');
					}
				});
			}
		}, {
			text: 'Pricing Table',
			icon: 'redo',
			onclick: function() {
				editor.insertContent( '[symple_pricing_table]<br />[symple_pricing size="one-half" plan="Free" cost="$0" per="per month" button_url="#" button_text="Sign Up" button_color="gold" button_border_radius="" button_target="self" button_rel="nofollow" position=""]<br /><ul><li>30GB Storage</li><li>512MB Ram</li><li>10 databases</li><li>1,000 Emails</li><li>25GB Bandwidth</li></ul>[/symple_pricing]<br /><br />[symple_pricing size="one-half" position="last" featured="yes" plan="Basic" cost="$19.99" per="per month" button_url="#" button_text="Sign Up" button_color="gold" button_border_radius="" button_target="self" button_rel="nofollow"]<br /><ul><li>30GB Storage</li><li>512MB Ram</li><li>10 databases</li><li>1,000 Emails</li><li>25GB Bandwidth</li></ul>[/symple_pricing]<br />[/symple_pricing_table]');
			}
		}, {
			text: 'Skillbar',
			icon: 'redo',
			onclick: function() {
				editor.windowManager.open( {
					title: 'Add Skillbar',
					body: [{
						type: 'textbox',
						name: 'title',
						label: 'Title'
					}, {
						type: 'textbox',
						name: 'per',
						label: 'Percentage'
					}, {
						type: 'textbox',
						name: 'color',
						label: 'Color - eg: #000'
					}],
					onsubmit: function( e ) {
						editor.insertContent( '[symple_skillbar title="' + e.data.title + '" percentage="' + e.data.per + '" color="' + e.data.color + '"]');
					}
				});
			}
		}, {
			text: 'Social Icon',
			icon: 'redo',
			onclick: function() {
				editor.windowManager.open( {
					title: 'Add Social Icon',
					body: [{
						type: 'textbox',
						name: 'title',
						label: 'Title'
					}, {
						type: 'textbox',
						name: 'url',
						label: 'Url'
					}, {
						type: 'listbox', 
						name: 'icon', 
						label: 'Icon', 
						'values': [
							{text: 'facebook', value: 'facebook'},
							{text: 'behance', value: 'behance'},
							{text: 'twitter', value: 'twitter'},
							{text: 'blogger', value: 'blogger'},
							{text: 'delicious', value: 'delicious'},
							{text: 'deviantart', value: 'deviantart'},
							{text: 'digg', value: 'digg'},
							{text: 'dopplr', value: 'dopplr'},
							{text: 'dribbble', value: 'dribbble'},
							{text: 'evernote', value: 'evernote'},
							{text: 'flickr', value: 'flickr'},
							{text: 'forrst', value: 'forrst'},
							{text: 'github', value: 'github'},
							{text: 'google', value: 'google'},
							{text: 'grooveshark', value: 'grooveshark'},
							{text: 'instagram', value: 'instagram'},
							{text: 'lastfm', value: 'lastfm'},
							{text: 'linkedin', value: 'linkedin'},
							{text: 'mail', value: 'mail'},
							{text: 'myspace', value: 'myspace'},
							{text: 'paypal', value: 'paypal'},
							{text: 'picasa', value: 'picasa'},
							{text: 'pinterest', value: 'pinterest'},
							{text: 'posterous', value: 'posterous'},
							{text: 'reddit', value: 'reddit'},
							{text: 'rss', value: 'rss'},
							{text: 'sharethis', value: 'sharethis'},
							{text: 'skype', value: 'skype'},
							{text: 'soundcloud', value: 'soundcloud'},
							{text: 'spotify', value: 'spotify'},
							{text: 'stumbleupon', value: 'stumbleupon'},
							{text: 'tumblr', value: 'tumblr'},
							{text: 'skype', value: 'skype'},
							{text: 'viddler', value: 'viddler'},
							{text: 'vimeo', value: 'vimeo'},
							{text: 'virb', value: 'virb'},
							{text: 'windows', value: 'windows'},
							{text: 'WordPress', value: 'WordPress'},
							{text: 'youtube', value: 'youtube'},
							{text: 'zerply', value: 'zerply'}
						]
					}],
					onsubmit: function( e ) {
						editor.insertContent( '[symple_social icon="' + e.data.icon + '" url="' + e.data.url + '" title="' + e.data.title + '" target="blank" rel="nofollow"]');
					}
				});
			}
		}, {
			text: 'Testimonial',
			icon: 'redo',
			onclick: function() {
				editor.windowManager.open( {
					title: 'Add Testimonial',
					body: [{
						type: 'textbox',
						name: 'by',
						label: 'By'
					}, {
						type: 'textbox',
						name: 'content',
						label: 'Content'
					}],
					onsubmit: function( e ) {
						editor.insertContent( '[symple_testimonial by="' + e.data.by + '"]' + e.data.content + '[/symple_testimonial]');
					}
				});
			}
		}, {
			text: 'Info Boxes',
			icon: 'redo',
			onclick: function() {
				editor.windowManager.open( {
					title: 'Add Box',
					body: [{
						type: 'listbox', 
						name: 'color', 
						label: 'Color', 
						'values': [
							{text: 'blue', value: 'blue'},
							{text: 'gray', value: 'gray'},
							{text: 'green', value: 'green'},
							{text: 'red', value: 'red'},
							{text: 'yellow', value: 'yellow'}
						]
					}, {
						type: 'listbox', 
						name: 'align', 
						label: 'Text align', 
						'values': [
							{text: 'left', value: 'left'},
							{text: 'center', value: 'center'},
							{text: 'right', value: 'right'}
						]
					}, {
						type: 'listbox', 
						name: 'float', 
						label: 'Float', 
						'values': [
							{text: 'none', value: 'none'},
							{text: 'left', value: 'left'},
							{text: 'right', value: 'right'}
						]
					}, {
						type: 'textbox',
						name: 'width',
						label: 'Width - eg: 100%'
					}, {
						type: 'textbox',
						name: 'content',
						label: 'Content'
					}],
					onsubmit: function( e ) {
						editor.insertContent( '[symple_box color="' + e.data.color + '" text_align="' + e.data.align + '" width="' + e.data.width + '" float="' + e.data.float + '"]' + e.data.content + '[/symple_box]');
					}
				});
			}
		}, {
			text: 'Highlight',
			icon: 'redo',
			onclick: function() {
				editor.windowManager.open( {
					title: 'Add highlight',
					body: [{
						type: 'listbox', 
						name: 'color', 
						label: 'Color', 
						'values': [
							{text: 'blue', value: 'blue'},
							{text: 'gray', value: 'gray'},
							{text: 'green', value: 'green'},
							{text: 'red', value: 'red'},
							{text: 'yellow', value: 'yellow'}
						]
					}, {
						type: 'textbox',
						name: 'content',
						label: 'Content'
					}],
					onsubmit: function( e ) {
						editor.insertContent( '[symple_highlight color="' + e.data.color + '"]' + e.data.content + '[/symple_highlight]');
					}
				});
			}
		}, {
			text: 'Accordion',
			icon: 'redo',
			onclick: function( e ) {
				editor.insertContent('[symple_accordion]<br />[symple_accordion_section title="Section 1"]<br />Accordion Content<br />[/symple_accordion_section]<br />[symple_accordion_section title="Section 2"]<br />Accordion Content<br />[/symple_accordion_section]<br />[/symple_accordion]');
			}
		}, {
			text: 'Tab',
			icon: 'redo',
			onclick: function( e ) {
				editor.insertContent('[symple_tabgroup]<br />[symple_tab title="First Tab"]<br />First tab content<br />[/symple_tab]<br />[symple_tab title="Second Tab"]<br />Third Tab Content.<br />[/symple_tab]<br />[/symple_tabgroup]');
			}
		}, {
			text: 'Toggle',
			icon: 'redo',
			onclick: function() {
				editor.windowManager.open( {
					title: 'Add Toggle',
					body: [{
						type: 'textbox',
						name: 'title',
						label: 'Title'
					}, {
						type: 'textbox',
						name: 'content',
						label: 'Content'
					}],
					onsubmit: function( e ) {
						editor.insertContent( '[symple_toggle title="' + e.data.title + '"]' + e.data.content + '[/symple_toggle]');
					}
				});
			}
		}, {
			text: 'Divider',
			icon: 'redo',
			onclick: function() {
				editor.windowManager.open( {
					title: 'Add divider',
					body: [{
						type: 'listbox', 
						name: 'style', 
						label: 'Style', 
						'values': [
							{text: 'solid', value: 'solid'},
							{text: 'dashed', value: 'dashed'},
							{text: 'dotted', value: 'dotted'},
							{text: 'double', value: 'double'},
							{text: 'fadeout', value: 'fadeout'},
							{text: 'fadein', value: 'fadein'}
						]
					}, {
						type: 'textbox',
						name: 'mt',
						label: 'Margin top - eg: 20px'
					}, {
						type: 'textbox',
						name: 'mb',
						label: 'Margin bottom'
					}],
					onsubmit: function( e ) {
						editor.insertContent( '[symple_divider style="' + e.data.style + '" margin_top="' + e.data.mt + '" margin_bottom="' + e.data.mb + '"]');
					}
				});
			}
		}, {
			text: 'Clear Floats',
			icon: 'redo',
			onclick: function( e ) {
				editor.insertContent('[symple_clear_floats]');
			}
		}, {
			text: 'Spacing',
			icon: 'redo',
			onclick: function() {
				editor.windowManager.open( {
					title: 'Add Spacing',
					body: [{
						type: 'textbox',
						name: 'size',
						label: 'Height size - eg: 30px'
					}],
					onsubmit: function( e ) {
						editor.insertContent( '[symple_spacing size="' + e.data.size + '"]');
					}
				});
			}
		}, {
			text: 'Youtube',
			icon: 'redo',
			onclick: function() {
				editor.windowManager.open( {
					title: 'Add Youtube',
					body: [{
						type: 'textbox',
						name: 'id',
						label: 'Video ID'
					}],
					onsubmit: function( e ) {
						editor.insertContent( '[symple_youtube id="' + e.data.id + '"]');
					}
				});
			}
		}, {
			text: 'Vimeo',
			icon: 'redo',
			onclick: function() {
				editor.windowManager.open( {
					title: 'Add Vimeo',
					body: [{
						type: 'textbox',
						name: 'id',
						label: 'Video ID'
					}],
					onsubmit: function( e ) {
						editor.insertContent( '[symple_vimeo id="' + e.data.id + '"]');
					}
				});
			}
		}],
		onclick: function(e) {
			e.stopPropagation();
			editor.insertContent(this.value());
		}
	});
    });
})();

(function() {	
	tinymce.create('tinymce.plugins.sympleShortcodeMce', {
		init : function(ed, url){
			tinymce.plugins.sympleShortcodeMce.theurl = url;
		}
	});
	tinymce.PluginManager.add("symple_shortcodes", tinymce.plugins.sympleShortcodeMce);
})();