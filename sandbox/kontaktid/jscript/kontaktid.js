/*
 * Oviir's family contacts
 * main.js
 * 
 * Creator: Mihkel Oviir
 * 04.2011
 * 
 */

// flexi setup
function setFlex(){
	// flexigrid table
	$('#contacts').flexigrid({
		url:'request.php?'+rnd(),
		dataType:'json',
		colModel :[
			{
				display:'Nr',
				name:'id',
				width:20,
				sortable:true,
				align:'right'
			},{
				display:'Eesnimi',
				name:'firstname',
				width:80,
				sortable:true,
				align:'left'
			},{
				display:'Perenimi',
				name:'lastname',
				width:80,
				sortable:true,
				align:'left'
			},{
				display:'Aadress',
				name:'address',
				width:300,
				sortable:true,
				align:'left'
			},{
				display:'E-post',
				name:'email',
				width:150,
				sortable:true,
				align:'left'
			},{
				display:'Telefon',
				name:'phone',
				width:100,
				sortable:true,
				align:'left'
			},{
				display:'Esivanem',
				name:'ancestor',
				width:60,
				sortable:true,
				align:'left'
			},{
				display:'Kutse?',
				name:'active',
				width:30,
				sortable:false,
				align:'left'
			},{
				display:'Muuda',
				name:'change',
				width:40,
				sortable:false,
				align:'center'
			},{
				display:'Kustuta',
				name:'delete',
				width:40,
				sortable:false,
				align:'center'
			}
		],
		sortname:'ancestor',
		sortorder:'asc',
		usepager:false,
		showTableToggleBtn:false,
		showToggleBtn:false,
		height:'auto',
		singleSelect:true,
		onSubmit: function(){
			$('#contacts').flexOptions({params:[{name:'c', value: 'kontaktid'},{name:'m', value: 'getContacts'},{name:'filter', value: $('#filter').serialize()}]});
			return true;
		}
	});
}

// get print content
function doPrint(){
	window.open('request.php?c=kontaktid&m=getPrintContent&data='+encodeURIComponent($('#filter').serialize())+'&'+rnd(), 'Printpage');
}

// filter
function doFilter(){
	$('#contacts').flexReload();
}

// reset
function doReset(){
	$('#filter input').val('');
	$('#filter select').val('*')
	$('#contacts').flexReload();
}

function doDelete(id){
	if(confirm('Kustutan kontakti?')){
		$.post('request.php?'+rnd(), {
			c: 'kontaktid',
			m: 'delContact',
			id: id
			},
			function(response){
				if(response.status=='1'){
					$('#contacts').flexReload();
				}
				else {
					alert('sorry');
				}
			},'json'
		);
	}
}

function doDetail(id){
	if(id != -1){
		$('#detailform input[name=firstname]').val($('#row'+id+' td[abbr=firstname] div').text());
		$('#detailform input[name=lastname]').val($('#row'+id+' td[abbr=lastname] div').text());
		$('#detailform input[name=address]').val($('#row'+id+' td[abbr=address] div').text());
		var email = $('#row'+id+' td[abbr=email] div').text();
		email = email.length < 5 ? '' : email;
		$('#detailform input[name=email]').val(email);
		$('#detailform input[name=phone]').val($('#row'+id+' td[abbr=phone] div').text());
		$('#detailform select[name=ancestor]').val($('#row'+id+' td[abbr=ancestor] div').text());
		if($('#row'+id+' td[abbr=active] div').text()=='ei'){
			$('#detailform select[name=active]').val(0);
		} else {
			$('#detailform select[name=active]').val(1);
		}
	}
	$('#detailform input[name=id]').val(id);
	$('#contact_details').fadeIn('fast');
	$('#overlaymask').fadeIn('fast');
	$('#detailform input:first').focus();
}

function doCancel(){
	$('.overlays').fadeOut('fast');
	$('#copy textarea').val('');
	$('#contact_details form input').val('');
	$('#contact_details form select').val($('#contact_details form select option:first').val());
}

function doSave(){
	// validate form
	if($('#detailform #firstname').val()!='' && $('#detailform #lastname').val()!=''){
		var email = $('#detailform #email').val();
		var valid = true;
		if(email != ''){
			valid = validate(email);
		}
		if(valid){
			$.post('request.php?'+rnd(), {
				c: 'kontaktid',
				m: 'saveContact',
				data: $('#detailform').serialize()
				},
				function(response){
					if(response.status=='1'){
						$('#contacts').flexReload();
					}
					else {
						alert('sorry');
					}
				},'json'
			);
			doCancel();
		}
	}
}

function validate(address) {
	var reg = /^([A-Za-z0-9_\-\.])+\@([A-Za-z0-9_\-\.])+\.([A-Za-z]{2,4})$/;
	if(reg.test(address) == false) {
		return false;
	} else {
		return true;
	}
}

function doCopy(col){
	var str = '';
	$('#contacts tbody tr').each(function(){
		$(this).children().each(function(){
			if($(this).attr('abbr')==col){
				var text = $(this).children('div:first').text();
				if(validate(text)){
					str+=text+', ';
				}
			}
		});
	});
	$('#copy textarea').val(str);
	$('#copy').fadeIn('fast');
	$('#overlaymask').fadeIn('fast');
}

//random string
function rnd(){ return String((new Date()).getTime()).replace(/\D/gi,''); }

$(document).ready(function() {
	if($.browser.msie && $.browser.version == '7.0'){
		$('#copy').html('Oeh, ka IE on juba 9 versiooni juures, rääkimata sellest, et on olemas palju etemaid veebisirvijaid kui see, mida sina kasutad. Tee omad järeldused...');
		$('#copy').show();
		$('#overlaymask').show();
	}

	$('#filter_container').show();
	$('.overlays').hide();
	setFlex();
	$('#filter input:first').focus();
	
	$('#logout').click(function(e){
		e.preventDefault();
		$.post('request.php?'+rnd(),{
				c:'login',
				m:'setLogout'
			},
			function(response){
				if(response.status=='1'){ //if correct login detail
					window.location.href = '?logout';
				}
				else {
					alert('piip');
				}
			},
			'json'
		);
	});
});