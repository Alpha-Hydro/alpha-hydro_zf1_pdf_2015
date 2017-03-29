$(function() {

	$("#mainmenu ul a").hover(function() {
		$(this).parent().addClass("active");
	}, function() {
		$(this).parent().removeClass("active");
	})

});