$(function() {

	function Basket() {
		this.put = function(id, count) {
			$("#basket>a").html( "Обновление.." );
			var e = this;
			$.post("/catalog/basket/put", {id: id, count: count}, function(){
				e.__refresh();
			}).error(function(){
				$("#basket>a").html("Ошибка");
			});
			
			return true;
		};
		
		this.__refresh = function(){
			
			$.post("/catalog/basket/count", {}, function(str){
				$("#basket>a").html( str );
			}).error(function(){
				$("#basket>a").html("Ошибка");
			});
		};
		
		this.clear = function() {
			Basket.items = new Array();
		};

	}

	$(".to_basket").click(function() {
		var c = parseInt($(this).prev().find("input").val(), 10);
		var basket = new Basket();
		basket.put($(this).data("id"), c);
	});
	
	$(".count .up").click(function(){
		var i = parseInt($(this).parent().find("input").val(), 10);
		$(this).parent().find("input").val(i+1);
	});
	
	$(".count .down").click(function(){
		var i = parseInt($(this).parent().find("input").val(), 10);
		if ( i > 0 )
			$(this).parent().find("input").val(i-1);
	});
	
	$("#basket .menu").click(function(){
		if($(this).hasClass('close')) {
			$(this).removeClass('close');
		} else {
			$(this).addClass('close');
		}
		$("#basket #basket-menu").toggle();
	});
});