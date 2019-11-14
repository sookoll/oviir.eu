$.expr[":"].containsNoCase = function(el, i, m) {
	var search = m[3];
	if (!search)
		return false;
	return eval("/" + search + "/i").test($(el).text());
};

$(function() {
	if(typeof nicEditors !== 'undefined'){
		var opts = {
			iconsPath : '/libs/nicEdit/nicEditorIcons.gif',
			buttonList : ['fontSize','bold','italic','underline','strikeThrough','left','center','right','justify','ol','ul','subscript','superscript','hr','forecolor','link'],
			maxHeight : 300
		}
		nicEditors.allTextAreas(opts);
		
		// inputs tips
		$('input.text').focus(function(){
			$(this).parent().append('<em class="tip">'+$(this).attr('placeholder')+'</em>');
		}).blur(function(){
			$(this).parent().find('em.tip').remove();
		});
		
	}
	
	$('select').selectBox();
	
	$('html').click(function(){
    	$('ul.memberslist').fadeOut('fast');
    });
	
	getPeopleList();
	
	$('input[name=related_name]').live('keyup',function(e) {
		var keycode = (e.keyCode ? e.keyCode : e.which);
		$('ul.memberslist li').hide();
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
		deleteDocument();
	});
	
	$('a.save').click(function(e){
		e.preventDefault();
		saveDocumentData();
	});
	
	// change YouTube video link to embbed link
	if($('input[name=type]').val()=='video'){
		var note = $('em.video-note').html();
		
		$('input[name=link]').blur(function(){
			var link = $('input[name=link]').val();
			if($.trim(link).length>10 && validateUrl($.trim(link))){
				var regex = /(\?v=|\&v=|\/\d\/|\/embed\/|\/v\/|\.be\/)([a-zA-Z0-9\-\_]+)/;
			    var regexyoutubeurl = link.match(regex);
			    if (regexyoutubeurl){
			         $(this).val('http://www.youtube.com/embed/'+regexyoutubeurl[2]);
			         $('em.video-note').html('Aadress õige!');
			    }else{
			    	$(this).val('');
					$('em.video-note').html(note);
			    }
			}else{
				$(this).val('');
				$('em.video-note').html(note);
			}
		});
	}
	
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
					$('ul.memberslist').html(html);
				} else
					alert('error');
			},
			error: function() {
				alert('error');
			}
		});
	}
	
	function deleteDocument(){
		var answer = confirm("Kas oled ikka päris kindel, et soovid seda vara kustutada?")
		if (answer){
			$('.loading').show();
			
			var data = {
				id:$('input[name=id]').val(),
				type:$('input[name=type]').val()
			}
			
			$.ajax({
				url:Conf.requestUrl+'&m=deleteDocument',
				type:'POST',
				data: data,
				dataType:'json',
				success: function(d) {
					if(d && typeof d == 'object' && d.status == 1) {
						$('.loading').hide();
						window.location.href = Conf.Url+'/?'+Conf.modulParam+'=varasalv';
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
	
	function saveDocumentData(){
			
		$('.loading').show();
		
		var data = {
			id:$('input[name=id]').val(),
			type:$('input[name=type]').val(),
			title:$('input[name=title]').val(),
			description:nicEditors.findEditor('editor1').getContent(),
			related_with:$('input[name=related_with]').val()
		};
		
		if(data.type == 'link' || data.type == 'video'){
			data.link = $('input[name=link]').val();
			if($.trim(data.link).length<10 || !validateUrl($.trim(data.link))){
				alert('Nõutud väli on täitmata või ei ole sobiv URL');
				return false;
			}
		}
		
		$.ajax({
			url:Conf.requestUrl+'&m=saveDocumentData',
			type:'POST',
			data: data,
			dataType:'json',
			success: function(d) {
				if(d && typeof d == 'object' && d.status == 1) {
					$('.loading').hide();
					window.location.href = Conf.Url+'/?'+Conf.modulParam+'=varasalv&id='+d.id;
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
	
	function validateUrl(value){
		return /^(https?|ftp):\/\/(((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:)*@)?(((\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5]))|((([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.)+(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.?)(:\d*)?)(\/((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)+(\/(([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)*)*)?)?(\?((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)|[\uE000-\uF8FF]|\/|\?)*)?(\#((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)|\/|\?)*)?$/i.test(value);
	}
}); 