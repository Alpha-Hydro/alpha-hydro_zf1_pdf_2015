$(function() {
	$("textarea.wysiwyg").each(function(){
		var editor = new nicEditor({fullPanel : true});
		editor.panelInstance( $(this).attr("id") );
	});
});