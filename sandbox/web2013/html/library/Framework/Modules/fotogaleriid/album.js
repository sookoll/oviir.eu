// parseUri 1.2.2
// (c) Steven Levithan <stevenlevithan.com>
// MIT License

function parseUri (str) {
	var	o   = parseUri.options,
		m   = o.parser[o.strictMode ? "strict" : "loose"].exec(str),
		uri = {},
		i   = 14;

	while (i--) uri[o.key[i]] = m[i] || "";

	uri[o.q.name] = {};
	uri[o.key[12]].replace(o.q.parser, function ($0, $1, $2) {
		if ($1) uri[o.q.name][$1] = $2;
	});

	return uri;
};

parseUri.options = {
	strictMode: false,
	key: ["source","protocol","authority","userInfo","user","password","host","port","relative","path","directory","file","query","anchor"],
	q:   {
		name:   "queryKey",
		parser: /(?:^|&)([^&=]*)=?([^&]*)/g
	},
	parser: {
		strict: /^(?:([^:\/?#]+):)?(?:\/\/((?:(([^:@]*)(?::([^:@]*))?)?@)?([^:\/?#]*)(?::(\d*))?))?((((?:[^?#\/]*\/)*)([^?#]*))(?:\?([^#]*))?(?:#(.*))?)/,
		loose:  /^(?:(?![^:@]+:[^:@\/]*@)([^:\/?#.]+):)?(?:\/\/)?((?:(([^:@]*)(?::([^:@]*))?)?@)?([^:\/?#]*)(?::(\d*))?)(((\/(?:[^?#](?![^?#\/]*\.[^?#\/.]+(?:[?#]|$)))*\/?)?([^?#\/]*))(?:\?([^#]*))?(?:#(.*))?)/
	}
};

$(function(){
	
	var delta = {
		h:60,
		w:70+parseInt($('#frame .panel').css('min-width').replace('px',''))
	}
	
	// set height
	var maxH = $(window).height()-delta.h;
	var maxW = $(window).width()-delta.w;
	var newH;
	var newW;
	
	if($('#frame .image').width()>maxW || $('#frame .image').height()>maxH){
		
		// if h is bigger
		if($('#frame .image').height()>maxH){
			newW = (maxH*$('#frame .image').width())/$('#frame .image').height();
			newH = maxH;
		}else{
			newW = $('#frame .image').width();
			newH = $('#frame .image').height();
		}
		
		// if new width is still bigger
		if(newW>maxW){
			newH = (maxW*$('#frame .image').height())/$('#frame .image').width();
			newW = maxW;
		}
		
		$('#frame .image,#frame .image img').height(newH).width(newW);
	}
		
	// prevent disabled links
	$('a.disabled').click(function(e){
		e.preventDefault();
	});
	
	// navigate with keyboard
	$(document).keydown(function(e) {
		e.preventDefault();
		switch(e.keyCode){
			case 37:// left
				if(!$('a.prev').hasClass('disabled'))
					window.location.href = $('a.prev').attr('href');
			break;
			case 38:// up
				
			break;
			case 39:// right
				if(!$('a.next').hasClass('disabled'))
					window.location.href = $('a.next').attr('href');
			break;
			case 40:// down
				
			break;
			case 27:// esc
				if(!$('a.close').hasClass('disabled'))
					window.location.href = $('a.close').attr('href');
			break;
		}
		
	});
	
	// preload prev and next into browser
	var uri = parseUri($('#frame .image img').attr('src'));
	var uriP = parseUri($('#frame a.prev').attr('href'));
	if(typeof uriP == 'object' && uriP.queryKey && uriP.queryKey.item)
		$('.preload').append('<img src="'+$('#frame .image img').attr('src').replace('&item='+uri.queryKey.item,'&item='+uriP.queryKey.item)+'" width="'+newW+'px" height="'+newH+'px">');
	var uriN = parseUri($('#frame a.next').attr('href'));
	if(typeof uriN == 'object' && uriN.queryKey && uriN.queryKey.item)
		$('.preload').append('<img src="'+$('#frame .image img').attr('src').replace('&item='+uri.queryKey.item,'&item='+uriN.queryKey.item)+'">');
	
});
