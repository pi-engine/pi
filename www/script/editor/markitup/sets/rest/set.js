// -------------------------------------------------------------------
// markItUp!
// -------------------------------------------------------------------
// Copyright (C) 2011 Florent Gallaire <fgallaire@gmail.com>  
// License GNU GPLv3 or any later version.
// Copyright (C) 2008 Jay Salvat
// http://markitup.jaysalvat.com/
// -------------------------------------------------------------------
// ReStrucuredText tags example
// http://docutils.sourceforge.net/docs/user/rst/quickref.html
// http://docutils.sourceforge.net/docs/user/rst/quickstart.html
// -------------------------------------------------------------------
// Feel free to add more tags
// -------------------------------------------------------------------
mySettings = {
	previewParserPath:	'', // path to your ReStructuredText parser
	onShiftEnter:		{keepDefault:false, replaceWith:'\n\n'},
	markupSet: [
		{name:'Heading', key:'1', placeHolder:'Your title here...', closeWith:function(markItUp) { return miu.markdownTitle(markItUp, '*') } },
		{name:'Heading', key:'2', placeHolder:'Your title here...', closeWith:function(markItUp) { return miu.markdownTitle(markItUp, '=') } },
		{name:'Heading', key:'3', placeHolder:'Your title here...', closeWith:function(markItUp) { return miu.markdownTitle(markItUp, '-') } },
		{name:'Heading', key:'4', placeHolder:'Your title here...', closeWith:function(markItUp) { return miu.markdownTitle(markItUp, '^') } },
		{name:'Heading', key:'5', placeHolder:'Your title here...', closeWith:function(markItUp) { return miu.markdownTitle(markItUp, '"') } },
		{separator:'---------------' },		
		{name:'Bold', key:'B', openWith:'**', closeWith:'**'}, 
		{name:'Italic', key:'I', openWith:'*', closeWith:'*'}, 
		{separator:'---------------' },
		{name:'Bulleted list', openWith:'- '}, 
		{name:'Numeric list', openWith:'# '}, 
		{separator:'---------------' },
		{name:'Picture', key:'P', openWith:'\n\n.. image:: ', closeWith:'\n\n', placeHolder:'Your picture here...'}, 
		{name:'Link', key:'L', openWith:'`', closeWith:' [![Link:!:http://]!]`_', placeHolder:'Your text to link here...'},
		{separator:'---------------'},	
		{name:'Quote', openWith:'\t'},
		{name:'Code', openWith:'``', closeWith:'``'},
		{separator:'---------------' },
		{name:'Preview', call:'preview', className:'preview'}
	]
}

// mIu nameSpace to avoid conflict.
miu = {
	markdownTitle: function(markItUp, char) {
		heading = '';
		n = $.trim(markItUp.selection||markItUp.placeHolder).length;
		for(i = 0; i < n; i++) {
			heading += char;
		}
		return '\n'+heading;
	}
}
