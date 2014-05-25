tinymce.PluginManager.add('filemanager', function(editor) {

	tinymce.activeEditor.settings.file_browser_callback = filemanager;

	function filemanager (id, value, type, win) {

		var title="FileManager";
		if (typeof editor.settings.filemanager_title !== "undefined" && editor.settings.filemanager_title) {
			title=editor.settings.filemanager_title;
		}

		tinymce.activeEditor.windowManager.open({
			title: title,
			//file: editor.settings.external_filemanager_path+'dialog.php?type='+urltype+'&descending='+descending+sort_by+fldr+'&lang='+editor.settings.language+'&akey='+akey,
			file: '/filemanager/default',
			width: 860,
			height: 570,
			resizable: true,
			maximizable: true,
			inline: 1
		}, {
			setUrl: function (url) {
				var fieldElm = win.document.getElementById(id);
				fieldElm.value = editor.convertURL(url);
				/*if ("fireEvent" in fieldElm) {
					fieldElm.fireEvent("onchange")
				} else {
					var evt = document.createEvent("HTMLEvents");
					evt.initEvent("change", false, true);
					fieldElm.dispatchEvent(evt);
				}*/
			}
		});
	};
	return false;
});