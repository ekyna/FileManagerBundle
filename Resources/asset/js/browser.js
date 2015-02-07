(function(document, $) {

	// http://james.padolsey.com/javascript/sorting-elements-with-jquery/
	jQuery.fn.sortElements = (function(){
	    var sort = [].sort;
	    return function(comparator, getSortable) {
	        getSortable = getSortable || function(){return this;};
	        var placements = this.map(function(){
	            var sortElement = getSortable.call(this),
	                parentNode = sortElement.parentNode,
	                nextSibling = parentNode.insertBefore(
	                    document.createTextNode(''),
	                    sortElement.nextSibling
	                );
	            return function() {
	                if (parentNode === this) {
	                    throw new Error(
	                        "You can't sort elements if any one is a descendant of another."
	                    );
	                }
	                parentNode.insertBefore(this, nextSibling);
	                parentNode.removeChild(nextSibling);
	            };
	        });
	        return sort.call(this, comparator).each(function(i){
	            placements[i].call(getSortable.call(this));
	        });
	    };
	})();

	var pluginName = 'defaultPluginName';
    var defaultConfig = {
        path:    "",
        filters: [],
        sortBy:  "filename",
        sortDir: "asc",
        search:  ""
    };

	function Browser($container) {
		this.config = $.extend({}, defaultConfig, $container.data('config'));
		this.system = $container.data('system');

		this.controls  = $container.find('.efm-controls');
		this.breadcrumb = $container.find('.breadcrumb');
		this.dialog = $container.find('.efm-dialog').hide();
		this.elements  = $container.find('.efm-elements');

		this.init();
	}

	Browser.prototype.init = function () {
		this.browse();
    };

    Browser.prototype.initHandlers = function () {
    	// Mkdir button
    	this.controls.on('click', '[data-role="mkdir"]', {browser: this}, function(e) {
    		e.preventDefault();
    		e.stopPropagation();
    		e.data.browser.mkdir();
    	});

    	// Upload button
    	this.controls.on('click', '[data-role="upload"]', {browser: this}, function(e) {
    		e.preventDefault();
    		e.stopPropagation();
    		e.data.browser.upload();
    	});

    	// Upload button
    	this.controls.on('click', '[data-role="refresh"]', {browser: this}, function(e) {
    		e.preventDefault();
    		e.stopPropagation();
    		e.data.browser.browse();
    	});

    	// Filter button
    	this.controls.on('change', 'input[name="filters[]"]', {browser: this}, function(e) {
    		e.preventDefault();
    		e.stopPropagation();
    		e.data.browser.filter();
    	});

    	// Search input
    	var searchTimeout = null;
    	this.controls.on('keyup', 'input[data-role="search"]', {browser: this}, function(e) {
    		e.preventDefault();
    		e.stopPropagation();
    		clearTimeout(searchTimeout);
    		searchTimeout = setTimeout(function() { e.data.browser.filter(); }, 1500);
    	});
    	this.controls.on('click', 'button[data-role="clear-search"]', {browser: this}, function(e) {
    		e.preventDefault();
    		e.stopPropagation();
    		clearTimeout(searchTimeout);
    		e.data.browser.controls.find('input[data-role="search"]').val('');
    		e.data.browser.filter();
    	});

    	// Sort button
    	this.controls.on('click', '[data-role="sort"]', {browser: this}, function(e) {
    		e.preventDefault();
    		var $input = $(e.currentTarget).find('input');
    		var $icon = $(e.currentTarget).find('span.glyphicon');
    		if ($input.is(':checked')) {
    			var icon = $input.val() == 'filename' ? 'glyphicon-sort-by-alphabet' : 'glyphicon-sort-by-attributes';
    			if ($input.data('dir') == 'asc') {
    				$icon.removeClass(icon).addClass(icon + '-alt');
    				$input.data('dir', 'desc');
    			} else {
    				$icon.removeClass(icon + '-alt').addClass(icon);					
    				$input.data('dir', 'asc');
    			}
    			$input.trigger('change');
    		}
    	});
    	this.controls.on('change', 'input[name="sort"]', {browser: this}, function(e) {
    		e.preventDefault();
    		e.stopPropagation();
    		e.data.browser.sort();
    	});

    	// Element selection
    	this.elements.on('click', '[data-role="select"]', {browser: this}, function(e) {
    		e.preventDefault();
    		e.stopPropagation();
    		var $element = $(e.currentTarget).parents('.efm-element');
    		if ($element.data('type') == 'directory' || $element.data('type') == 'back') {
    			e.data.browser.browse($element.data('system_path'));
    		} else if(parent.tinymce !== undefined && parent.tinymce.activeEditor !== undefined) {
    			parent.tinymce.activeEditor.windowManager.getParams().setUrl($element.data('web_path'));
    			parent.tinymce.activeEditor.windowManager.close();
    		} else {
    			console.log($element.data());
    		}
    	});

    	// Element show
    	this.elements.on('click', '[data-role="show"]', {browser: this}, function(e) {
    		e.preventDefault();
    		e.stopPropagation();
    		var $element = $(e.currentTarget).parents('.efm-element');
    		var url = Routing.generate(
				'ekyna_filemanager_show', 
				$.extend(e.data.browser.config, {'system': e.data.browser.system, 'file': $element.data('filename')})
    		);
    		window.open(url,'_blank');
    	});

    	// Element download
    	this.elements.on('click', '[data-role="download"]', {browser: this}, function(e) {
    		e.preventDefault();
    		e.stopPropagation();
    		var $element = $(e.currentTarget).parents('.efm-element');
    		var url = Routing.generate(
				'ekyna_filemanager_download', 
				$.extend(e.data.browser.config, {'system': e.data.browser.system, 'file': $element.data('filename')})
    		);
    		window.open(url,'_blank');
    	});

    	// Element rename
    	this.elements.on('click', '[data-role="rename"]', {browser: this}, function(e) {
    		e.preventDefault();
    		e.stopPropagation();
    		var $element = $(e.currentTarget).parents('.efm-element');
    		e.data.browser.rename($element);
    	});

    	// Element remove
    	this.elements.on('click', '[data-role="remove"]', {browser: this}, function(e) {
    		e.preventDefault();
    		e.stopPropagation();
    		var $element = $(e.currentTarget).parents('.efm-element');
    		e.data.browser.remove($element);
    	});

    	// Breadcrumb click
    	this.breadcrumb.on('click', 'a', {browser: this}, function(e) {
    		e.preventDefault();
    		e.stopPropagation();
    		e.data.browser.browse($(e.currentTarget).attr('href'));
    	});

    	// Form cancel button
    	this.dialog.on('click', '.efm-form-cancel', function(e) {
    		e.preventDefault();
    		e.stopPropagation();
    		$(e.currentTarget).parents('form').remove();
    	});
    };

    Browser.prototype.clearHandlers = function () {
    	this.controls.off();
    	this.elements.off();
    	this.breadcrumb.off();
    	this.dialog.off();
    };

    Browser.prototype.handleResponse = function (data) {
    	var browser = this;
    	browser.dialog.find('form').remove();

    	// Flash
    	if (data.flash != undefined) {
			browser.flash(data.flash);
    	}

    	// Handle form
    	if (data.form != undefined) {
    		var $form = $(data.form);
    		browser.dialog.append($form).show();
			$form.ajaxForm({
	    		data: browser.config,
	    		dataType: 'json',
	    		beforeSerialize: function($form, options) {
	    			$form.find('input').trigger('blur');
	    		},
	    		beforeSubmit: function(arr, $form, options) {
	    			
	    		},
				success: function (data) {
					browser.handleResponse(data);
				}
			});

			var $file = $form.find('.file-widget').filePicker();
			var $rename = $form.find('.rename-widget');
			if ($rename.length == 1) {
				var options = $file.length == 1 ? {file: $file.find('input:file')} : {};
				$rename.renameWidget(options);
			}
		}

    	// Refresh browser if required
    	if (data.browse === true) {
    		browser.browse();
    		return;
    	}

    	// Render elements
    	if (data.elements != undefined || data.breadcrumb != undefined) {
    		browser.elements.empty();
			$(data.elements).each(function(index, elementData) {
				//console.log(elementData);
				var $element = $(Twig.render(filemanager_browser_element, {'element': elementData}));
				$element.data(elementData);
				browser.elements.append($element);
				if(0 > $.inArray(elementData.type, ['back', 'directory'])) {
					$element.hide();
				}
			});

			// Render breadcrumb
			browser.breadcrumb.empty();
			$(data.breadcrumb).each(function(index, breadcrumbData) {
				var $a = $('<a>').text(breadcrumbData.label).attr('href', breadcrumbData.path);
				browser.breadcrumb.append($('<li>').append($a));
			});

			browser.sort();
			browser.filter();
    	}

    	browser.done();
    };

    Browser.prototype.busy = function () {
    	this.clearHandlers();
    	this.controls.find('button, label').addClass('disabled').prop('disabled', true);
    	this.controls.find('input').prop('disabled', true);
    	this.controls.find('[data-role="refresh"] span.glyphicon').addClass('glyphicon-refresh-animate');
    };

    Browser.prototype.done = function () {
    	this.initHandlers();
    	this.controls.find('button, label').removeClass('disabled').prop('disabled', false);
    	this.controls.find('input').prop('disabled', false);
    	this.controls.find('[data-role="refresh"] span.glyphicon').removeClass('glyphicon-refresh-animate');
    };

    Browser.prototype.flash = function (flash) {
    	var $flash = $(Twig.render(filemanager_browser_flash, {'flash': flash}));
		this.dialog.append($flash).show();
		var timeout = setTimeout(function() {
			$flash.fadeOut(function() {
				$flash.remove();
			});
		}, 6000);
    };

    Browser.prototype.request = function (url, config) {
    	var browser = this;
    	browser.busy();
    	config = $.extend(browser.config, config);
    	$.ajax(url, {
    		data: config,
    		dataType: 'json',
    		type: 'GET'
		})
		.done(function (data) {
			browser.handleResponse(data);
		})
    	.fail(function() {
    		browser.flash({type: 'danger', message: 'L\'application a rencontré une erreur.'});
    	});
    };
    
    Browser.prototype.mkdir = function () {
    	this.request(Routing.generate('ekyna_filemanager_mkdir', {'system': this.system}));
    };

    Browser.prototype.upload = function () {
    	this.request(Routing.generate('ekyna_filemanager_upload', {'system': this.system}));
    };

    Browser.prototype.rename = function ($element) {
    	this.request(Routing.generate('ekyna_filemanager_rename', {'system': this.system, 'file': $element.data('filename')}));
    };

    Browser.prototype.remove = function ($element) {
    	this.request(Routing.generate('ekyna_filemanager_remove', {'system': this.system, 'file': $element.data('filename')}));
    };

    Browser.prototype.browse = function (path) {
    	this.request(Routing.generate('ekyna_filemanager_index', {'system': this.system}), {path: path});
    };

    Browser.prototype.filter = function() {
    	var filters = this.controls.find('input[name="filters[]"]:checked').map(function(_, el) {
    		return $(el).val()
    	}).get();
    	var search = this.controls.find('input[name="search"]').val();
    	if (filters.length > 0 || search.length > 0) {
    		var searchRegex = new RegExp(search.escapeRegExp());
	    	this.elements.find('.efm-element').each(function(i, element) {
	    		var $element = $(element);
	    		var type = $element.data('type');
	    		if (0 <= $.inArray(type, ['back', 'directory'])) {
	    			return;
	    		}
	    		if(filters.length > 0) {
		    		if (0 > $.inArray(type, filters)) {
		    			$element.hide();
		    		} else {
		    			$element.show();
		    		}
	    		}
	    		if(search.length > 0) {
	    			if(searchRegex.test($element.data('filename'))) {
	    				$element.show();
		    		} else {
		    			$element.hide();
		    		}
	    		}
	    	});
    	} else {
    		this.elements.find('.efm-element').show();
    	}
    };

    Browser.prototype.sort = function() {
    	var $input = $(this.controls.find('input[name="sort"]:checked'));
    	var prop = $input.val();
    	var dir = $input.data('dir') == 'asc' ? 1 : -1;
    	this.elements.find('.efm-element-directory').sortElements(function(a, b) {
    	    return $(a).data(prop) > $(b).data(prop) ? dir : -dir;
		});
    	this.elements.find('.efm-element').not('.efm-element-back, .efm-element-directory').sortElements(function(a, b) {
    		return $(a).data(prop) > $(b).data(prop) ? dir : -dir;
    	});
    };

	$(document).ready(function() {
		
		var browser = new Browser($('.efm-browser'));

	});
	
})(window.document, jQuery);