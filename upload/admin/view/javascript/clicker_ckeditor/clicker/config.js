/**
 * @license Copyright (c) 2003-2023, CKSource - Frederico Knabben. All rights reserved.
 * For licensing, see https://ckeditor.com/legal/ckeditor-oss-license
 */

CKEDITOR.editorConfig = function( config ) {
	// Define changes to default configuration here. For example:
	// config.language = 'fr';
	// config.uiColor = '#AADC6E';


	// config.skin = 'bootstrapck';

	// config.height = 350;
	// config.resize_enabled = 1;
	// config.resize_dir = 'vertical';

	config.versionCheck = false;

	config.embed_provider = '//ckeditor.iframe.ly/api/oembed?url={url}&callback={callback}'; // for ckeditor 4.7

	config.toolbar = 'full';

	config.toolbar_basic = [
		['Source'],
		['Maximize'],
		['Bold','Italic','Underline','Strike','-','Subscript','Superscript'],
		['NumberedList','BulletedList','-','Outdent','Indent'],
		['JustifyLeft','JustifyCenter','JustifyRight','JustifyBlock'],
		['SpecialChar'],
		'/',
		['Undo','Redo'],
		['Font','FontSize'],
		['TextColor','BGColor'],
		['Link','Unlink','Anchor'],
		['Image','cl_bs_grid','Table','HorizontalRule']
	];

	// FontAwesome configs
	config.fontawesomePath = 'view/javascript/font-awesome/css/font-awesome.min.css';

	// Use OpenCart filemanager handler for dialogs
	// config.filebrowserDialogs = ['image', 'image2', 'link', 'flash', 'video', 'html5video', 'html5audio'];
	// config.filebrowserButtons = ['browseposter', 'browse'];

	CKEDITOR.on('instanceLoaded', function(evt) {
		ck_clicker_on_ready(evt);
	});

	// Emoji default list url
	config.emoji_emojiListUrl = CKEDITOR.basePath + 'clicker/plugins/emoji/emoji.json';

	config = c_ckeSetConfig(config);

	// console.log(config);
};

var ck_clicker_on_ready_to = {};
var cl_cke;

function ck_clicker_on_ready(evt) {
	if (typeof ck_clicker_on_ready_to[evt.editor.name] != 'undefined') {
		clearTimeout(ck_clicker_on_ready_to[evt.editor.name]);
	}
	ck_clicker_on_ready_to[evt.editor.name] = setTimeout(function(evt) {
		if (typeof cl_cke == 'undefined') {
			cl_cke = new c_cke({
				debug: cke_settings.debug,
				user_token: cke_settings.user_token,
				token: cke_settings.token,
			});
		}

		for (instance in CKEDITOR.instances) {
			// Store editor data to textarea when focus lost
			CKEDITOR.instances[instance].on('change', function(e) {
				if (cl_cke.timeout_onchange) clearTimeout(cl_cke.timeout_onchange);

				cl_cke['timeout_onchange'] = setTimeout(function() {
					for (var idx in CKEDITOR.instances) {
						CKEDITOR.instances[idx].updateElement();
					}
				}, 200);
			});
		}

		var toolbar_before = {
			'a.cke_button.cke_button__image': 'a.cke_button__cl_bs_grid',
			'a.cke_button.cke_button__video': 'a.cke_button__cl_bs_grid',
			'a.cke_button.cke_button__html5video': 'a.cke_button__cl_bs_grid',
			'a.cke_button.cke_button__html5audio': 'a.cke_button__cl_bs_grid',
			'a.cke_button.cke_button__youtube': 'a.cke_button__cl_bs_grid',
			'a.cke_button.cke_button__fontawesome6': 'a.cke_button__cl_bs_grid',
		};
		var toolbar_after = {};
		var toolbar_hide = {};

		setTimeout(function(evt) {
			for (instance in CKEDITOR.instances) {
				var toolbar_selector = String('#cke_' + evt.editor.name + ' .cke_toolbox ')
					.split('[').join('\\[')
					.split(']').join('\\]');

				for (idx in toolbar_before) {
					if ($(toolbar_selector + toolbar_before[idx]).length) {
						let ckebtn = $(toolbar_selector + idx).detach();
						if (ckebtn.length) {
							$(toolbar_selector + toolbar_before[idx]).before(ckebtn);
						}
					}
				}
			}
		}, 50, evt);
	}, 80, evt);
}