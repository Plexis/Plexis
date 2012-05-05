
$(document).ready(function() {
	
	/*
	 * Accordion Menu
	 */
	$('.menu').initMenu();
	
	
	/*
	 * Slide Effect
	 */
	$('.menu li a').slideList();
	
	
	/*
	 * Scroll Effect
	 */
	/* $('a[href*=#]').bind("click", function(event) {
		event.preventDefault();
		var target = $(this).attr("href");
		
		$('html,body').animate({
			scrollTop: $(target).offset().top
		}, 1000 , function () {
			//location.hash = target;
			// finished scrolling
		});
	}); */
	
	
	/*
	 * Form Elements
	 */
	$("select, input:checkbox, input:text, input:password, input:radio, input:file, textarea").uniform();
	
	
	/*
	 * Closable Alert Boxes
	 */
	$('span.hide').click(function() {
		$(this).parent().slideUp();					   
	});	
	
	
	/*
	 * Toolbox
	 */
	$('.toolbox-action').click(function() {
		$('.toolbox-content').fadeOut();
		$(this).next().fadeIn();
		
        return false;
	});
	
	$('.close-toolbox').click(function() {
		$(this).parents('.toolbox-content').fadeOut();
	});
	
    
	/*
	 * Dropdown-menu for left sidebar
	 */
	$('.user-button').click(function() {
		$('.dropdown-username-menu').slideToggle();
	});
	
	$(document).click(function(e){
		if (!$(e.target).is('.user-button, .arrow-link-down, .dropdown-username-menu *')) {
			$('.dropdown-username-menu').slideUp();
		}
	});
	
	var ddumTimer;
	
	$('.user-button, ul.dropdown-username-menu').mouseleave(function(e) {
		ddumTimer = setTimeout(function() {
			$('.dropdown-username-menu').slideUp();
		},400);
	});
	
	$('.user-button, ul.dropdown-username-menu').mouseenter(function(e) {
		clearTimeout(ddumTimer);
	});
	

	/*
	 * Closable Content Boxes
	 */
	$('.block-border .block-header span').click(function() {
		if($(this).hasClass('closed')) {
			$(this).removeClass('closed');
		} else {
			$(this).addClass('closed');
		}
		
		$(this).parent().parent().children('.block-content').slideToggle();
	});

	
	/*
	 * Tooltips
	 */
	$('a[rel=tooltip]').tipsy({fade: true});
	$('a[rel=tooltip-bottom]').tipsy({fade: true});
	$('a[rel=tooltip-right]').tipsy({fade: true, gravity: 'w'});
	$('a[rel=tooltip-top]').tipsy({fade: true, gravity: 's'});
	$('a[rel=tooltip-left]').tipsy({fade: true, gravity: 'e'});
	
	$('a[rel=tooltip-html]').tipsy({fade: true, html: true});
	
	$('div[rel=tooltip]').tipsy({fade: true});
});