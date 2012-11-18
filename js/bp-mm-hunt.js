jQuery(document).ready(function($){
	$('.avatar').each(function(){
		var linkelement ='';
		var linkprofile ='';
		if( $(this).hasClass('mysterious') ) {
			
			linkelement = $(this).parents().find('a').first();
			linkprofile = linkelement.attr('href') +'profile/change-avatar';
			
			linkelement.attr('href', linkprofile);
			
		}
			
	})
});