;(function ($) {
	
	/**
	 * File widget
	 */
	$.fn.filePicker = function(params) {
		
		params = $.extend({}, params);
		
		this.each(function() {

			var $file = $(this).find('input:file');
			var $text = $(this).find('input:text');
			var $button = $(this).find('button');

			$button.unbind('click').bind('click', function(e) {
				e.preventDefault(); $file.trigger('click');
			});

			$text.unbind('click').bind('click', function(e) {
				e.preventDefault(); $file.trigger('click');
			});

			$file.unbind('change').bind('change', function() {
				$text.val($file.val().fileName());
			});
		});
		return this;
	};

	/**
	 * Rename widget
	 */
	$.fn.renameWidget = function(params) {

		params = $.extend({file: null}, params);

		this.each(function() {

			var $rename = $(this);
			var $file = params.file;
			var extension = '';
			var defaultValue = $rename.val();

			$rename.stripExtension = function() {
				if(extension.length == 0) return;
				var extension_index = $rename.val().lastIndexOf(extension);
				if(extension_index > 0) {
					$rename.val($rename.val().substring(0, extension_index));
				}
			};

			$rename.appendExtension = function() {
				$rename.val($rename.val() + extension);
			};

			$rename.normalize = function() {
				$rename.stripExtension();
				var value = $rename.val().trim().urlize();
				if(value.length > 0) {
					$rename.val(value);
					$rename.appendExtension();
				}else{
					$rename.val(defaultValue);
				}
			};

			$rename.getExtension = function() {
				var ext = $rename.val().fileExtension();
				if(ext.length > 0) {
					extension = '.'+ext;
				}
				$rename.normalize();
			};

			if($file !== null && $file.length == 1) {
				$rename.updateFromFile = function() {
					if($rename.val().length == 0) {
						$rename.val($file.val().fileName());
					}
					var ext = $file.val().fileName().fileExtension();
					if(ext.length > 0) {
						$rename.stripExtension();
						extension = '.'+ext;
						$rename.normalize();
					}else{
						$rename.getExtension();
					}
				};
				$file.bind('change', $rename.updateFromFile);
				$rename.updateFromFile();
			}else{
				$rename.getExtension();
			}

			$rename.bind('focus', function() {
				$rename.stripExtension();
			});

			$rename.bind('blur', $rename.normalize);
		});
		return this;
	};

})(window.jQuery);