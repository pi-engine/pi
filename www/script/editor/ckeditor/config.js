/**
 * @license Copyright (c) 2003-2015, CKSource - Frederico Knabben. All rights reserved.
 * For licensing, see LICENSE.md or http://ckeditor.com/license
 */

CKEDITOR.editorConfig = function( config ) {
    // Define changes to default configuration here.
    // For complete reference see:
    // http://docs.ckeditor.com/#!/api/CKEDITOR.config

    // The toolbar groups arrangement, optimized for two toolbar rows.
    config.toolbarGroups = [
        { name: 'clipboard', groups: [ 'clipboard', 'undo' ] },
        { name: 'paragraph', groups: [ 'list', 'indent', 'blocks', 'align', 'bidi', 'paragraph' ] },
        { name: 'links', groups: [ 'links' ] },
        '/',
        { name: 'insert', groups: [ 'insert' ] },
        { name: 'editing', groups: [ 'find', 'selection', 'spellchecker', 'editing' ] },
        { name: 'basicstyles', groups: [ 'basicstyles', 'cleanup' ] },
        { name: 'document', groups: [ 'mode', 'document', 'doctools' ] },
        { name: 'others', groups: [ 'others' ] },
        '/',
        { name: 'styles', groups: [ 'styles' ] },
        { name: 'colors', groups: [ 'colors' ] },
        { name: 'about', groups: [ 'about' ] },
        { name: 'tools', groups: [ 'tools' ] }
    ];

    // Remove some buttons provided by the standard plugins, which are
    // not needed in the Standard(s) toolbar.
    config.removeButtons = 'Underline,Subscript,Superscript';

    // Set the most common block elements.
    config.format_tags = 'p;h1;h2;h3;pre';

	// Allow all div classes (from theme and for Boostrap and FontAwesome), allow empty <i>
    config.extraAllowedContent = 'div(*)[*]{*}; a[!href]; span(*)[*]{*};table(*)[*]{*}; p(*)[*]{*}; li(*)[*]{*}; ul(*)[*]{*}; img(*)[*]{*}';
	CKEDITOR.dtd.$removeEmpty.i = 0;
	CKEDITOR.dtd.$removeEmpty.span = 0;
	
    // Simplify the dialog windows.
    config.removeDialogTabs = 'image:advanced;link:advanced';

    // Set editor height
    config.height = 600;

    // Set roxyFileman example config
    // var roxyFileman = 'YOUR_PI_URL/script/editor/fileman/index.html?integration=ckeditor';
    // config.filebrowserBrowseUrl = roxyFileman;
    // config.filebrowserImageBrowseUrl = roxyFileman+'&type=image';
};
