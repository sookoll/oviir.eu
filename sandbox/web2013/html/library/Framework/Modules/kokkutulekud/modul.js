$(function(){
	var opts = {
		iconsPath : '/libs/nicEdit/nicEditorIcons.gif',
		buttonList : ['fontSize','bold','italic','underline','strikeThrough','left','center','right','justify','ol','ul','subscript','superscript','hr','forecolor','link'],
		maxHeight : 300
	}
	nicEditors.allTextAreas(opts);
	$('select').selectBox();
	
	$('a.save').click(function(e){
		e.preventDefault();
		saveEventData();
	});
	
	$('a.delete').click(function(e){
		e.preventDefault();
		deleteEvent();
	});
	
	$('input.text').focus(function(){
		$(this).parent().append('<em class="tip">'+$(this).attr('placeholder')+'</em>');
	}).blur(function(){
		$(this).parent().find('em.tip').remove();
	});
	
	function saveEventData(){
		
		$('.loading').show();
		
		var data = {
			event_id:$('input[name=event_id]').val(),
			year:$('input[name=year]').val(),
			title:$('input[name=title]').val(),
			event_organizer:$('input[name=event_organizer]').val(),
			event_time:$('input[name=event_time]').val(),
			event_location:$('input[name=event_location]').val(),
			content:nicEditors.findEditor('editor1').getContent(),
			picture:$('select[name=picture]').val(),
			status:$('select[name=status]').val(),
			type:$('input[name=type]').val(),
			id:$('input[name=id]').val()
		}
		
		if(data.content.replace(/ /g,'') == '<br>' || data.content.replace(/ /g,'') == '<br/>')
			data.content = '';
		
		$.ajax({
			url:Conf.requestUrl+'&m=saveEvent',
			type:'POST',
			data: data,
			dataType:'json',
			success: function(d) {
				if(d && typeof d == 'object' && d.status == 1) {
					$('.loading').hide();
					window.location.href = Conf.Url+'/?'+Conf.modulParam+'=kokkutulekud&aasta='+data.year;
				} else
					alert('error');
					$('.loading').hide();
			},
			error: function() {
				alert('error');
				$('.loading').hide();
			}
		});
	}
	
	function deleteEvent(){
		
		var answer = confirm("Kas oled ikka päris kindel, et soovid kokkutulekut kustutada?")
		if (answer){
			$('.loading').show();
			
			var data = {
				id:$('input[name=id]').val()
			}
			
			$.ajax({
				url:Conf.requestUrl+'&m=deleteEvent',
				type:'POST',
				data: data,
				dataType:'json',
				success: function(d) {
					if(d && typeof d == 'object' && d.status == 1) {
						$('.loading').hide();
						window.location.href = Conf.Url+'/?'+Conf.modulParam+'=kokkutulekud';
					} else
						alert('error');
						$('.loading').hide();
				},
				error: function() {
					alert('error');
					$('.loading').hide();
				}
			});
		}
	}
});