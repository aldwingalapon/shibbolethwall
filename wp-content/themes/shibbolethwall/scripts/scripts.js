// Avoid `console` errors in browsers that lack a console.
(function() {
    var method;
    var noop = function () {};
    var methods = [
        'assert', 'clear', 'count', 'debug', 'dir', 'dirxml', 'error',
        'exception', 'group', 'groupCollapsed', 'groupEnd', 'info', 'log',
        'markTimeline', 'profile', 'profileEnd', 'table', 'time', 'timeEnd',
        'timeline', 'timelineEnd', 'timeStamp', 'trace', 'warn'
    ];
    var length = methods.length;
    var console = (window.console = window.console || {});

    while (length--) {
        method = methods[length];

        // Only stub undefined methods.
        if (!console[method]) {
            console[method] = noop;
        }
    }
}());

$(document).ready(function(){
	$('[data-toggle="tooltip"]').tooltip({html: true}); 
	
	$(function() {
		$('.lazy-image').lazy();
	});
	
	/*
	* back to top button
	*/ 
	$(window).scroll(function(){
	  var offset = 800,
	  //footerHeight = $('footer').height() + 80,
	  //browser window scroll (in pixels) after which the "back to top" link opacity is reduced
	  offset_opacity = 1000;
	  //grab the "back to top" link
	  $back_to_top = $('.back_to_top-button');
	  ( $(this).scrollTop() > offset ) ? $back_to_top.addClass('button_visible') : $back_to_top.removeClass('button_visible fade-out');
	  if( $(this).scrollTop() > offset_opacity ) { 
		$back_to_top.addClass('fade-out');
	  }
	});

	/*
	* Smooth Scroll
	*/
	$('a.smooth-scroll[href^="#"]').on('click',function (e) {
	  e.preventDefault();
	  var target = this.hash;
	  var $target = $(target);
	  if(target == '#accordion') {
		// do nothing
	  } else if(target.length == 0) {
		$('html,body').animate({
		  scrollTop: 0
		}, 1200);
	  } else {
		$('html, body').stop().animate({
			'scrollTop': $target.offset().top - 150
		}, 1200, 'swing');
	  }
	});	
});

$(document).ready(function(){
    $("#main-header .primary-menu li a").click(function(evn){
        evn.preventDefault();
		var headerHeight = $('header').outerHeight();
		var divPos = $(this.hash).offset().top - (headerHeight);
		console.log(divPos);
		
        $('html,body').scrollTo(divPos, 800, {easing:'swing'}); 
		if($(window).width()<1000){
			$('.c-hamburger').click();
		}
    });

	
    var aChildren = $("#menu-main-menu li").children(); // find the a children of the list items
    var aArray = []; // create the empty aArray
    for (var i=0; i < aChildren.length; i++) {    
        var aChild = aChildren[i];
        var ahref = $(aChild).attr('href');
        aArray.push(ahref);
    } // this for loop fills the aArray with attribute href values

    $(window).scroll(function(){
        var windowPos = $(window).scrollTop(); // get the offset of the window from the top of page
        var windowHeight = $(window).height(); // get the height of the window
        var docHeight = $(document).height();
		var headerHeight = $('header').outerHeight();

        for (var i=0; i < aArray.length; i++) {
            var theID = aArray[i];
           // console.log(theID);
            var divPos = $(theID).offset().top - (headerHeight); // get the offset of the div from the top of page
            var divHeight = $(theID).height(); // get the height of the div in question
            if (windowPos >= divPos && windowPos < (divPos + divHeight - 5)) {
            	if(theID =='#our-approach'){console.log(  ' ADDING ');}
                $("a[href='" + theID + "']").addClass("nav-active");
                 
            } else 	{
            	if(theID =='#our-approach'){console.log(  ' removing ');}
           		$("a[href='" + theID + "']").removeClass("nav-active");
            	
            }
        }

        if(windowPos + windowHeight == docHeight) {
            if (!$("nav li:last-child a").hasClass("nav-active")) {
                var navActiveCurrent = $(".nav-active").attr("href");
                $("a[href='" + navActiveCurrent + "']").removeClass("nav-active");
                $("nav li:last-child a").addClass("nav-active");
            }
        }
    });
});