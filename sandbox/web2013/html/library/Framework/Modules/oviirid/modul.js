$(function(){
	$('select').selectBox();
	
	$('a.copy_emails').click(function(e){
		e.preventDefault();
		$('.loading').show();
		
		$.ajax({
			url:Conf.requestUrl+'&m=getAllEmails',
			type:'POST',
			data: {},
			dataType:'json',
			success: function(d) {
				if(d && typeof d == 'object' && d.status == 1) {
					$('.loading').hide();
					$('#frame textarea').val(d.data);
					$('.modal').show();
					$('#frame textarea').focus().select();
				} else
					alert('error');
					$('.loading').hide();
			},
			error: function() {
				alert('error');
				$('.loading').hide();
			}
		});	
	});
	
	$('a.close').click(function(e){
		e.preventDefault();
		$('.modal').hide();
		$('#frame textarea').val('');
	});
	
	// navigate with keyboard
	$(document).keydown(function(e) {
		//e.preventDefault();
		switch(e.keyCode){
			case 27:// esc
				if(!$('a.close').hasClass('disabled'))
					$('a.close').trigger('click');
			break;
		}
		
	});
});
