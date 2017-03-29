$(function(){
		
	$(".tabs.header a").click(function(){
		$(this).parent().find("a").removeClass("active");
		$(this).addClass("active");
		
		$(".tabs.content > div").hide();
		$("#tab-"+$(this).attr("href").substr(1)).show();
	});
	
	
	
	if ( window.location.hash && $(".tabs.header a[href='"+window.location.hash+"']").size() > 0 ){
		$(".tabs.header a[href='"+window.location.hash+"']").click();
	} else {
		$(".tabs.header a:first").click();
	}
	
	
});
