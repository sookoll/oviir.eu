$(function(){
	
	// topic edit/add page
	if(typeof nicEditors !== 'undefined'){
		var opts = {
			iconsPath : '/libs/nicEdit/nicEditorIcons.gif',
			buttonList : ['fontSize','bold','italic','underline','strikeThrough','left','center','right','justify','ol','ul','subscript','superscript','hr','forecolor','link','blockquote'],
			maxHeight : 300,
			xhtml: true
		}
		nicEditors.allTextAreas(opts);
		
		// inputs tips
		$('input.text').focus(function(){
			$(this).parent().append('<em class="tip">'+$(this).attr('placeholder')+'</em>');
		}).blur(function(){
			$(this).parent().find('em.tip').remove();
		});
		
	}
	
	$('a[href=save]').click(function(e){
		e.preventDefault();
		saveTopic();
	});
	
	$('a[href=del]').click(function(e){
		e.preventDefault();
		deleteTopic();
	});
	
	function deleteTopic(){
		
		var answer = confirm("Kas oled ikka päris kindel, et soovid sugulast süsteemist kustutada?");
		if (answer){
			$('.loading').show();
			
			var data = {
				forum:$('select[name=forum]').val(),
				id:$('select[name=id]').val()
			};
			
			$.ajax({
				url:Conf.requestUrl+'&m=deleteTopic',
				type:'POST',
				data: data,
				dataType:'json',
				success: function(d) {
					if(d && typeof d == 'object' && d.status == 1) {
						$('.loading').hide();
						window.location.href = d.href;
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
	
	function saveTopic(){
		$('.loading').show();
		
		var data = {
			id:$('input[name=id]').val(),
			forum:$('input[name=forum]').val(),
			title:$('input[name=title]').val(),
			type:$('input[name=type]').val(),
			content:nicEditors.findEditor('editor1').getContent()
		};
		
		if($('input[name=sticky]').length>0)
			data.sticky = $('input[name=sticky]').is(':checked')?1:0;
		if($('input[name=status]').length>0)
			data.status = $('input[name=status]').is(':checked')?'closed':'published';
		if($('input[name=topic]').length>0)
			data.topic = $('input[name=topic]').val();
		
		if($.trim(data.title).length==0 || $.trim(data.content).length==0){
			alert('Väli tühi!');
			$('.loading').hide();
			return false;
		}
		
		$.ajax({
			url:Conf.requestUrl+'&m=saveTopic',
			type:'POST',
			data: data,
			dataType:'json',
			success: function(d) {
				if(d && typeof d == 'object' && d.status == 1) {
					$('.loading').hide();
					window.location.href = d.href;
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
});