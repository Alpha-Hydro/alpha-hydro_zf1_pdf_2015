$(function() {
	var t = 0;

	current = new Object();

	var current = {

		__self : null,
		
		init : function( setup ) {
			if ( setup )
				__self = $("#search-result span:first");
			else
				__self = null;
		},

		next : function() {
			__self.removeClass("active");
			__self = __self.next('a').size() ? __self.next('a') : $("#search-result a:first");
			__self.addClass("active");
		},
		prev : function() {
			__self.removeClass("active");
			__self = __self.prev('a').size() ? __self.prev('a') : $("#search-result a:last");
			__self.addClass("active");
		},
		
		goTo: function(){
                    	window.location.assign( __self.attr('href') );
		},
		
	}

	function searchProduct() {
		if ($(this).val() != '' )
			$.post("/catalog/products/search", {
				sku : $(this).val()
			}, function(response) {
				$("#search-result").html(response);
				current.init(true);
			})
//			.error(function() {
//				alert("error occurred");
//			});
		else
			$("#search-result").empty();
	}

	$("form#search input[type='text']").focus(function() {
		if (!$(this).hasClass("stored"))
			$(this).val("");
		$("#search-result").show();
	}).blur(
			function() {
				$(this).removeClass("active");
				clearInterval(t);
				if ($(this).val().trim() == "") {
					$(this).removeClass("stored");
					$(this).val($(this).data("default"));
				} else {
					$(this).addClass("stored");
				}

				$("#search-result").is(":hover") ? $(this).focus() : $(
						"#search-result").hide();

			}).keyup(
			function(e) {
                                console.log(e.keyCode);
				var ARROW_UP = 38;
				var ARROW_DOWN = 40;
				var RETURN = 13;
				if (e.keyCode == RETURN ) {
					//current.goTo();
					return false;
				}

				switch (e.keyCode) {
					case ARROW_UP:
						current.prev();
						break;
	
					case ARROW_DOWN:
						current.next();
						break;
	
					default:
						clearTimeout(t);
						current.init(false);
						t = setTimeout(searchProduct.bind(this), 500);
						break;
				}

			});
});
