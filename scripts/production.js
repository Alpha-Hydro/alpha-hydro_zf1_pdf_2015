$(function() {
	$("body")
            .append(
                '<div id="bg-grey" style="position: fixed;top: 0;left: 0;height: 100%;width: 100%;z-index: 1101;background: rgba(34, 34, 34, 0.6); overflow-y: scroll; display: none;"><div id="window" class="production-item r big"><div class="header"><a href="javascript:void(0)" class="close"><span>Закрыть</span></a></div><div class="content"></div></div></div>'
            );

	$(".window").click(function() {
		var item = $(this).parent();

		$("#window .content").html($(item).html());
		$("#window img").attr("src", $("#window .window").attr("href"));
		$("#window .hover").hide();
		$("#window").show();
		$("#bg-grey").show();
		                
                $("body").css("overflow", 'hidden');

		return false;
	});

	$(this).find(".hover").hide();
	$(".window").hover(function() {
		$(this).find(".hover").show();
	}, function() {
		$(this).find(".hover").hide();
	});

	$("#window .close").click(function() {
		$("#window").hide();
		$("#bg-grey").hide();
                $("body").css("overflow", 'inherit');
	});

	$("#window .window").live("click", function() {
		return false;
	});
	
	$(".production-item.r").after("<br clear='all' />");
});