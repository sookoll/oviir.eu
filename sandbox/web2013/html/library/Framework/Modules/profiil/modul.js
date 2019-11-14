$.expr[":"].containsNoCase = function(el, i, m) {
	var search = m[3];
	if (!search)
		return false;
	return eval("/" + search + "/i").test($(el).text());
};

$(function(){
	
	// init selectboxes
	$('select').selectBox();
	
	$('html').click(function(){
    	$('ul.memberslist').fadeOut('fast');
    });
	
	// inputs tips
	$('input.text').focus(function(){
		$(this).parent().append('<em class="tip">'+$(this).attr('placeholder')+'</em>');
	}).blur(function(){
		$(this).parent().find('em.tip').remove();
	});
	
	getPeopleList();
	
	$('input[name=related_name]').live('keyup',function(e) {
		var keycode = (e.keyCode ? e.keyCode : e.which);
		$('ul.memberslist li').hide();
		//$('ul.memberslist').hide();
		if($(this).val().length>1) {
			var s = $(this).val();
			$('li:containsNoCase("' + s + '")').each( function() {
				$(this).show();
			});
			$('ul.memberslist').fadeIn('fast');
		}else{
			$('input[name=related_with]').val('');
			$('ul.memberslist').fadeOut('fast');
		}
	});
	
	$('ul.memberslist li a').live('click',function(e){
		e.preventDefault();
		$('input[name=related_name]').val($(this).attr('rel'));
		$('input[name=related_with]').val($(this).parent().attr('rel'));
	});
	
	$('a.delete').click(function(e){
		e.preventDefault();
		deleteMember();
	});
	
	$('a.save').click(function(e){
		e.preventDefault();
		saveMemberData();
	});
	
	
	function getPeopleList(){
		
		$.ajax({
			url:Conf.requestUrl+'&m=getMembersList',
			type:'POST',
			data: {},
			dataType:'json',
			success: function(d) {
				if(d && typeof d == 'object' && d.status == 1) {
					var html = '';
					for(var i in d.data){
						html += d.data[i].datum==''?'<li rel="'+d.data[i].id+'"><a href="lisa" rel="'+d.data[i].name+'">'+d.data[i].name+'</a></li>':'<li rel="'+d.data[i].id+'"><a href="lisa" rel="'+d.data[i].name+'">'+d.data[i].name+' '+d.data[i].datum+'</a></li>';
					}
					$('#profile ul.memberslist').html(html);
				} else
					alert('error');
			},
			error: function() {
				alert('error');
			}
		});
	}
	
	function deleteMember(){
		var answer = confirm("Kas oled ikka päris kindel, et soovid sugulast süsteemist kustutada?")
		if (answer){
			$('.loading').show();
			
			var data = {
				id:$('input[name=id]').val()
			}
			
			$.ajax({
				url:Conf.requestUrl+'&m=deleteMember',
				type:'POST',
				data: data,
				dataType:'json',
				success: function(d) {
					if(d && typeof d == 'object' && d.status == 1) {
						$('.loading').hide();
						window.location.href = Conf.Url+'/?'+Conf.modulParam+'=oviirid';
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
	
	function saveMemberData(){
			
		$('.loading').show();
		var settings = 0;
		if($('input[name=settings]').length>0 && $('input[name=settings]').val() == 1)
			settings = 1;
		var data = {};
		
		// general data
		if(settings == 0){
			data = {
				id:$('input[name=id]').val(),
				first_name:$('input[name=first_name]').val(),
				last_name:$('input[name=last_name]').val(),
				birth:$('input[name=birth]').val(),
				death:$('input[name=death]').val(),
				relation:$('select[name=relation]').val(),
				ancestor:$('select[name=ancestor]').val(),
				related_with:$('input[name=related_with]').val(),
				related_name:$('input[name=related_name]').val(),
				address:$('input[name=address]').val(),
				email:$('input[name=email]').val(),
				phone:$('input[name=phone]').val()
			}
		}
		// settings
		else if(settings == 1){
			data = {
				id:$('input[name=id]').val(),
				uname:$('input[name=uname]').val(),
				passwoord:$('input[name=passwoord]').val(),
				passwoord2:$('input[name=passwoord2]').val(),
				invitation:$('select[name=invitation]').val(),
				notification:$('select[name=notification]').val(),
				level:$('select[name=level]').val(),
				status:$('select[name=status]').val()
			}
			
			if(data.passwoord && data.passwoord.length>0 && (data.passwoord.length<6 || data.passwoord !== data.passwoord2)){
				alert('Paroolid ei sobi!');
				$('.loading').hide();
				return;
			}
		}
		
		$.ajax({
			url:Conf.requestUrl+'&m=saveMemberData',
			type:'POST',
			data: data,
			dataType:'json',
			success: function(d) {
				if(d && typeof d == 'object' && d.status == 1) {
					$('.loading').hide();
					window.location.href = Conf.Url+'/?'+Conf.modulParam+'=profiil&user='+d.id;
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
