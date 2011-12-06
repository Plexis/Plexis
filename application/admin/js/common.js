/*
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

/*
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

/*
 * Create Alert Boxes
 */

(function($){
	$.fn.alertBox = function(message, options){
		var settings = $.extend({}, $.fn.alertBox.defaults, options);
		
		this.each(function(i){
			var block = $(this);
			
			var alertClass = 'alert ' + settings.type;
			if (settings.noMargin) {
				alertClass += ' no-margin';
			}
			if (settings.position) {
				alertClass += ' top';
			}
			var alertMessage = '<div id="alertBox-generated" style="display:none" class="' + alertClass + '">' + message + '</div>';
			
			var alertElement = block.prepend(alertMessage);
			
			$('#alertBox-generated').fadeIn();
		});
	};
	
	// Default config for the alertBox function
	$.fn.alertBox.defaults = {
		type: 'info',
		position: 'top',
		noMargin: true
	};
})(jQuery);

/*
 * Remove Alert Boxes
 */

(function($){
	$.fn.removeAlertBoxes = function(message, options){
		var block = $(this);
		
		var alertMessages = block.find('.alert');
		alertMessages.remove();
	};
})(jQuery);

/*
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
	$(this).removeAlertBoxes();
	return this.each(function() {
		// guard against an input with the name of 'reset'
		// note that IE reports the reset function as an 'object'
		if (typeof this.reset == 'function' || (typeof this.reset == 'object' && !this.reset.nodeType))
			this.reset();
	});
};

/*
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
