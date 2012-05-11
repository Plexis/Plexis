$(document).ready(function() {
	
	/** Accordion Menu */
	$('.menu').initMenu();
	
	/** Slide Effect */
	$('.menu li a').slideList();
	
	/** Scroll Effect */
	$('a[href*=#]').bind("click", function(event) {
		event.preventDefault();
		var target = $(this).attr('href');
		
		$('html,body').animate({
			scrollTop: $(target).offset().top
		}, 1000 , function () {
			// location.hash = target;
			// finished scrolling
		});
	});
	
	/** Form Elements */
	$("select, input:checkbox, input:text, input:password, input:radio, input:file, textarea").uniform();
	
	/** Closable Alert Boxes */
	$('span.hide').click(function() {
		$(this).parent().slideUp();					   
	});	
	 
	/** Dropdown-menu for left sidebar */
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
	
	/** Closable Content Boxes */
	$('.block-border .block-header span').click(function() {
		if($(this).hasClass('closed')) {
			$(this).removeClass('closed');
		} else {
			$(this).addClass('closed');
		}
		
		$(this).parent().parent().children('.block-content').slideToggle();
	});

	/** Tooltips */
	$('a[rel=tooltip]').tipsy({fade: true});
	$('a[rel=tooltip-bottom]').tipsy({fade: true});
	$('a[rel=tooltip-right]').tipsy({fade: true, gravity: 'w'});
	$('a[rel=tooltip-top]').tipsy({fade: true, gravity: 's'});
	$('a[rel=tooltip-left]').tipsy({fade: true, gravity: 'e'});
	
	$('a[rel=tooltip-html]').tipsy({fade: true, html: true});
	
	$('div[rel=tooltip]').tipsy({fade: true});
    
    /** Information Model */
	CmsDialog = $("#cms-info-dialog").dialog({
		autoOpen: false,  
		modal: true, 
		width: 500,
        resizable: false,
		buttons: [{
			text: "Close", 
			click: function() {
				$( this ).dialog( "close" );
			}
		}]
	});
    
    $('#open-info-dialog').click(function() {
        // Open the Modal Window
		CmsDialog.dialog("open");
    });
});

/**
 * Accordion Menu
 */

jQuery.fn.initMenu = function() {  
    return this.each(function(){
        var theMenu = $(this).get(0);
        
        $('li:has(ul)',this).each(function() {
			$('>a', this).append("<span class='arrow'></span>");
		});
        
        $('.sub', this).hide();
        $('li.expand > .sub', this).show();
        $('li.expand > .sub', this).prev().addClass('active');
        $('li a', this).click(
            function(e) {
                e.stopImmediatePropagation();
                var theElement = $(this).next();
                var parent = this.parentNode.parentNode;
                if($(this).hasClass('active-icon')) {
                	$(this).addClass('non-active-icon');
                	$(this).removeClass('active-icon');
                }else{
                	$(this).addClass('active-icon');
                	$(this).removeClass('non-active-icon');
                }
                if($(parent).hasClass('noaccordion')) {
                    if(theElement[0] === undefined) {
                        window.location.href = this.href;
                    }
                    $(theElement).slideToggle('normal', function() {
                        if ($(this).is(':visible')) {
                            $(this).prev().addClass('active');
                        }
                        else {
                            $(this).prev().removeClass('active');
                            $(this).prev().removeClass('active-icon');
                        }    
                    });
                    return false;
                }
                else {
                    if(theElement.hasClass('sub') && theElement.is(':visible')) {
                        if($(parent).hasClass('collapsible')) {
                            $('.sub:visible', parent).first().slideUp('normal', 
                                function() {
                                    $(this).prev().removeClass('active');
                                    $(this).prev().removeClass('active-icon');
                                }
                            );
                            return false;  
                        }
                        return false;
                    }
                    if(theElement.hasClass('sub') && !theElement.is(':visible')) {         
                        $('.sub:visible', parent).first().slideUp('normal', function() {
                            $(this).prev().removeClass('active');
                            $(this).prev().removeClass('active-icon');
                        });
                        theElement.slideDown('normal', function() {
                            $(this).prev().addClass('active');
                        });
                        return false;
                    }
                }
            }
        );
	});
};

/**
 * Sliding Entrys
 */

(function($){
	$.fn.slideList = function(options) {
		return $(this).each(function() {
			var padding_left = $(this).css("padding-left");
      		var padding_right = $(this).css("padding-right");

			$(this).hover(
				function() {
					$(this).animate({
						paddingLeft:parseInt(padding_left) + parseInt(5) + "px",
						paddingRight: parseInt(padding_right) - parseInt(5) + "px"
					}, 130);
				},
				function() {
					bc_hover = $(this).css("background-color");
					$(this).animate({
						paddingLeft: padding_left,
						paddingRight: padding_right
					}, 130);
				}
			);
      });
	};
})(jQuery);

/**
 * Placeholder
 */

$('[placeholder]').focus(function() {
  var input = $(this);
  if (input.val() == input.attr('placeholder')) {
    input.val('');
    input.removeClass('placeholder');
  }
}).blur(function() {
  var input = $(this);
  if (input.val() == '' || input.val() == input.attr('placeholder')) {
    input.addClass('placeholder');
    input.val(input.attr('placeholder'));
  }
}).blur().parents('form').submit(function() {
  $(this).find('[placeholder]').each(function() {
    var input = $(this);
    if (input.val() == input.attr('placeholder')) {
      input.val('');
    }
  })
});

/**
 * Form Reset
 * Resets the form data.  Causes all form elements to be reset to their original value.
 */
$.fn.resetForm = function() {
	return this.each(function() {
		// guard against an input with the name of 'reset'
		// note that IE reports the reset function as an 'object'
		if (typeof this.reset == 'function' || (typeof this.reset == 'object' && !this.reset.nodeType))
			this.reset();
	});
};

/**
 * Tabs
 */

(function($){
	$.fn.createTabs = function(){
		var container = $(this);
		
		container.find('.tab-content').hide();
		container.find("ul.tabs li:first").addClass("active").show();
		container.find(".tab-content:first").show();
		
		container.find("ul.tabs li").click(function() {
	
			container.find("ul.tabs li").removeClass("active");
			$(this).addClass("active");
			container.find(".tab-content").hide();
	
			var activeTab = $(this).find("a").attr("href");
			$(activeTab).fadeIn();
			return false;
		});
		
	};
})(jQuery);