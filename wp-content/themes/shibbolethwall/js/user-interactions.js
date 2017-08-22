
$(window).resize(function(){
	var screenWidth = $(document).width();
	if (screenWidth > 970) {
		$('.menu-main-menu-container').show();
	}else{
		$('.menu-main-menu-container').hide();
	}
});

$(document).ready(function(){
	$('#hamburger-menu').on('click', function(){
		$('.menu-main-menu-container').slideToggle();

	});

});

