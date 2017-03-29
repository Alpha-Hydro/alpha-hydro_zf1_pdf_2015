$(function(){
	$('#sections a').mouseover(function() {
		$('#sections a').addClass('add-blur');
		$(this).removeClass('add-blur');
	}).mouseleave(function() {
		$('#sections a').removeClass('add-blur');
	});
	
	$('#homepage-news-block .news-2-stocks').click(function() {
		var newsBlock = $('#homepage-news-block .news');
		var stocksBlock = $('#homepage-news-block .stocks');
		
		if(newsBlock.hasClass('hidden')) {
			stocksBlock.fadeOut(function() {
				newsBlock.fadeIn(function() {
					newsBlock.removeClass('hidden');
				});				
				stocksBlock.addClass('hidden');				
			});			
		} else {
			newsBlock.fadeOut(function() {
				stocksBlock.fadeIn(function() {
					stocksBlock.removeClass('hidden');
				});				
				newsBlock.addClass('hidden');				
			});
		}
	});
});