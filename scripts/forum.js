$(function(){
	$(".forum-answer").click(function(){
		$(this).parent().parent().find(".answer:last").toggle();
	});
	
	$("form.edit-answer").live("submit", function(){
		var tid = $(this).data("id");
		var answer = $(this).parent();
		
		var qStr = $(this).formSerialize();
		var fAct = $(this).attr("action");
		
		$(answer).html("Сохраняю.. Пожалуйста подождите..");
		
		$.post( fAct, qStr, function(){
			$.get("/forum/view", {id: tid}, function(response){
				answer.html( response );
			}, 'html').error(function(){
				alert("При загрузке отображения произошла ошибка");
			});
		});
		return false;
	});
	
	$(".answer .edit, .question .edit").live("click", function(){
		var answer = $(this).parent().parent().parent();
		
		$.get("/forum/edit", {id: $(answer).data("id")}, function(response){
			$(answer).html( response );
			
			$(answer).find(".datepicker").each(function(){
				var elem = $(this);
				$(this).DatePicker({date: $(elem).val(),
									format: "d.m.Y",
									position: "right",
									onChange: function(formated, dates){
										if ( formated != $(elem).val() )
											$(elem).DatePickerHide();
										$(elem).val(formated);
									}});
			});
			
		}, 'html').error(function(){
			alert("При загрузке формы редактирования произошла ошибка");
		});
		
		return false;
	});
	
	$(".answer .cancel, .question .cancel").live("click", function(){
		var answer = $(this).parent().parent().parent().parent();
		
		$.get("/forum/view", {id: $(answer).data("id")}, function(response){
			$(answer).html( response );
		}, 'html').error(function(){
			alert("При загрузке отображения произошла ошибка");
		});
	});
});