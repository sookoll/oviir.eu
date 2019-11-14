$(function(){
	$('select.box').selectBox();
	
	$('a[href=toggle],a[href=cancel]').click(function(e){
		e.preventDefault();
		$('select option').removeAttr('selected').find('option:first').attr('selected','selected');
		$('select[name=forum] option:not(:first)').remove();
		$('select.box').selectBox('destroy').selectBox();
		$('div.add input[name=title]').val('');
		$('div.add,a[href=toggle]').toggle();
	});
	
	$('a[href=save]').click(function(e){
		e.preventDefault();
		saveForumData();
	});
	
	$('a[href=del]').click(function(e){
		e.preventDefault();
		deleteForum();
	});
	
	$('select[name=cat]').change(function(){
		$('select[name=forum] option:not(:first)').remove();
		if(typeof $('select[name=opts_'+$(this).val()+']').html() != 'undefined')
			$('select[name=forum]').append($('select[name=opts_'+$(this).val()+']').html());
		$('select[name=forum]').selectBox('destroy').selectBox();
	});
	
	$('select[name=forum]').change(function(){
		if($(this).val()!='')
			$('div.add input[name=title]').val($(this).find('option:selected').text());
		else
			$('div.add input[name=title]').val('');
	});
	
	function deleteForum(){
		if($.trim($('select[name=forum]').val()).length==0){
			alert('Vali alamfoorum!');
			return false;
		}
		var answer = confirm("Kas oled ikka päris kindel, et soovid sugulast süsteemist kustutada?");
		if (answer){
			$('.loading').show();
			
			var data = {
				forum:$('select[name=forum]').val(),
				action:'delete'
			};
			
			$.ajax({
				url:Conf.requestUrl+'&m=editSubForum',
				type:'POST',
				data: data,
				dataType:'json',
				success: function(d) {
					if(d && typeof d == 'object' && d.status == 1) {
						$('.loading').hide();
						window.location.href = Conf.Url+'/?'+Conf.modulParam+'=foorum';
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
	
	function saveForumData(){
		$('.loading').show();
		
		var data = {
			category:$('select[name=cat]').val(),
			forum:$('select[name=forum]').val(),
			title:$('input[name=title]').val(),
			action:'edit'
		};
		
		if($.trim(data.category).length==0 || $.trim(data.title).length==0){
			alert('Väli tühi!');
			$('.loading').hide();
			return false;
		}
		
		$.ajax({
			url:Conf.requestUrl+'&m=editSubForum',
			type:'POST',
			data: data,
			dataType:'json',
			success: function(d) {
				if(d && typeof d == 'object' && d.status == 1) {
					$('.loading').hide();
					window.location.href = Conf.Url+'/?'+Conf.modulParam+'=foorum';
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