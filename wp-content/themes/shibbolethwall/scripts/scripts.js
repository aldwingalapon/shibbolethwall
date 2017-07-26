$(document).ready(function(){
	$('[data-toggle="tooltip"]').tooltip({html: true}); 
	
	$(function() {
		$('.lazy-image').lazy();
	});	
});