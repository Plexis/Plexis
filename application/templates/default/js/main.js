jQuery(document).ready(function(){

        // cycle
		cycle()
		  
		// dropdown menu
		dd_menu();

		// display your tweets info @ http://code.google.com/p/twitterjs/ or http://remysharp.com/2007/05/18/add-twitter-to-your-blog-step-by-step/
		tweets(3,"arminvanbuuren");// 1 = number of tweets 2 = username
		
		// basic jqyuery animate
	    animate();
		
		// login
		login();
		
		// animated scroll to top	
		scroll_top();
		
		// nivo slider, more info @ http://nivo.dev7studios.com/
		nivo_slider();
		
		// lightbox info @ http://www.pirolab.it/pirobox/
	    lightbox();
		
		// preload at he images
		preload();
		
		// activates preloader for non-slideshow images	
	    jQuery(".infade, .footer-gallery, .sidebar-gallery").img_preloader ({delay:200});
		
		// basic jquery equal height, used for the content and sidebar
	    equalHeight(jQuery(".e-col, ul.portfolio-box-2 li"));
		

}); 
        function cycle() {
			jQuery('.sidebar-testimonials').cycle({
				fx: 'fade',
				speed:    500, 
    			timeout:  5000 
			});
		}
        function equalHeight(group) {
			tallest = 0;
			group.each(function() {
				thisHeight = jQuery(this).height();
				if(thisHeight > tallest) {
					tallest = thisHeight;
				}
			});
			group.height(tallest);
		}

		function preload(){			
			// preload all images
			jQuery.fn.img_preloader = function(options){
				var defaults = {
					repeatedCheck: 500,
					fadeInSpeed: 1000,
					delay:600,
					callback: ''
				};
				var options = jQuery.extend(defaults, options);
				return this.each(function(){
					var imageContainer = jQuery(this),
						images = imageContainer.find('img').css({opacity:0, visibility:'hidden'}),
						imagesToLoad = images.length;				
						imageContainer.operations = {	
							preload: function(){	
								var stopPreloading = true;
								images.each(function(i, event){	
									var image = jQuery(this);
									
									if(event.complete == true){	
										imageContainer.operations.showImage(image);
									}else{
										image.bind('error load',{currentImage: image}, imageContainer.operations.showImage);
									}
									
								});
								return this;
							},showImage: function(image){	
								imagesToLoad --;
								if(image.data.currentImage != undefined) { image = image.data.currentImage;}
														
								if (options.delay <= 0) image.css('visibility','visible').animate({opacity:1}, options.fadeInSpeed);
														 
								if(imagesToLoad == 0){
									if(options.delay > 0){
										images.each(function(i, event){	
											var image = jQuery(this);
											setTimeout(function(){	
												image.css('visibility','visible').animate({opacity:1}, options.fadeInSpeed);
											},
											options.delay*(i+1));
										});
										
										if(options.callback != ''){
											setTimeout(options.callback, options.delay*images.length);
										}
									}else if(options.callback != ''){
										(options.callback)();
									}
								}
							}
						};
						imageContainer.operations.preload();
					});
				}
		}

		function dd_menu(){	
            //dropdown menu
			jQuery("ul#header-nav li, ul#header-nav li a span").hover(function(){
				jQuery(this).find('ul:first').css({visibility: "visible",display: "none"}).animate({height: 'toggle'}, 400);
			}, function(){
				jQuery('ul:first',this).css('visibility', 'hidden');
			});
		
			// add an active class to the parent li
			jQuery(".second").hover(function(){
				jQuery(this).parent("li").addClass("activeli");							
			}, function(){
				jQuery(this).parent("li").removeClass("activeli");	
			});
			// animate the links in the dropdown
			jQuery(".second li a").hover(function(){
				jQuery(this).stop().animate({paddingLeft: "17px", width: "123px"}, 200);				   
			},function(){ 
				jQuery(this).stop().animate({paddingLeft: "12px" , width: "128px"}, 200);	
			});	
		}

		function animate(){
			// read more links 
			jQuery(".readmore").hover(function(){
				jQuery(this).stop().animate({width: "156px"}, 200);				   
			},function(){ 
				jQuery(this).stop().animate({width: "62px"}, 200);	
			});
			
			// footer links
			jQuery("ul.list-footer li a").hover(function(){
				jQuery(this).stop().animate({paddingLeft: "10px", width: "140px"}, 200);				   
			},function(){ 
				jQuery(this).stop().animate({paddingLeft: "0px" , width: "150px"}, 200);	
			});		
		}

		function login(){
			// center the box
			jQuery.fn.center = function (absolute) {
				return this.each(function () {
					var t = jQuery(this);
					t.css({
						position:    absolute ? 'absolute' : 'fixed', 
						left:        '50%', 
						top:        '50%'
					}).css({
						marginLeft:    '-' + (t.outerWidth() / 2) + 'px', 
						marginTop:    '-' + (t.outerHeight() / 2) + 'px'
					});
					if (absolute) {
						t.css({
							marginTop:    parseInt(t.css('marginTop'), 10) + jQuery(window).scrollTop(), 
							marginLeft:    parseInt(t.css('marginLeft'), 10) + jQuery(window).scrollLeft()
						});
					}
				});
			};
			jQuery("#login-box").center();
			// slide up/down login panel
			jQuery("#login-down, #login-down-2").click(function(){
				jQuery('body').append('<div id="overlay" />');
				jQuery('#overlay').fadeTo('fast', 0.3);
				jQuery("#login-box").stop().fadeIn();
			});
			jQuery("#login-close").click(function(){
				jQuery('#overlay').fadeOut();
				jQuery("#login-box").fadeOut();
			});			
		}
		
		function nivo_slider(){
			jQuery('#slider').nivoSlider({
				effect:'random',
				slices:15,
				animSpeed:500,
				pauseTime:5000,
				directionNav:true, //Next & Prev
				directionNavHide:true, //Only show on hover
				controlNav:true, //1,2,3...
				keyboardNav:true, //Use left & right arrows
				pauseOnHover:true, //Stop animation while hovering
				manualAdvance:false, //Force manual transitions
				captionOpacity:0.8, //Universal caption opacity
				beforeChange: function(){},
				afterChange: function(){},
				slideshowEnd: function(){} //Triggers after all slides have been shown
			});
		}
		
		function lightbox(){	
			//basic lightbox
			jQuery().piroBox({
				my_speed: 300, //animation speed
				bg_alpha: 0.3, //background opacity
				slideShow : true, // true == slideshow on, false == slideshow off
				slideSpeed : 4, //slideshow
				close_all : '.piro_close, .piro_overlay' // add class .piro_overlay(with comma)if you want overlay click close piroBox
			});
			
			// fade the lightboxes when hoverd(adds an tip)
			jQuery(".pirobox,  .pirobox_footer, .pirobox_portfolio").hover(function(){
				jQuery(this).find("img").stop().fadeTo("fast",0.7);
				jQuery(this).append('<span class="lightbox-tip" ></span>');
		    }, function(){
				jQuery(this).find("img").stop().fadeTo("fast",1.0);
			    jQuery("span.lightbox-tip").fadeOut(200);
			});
			
			// removing the loader background
			// this willprevent the showing of the img when hoverd
			jQuery('.imgwrap, .imgwrap-about, .imgwrap-portfolio, .pirobox_footer').hover(function() {
				jQuery(this).css({backgroundImage: "none"});	
			});
			
			// fading in the shadows
			// IE dont like fading stuff, so thsi has an IE fallback
			 if(jQuery.browser.msie){
			 	jQuery("span.shadow-140, span.shadow-225, span.shadow-240, span.shadow-300, span.shadow-460, span.shadow-680, span.shadow-960").show(1);	 
			 }else{
				jQuery("span.shadow-140, span.shadow-225, span.shadow-240, span.shadow-300, span.shadow-460, span.shadow-680, span.shadow-960").delay(1000).fadeIn(1000);	 
			 }			 
		}
		
		function tweets(tws, username){
			getTwitters('tweets', {
				id: username, 
				clearContents: false, // leave the original message in place
				count: tws, 
				withFriends: true,
				ignoreReplies: false,
				newwindow: true,
				template: '<a href="http://twitter.com/%screen_name%">%user_screen_name%</a> said: "%text%" - <span>%time%</span>'

			});
		}
		
		function scroll_top(){
			jQuery('#top').click(function() {
            	jQuery('html, body').animate({scrollTop:0}, 'slow');
            });
        }
	  		
		function switch_view(){
			jQuery("a.switch_thumb").toggle(function(){
				jQuery(this).addClass("swap");
				jQuery("ul.display").fadeOut("fast", function() {
					jQuery(this).fadeIn("fast").removeClass("display").addClass("thumb_view");
				});
			}, function () {
				jQuery(this).removeClass("swap");
				jQuery("ul.thumb_view").fadeOut("fast", function() {
					jQuery(this).fadeIn("fast").addClass("display").removeClass("thumb_view");
				});
			}); 
		}
