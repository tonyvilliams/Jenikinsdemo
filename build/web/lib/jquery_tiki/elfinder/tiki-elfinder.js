/**
 * Tiki wrapper for elFinder
 *
 * (c) Copyright 2002-2013 by authors of the Tiki Wiki CMS Groupware Project

 * All Rights Reserved. See copyright.txt for details and a complete list of authors.
 * Licensed under the GNU LESSER GENERAL PUBLIC LICENSE. See license.txt for details.
 *
 * $Id: tiki-elfinder.js 46289 2013-06-12 14:36:55Z jonnybradley $
 */

/**
 * Open a dialog with elFinder in it
 * @param element	unused?
 * @param options	object containing jquery-ui and elFinder dialog options
 * @return {Boolean}
 */

openElFinderDialog = function(element, options) {
	var $dialog = $('<div/>'), buttons = {};
	options = options ? options : {};
	$(document.body).append($dialog);
	$(window).data('elFinderDialog', $dialog);	// needed for select handler later

	options = $.extend({
		title : tr("Browse Files"),
		minWidth : 500,
		height : 520,
		width: 800,
		zIndex : 9999,
		modal: true,
		eventOrigin: this
	}, options);

	buttons[tr('Close')] = function () {
		$dialog
			.dialog('close');
	};


	if (options.eventOrigin) {	// save it for later
		$("body").data("eventOrigin", options.eventOrigin);	// sadly adding data to the dialog kills elfinder :(
		delete options.eventOrigin;
	}

	var elfoptions = initElFinder(options);

	$dialog.dialog({
		title: options.title,
		minWidth: options.minWidth,
		height: options.height,
		width: options.width,
		buttons: buttons,
		modal: options.modal,
		zIndex: options.zIndex,
		open: function () {

			var $elf = $('<div class="elFinderDialog" />');
			$(this).append($elf);
			$elf.elfinder(elfoptions).elfinder('instance');
		},
		close: function () {
			$("body").data("eventOrigin", "");
			$(this).dialog('close')
				.dialog('destroy')
				.remove();
		}
	});

	return false;
};

/**
 * Set up elFinder for tiki use
 *
 * @param options {Object} Tiki ones: defaultGalleryId, deepGallerySearch & getFileCallback
 * 			also see https://github.com/Studio-42/elFinder/wiki/Client-configuration-options
 * @return {Object}
 */

function initElFinder(options) {

	options = $.extend({
		getFileCallback: null,
		defaultGalleryId: 0,
		deepGallerySearch: true,
		url: $.service('file_finder', 'finder'), // connector URL
		// lang: 'ru',								// language (TODO)
		customData:{
			defaultGalleryId:options.defaultGalleryId,
			deepGallerySearch:options.deepGallerySearch
		}
	}, options);

	var lang = jqueryTiki.language;
	if (lang && typeof elFinder.prototype.i18[lang] !== "undefined" && !options.lang) {
		if (lang == 'cn') {
			lang = 'zh_CN';
		} else if (lang == 'pt-br') {
			lang = 'pt_BR';
		}
		options.lang = lang;
	}

	if (options.defaultGalleryId > 0) {
		options.rememberLastDir = false;
		if (!options.deepGallerySearch) {
			//elfoptions.ui = ['toolbar', 'path', 'stat'];
		}
	}

	delete options.defaultGalleryId;		// moved into customData
	delete options.deepGallerySearch;


	// turn off some elfinder commands - not many left to do...
	var remainingCommands = elFinder.prototype._options.commands, idx;
	var disabled = ['mkfile', 'edit', 'archive', 'resize'];
	// done 'rm', 'duplicate', 'rename', 'mkdir', 'upload', 'copy', 'cut', 'paste', 'extract',
	$.each(disabled, function (i, cmd) {
		(idx = $.inArray(cmd, remainingCommands)) !== -1 && remainingCommands.splice(idx, 1);
	});
	return options;
}

