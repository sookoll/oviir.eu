$(function(){
	$("img.lazy").lazyload();
	
	// if we have something behind dash, then visualize image
	var hash = window.location.hash;
	hash = hash.replace(/^#/,'');
	if(hash!=''){
		$('li a[name="'+hash+'"]').parent().css('border-color','#ef5d9b');
	}
});