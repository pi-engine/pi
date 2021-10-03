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
        {"name":"basicstyles","groups":["basicstyles"]},
        {"name":"links","groups":["links"]},
        {"name":"paragraph","groups":["list","blocks"]},
        {"name":"document","groups":["mode"]},
        {"name":"insert","groups":["insert"]},
        {"name":"styles","groups":["styles"]}
    ];

    // Active this part and disable all editor filters
    // config.allowedContent = true;

    // Remove some buttons provided by the standard plugins, which are
    // not needed in the Standard(s) toolbar.
    config.removeButtons = 'Underline,Strike,Subscript,Superscript,Anchor,Styles,Specialchar';

    // Set the most common block elements.
    config.format_tags = 'p;h1;h2;h3;pre';

    // Allow all div classes (from theme and for Boostrap and FontAwesome), allow empty <i>
    // config.extraAllowedContent = 'div(*)[*]{*}; a[!href]; span(*)[*]{*};table(*)[*]{*}; p(*)[*]{*}; li(*)[*]{*}; ul(*)[*]{*}; img(*)[*]{*}';
    // CKEDITOR.dtd.$removeEmpty.i = 0;
    // CKEDITOR.dtd.$removeEmpty.span = 0;

    // Simplify the dialog windows.
    // config.removeDialogTabs = 'image:advanced;link:advanced';

    // Set editor height
    config.height = 400;

    // Set roxyFileman example config
    // var roxyFileman = 'YOUR_PI_URL/script/editor/fileman/index.html?integration=ckeditor';
    // config.filebrowserBrowseUrl = roxyFileman;
    // config.filebrowserImageBrowseUrl = roxyFileman+'&type=image';
};

