$(function() {

	$(document).bind('drop dragover', function(e) {
		e.preventDefault();
	});
	
	var type = $('input[name=type]').val();
	
	$('.upload').fileupload({
		dataType : 'json',
		url : Conf.requestUrl+'&m=uploadFiles&type='+type,
		dropZone : $('.drop-zone'),
		autoUpload : true,
		maxNumberOfFiles : 1,
		acceptFileTypes : $('input[name=filetypes]').val(),
		add : function(e, data) {
			if($(this).find('input').attr('disabled'))
				return false;
			
			$(this).find('.btn').addClass('disabled');
			$(this).find('input').attr('disabled', 'disabled');
			data.context = $('<span/>').text('Laen üles...').appendTo('.info');
			$('.drop-zone').addClass('bg-loading');
			
			if(eval($('input[name=filetypes]').val()).test(data.files[0].name) === false){
				data.context.text('Tekkis viga, loe veateadet! Uuesti proovimiseks lae leht uuesti');
				$('.errors').append('Veateade: See fail ei ole lubatud!');
				return false;
			}
			
			data.submit();
		},
		progressall : function(e, data) {
			var progress = parseInt(data.loaded / data.total * 100, 10);
			$('.progress .bar').css('width', progress + '%');
		},
		done : function(e, data) {
			$('.progress .bar').css('width', '100%');
			$('.drop-zone').removeClass('bg-loading');
			
			if(data && data.result && data.result.status && data.result.status == 1){
				data.context.text('Üleslaadimine lõpetatud, suunan edasi...');
				setTimeout(function(){
					window.location.href = data.result.url;
				},1000);
			}else{
				data.context.text('Tekkis viga, loe veateadet! Uuesti proovimiseks lae leht uuesti');
				$('.errors').append('Veateade: '+data.result.files[0].error);
			}
			
			
		},
		fail:function(e,data){
			console.log(data);
		}
	});

});