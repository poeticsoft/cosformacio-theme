/*
 * 	Themes Slider 1.0 - jQuery plugin
 *	written by Ihor Ahnianikov	
 * https://themex.co
 *
 *	Copyright (c) 2013 Ihor Ahnianikov
 *
 *	Built for jQuery library
 *	http://jquery.com
 *
 */
 
(function($) {

	$.fn.themexSlider = function (options) {

		var options = jQuery.extend ({
			speed: 1000,
			pause: 5000,
			effect: 'fade'
		}, options);
	 
		var slider = $(this);
		var list = $(this).children('ul');
		var disabled = false;
		var autoSlide;
		var arrows = slider.find('.arrow');
		var substrate = slider.find('.substrate');
		
		//initialize slider

		function init() {
		
			//init slides		

			if(options.effect == 'slide' && list.children('li').length>1) {

				list.children('li:first-child').clone().appendTo(list);
				list.children('li:last-child').prev('li').clone().prependTo(list);	

			} else {

				list.children('li:first-child').addClass('current');
			}
			
			arrows.click(function() {

				//next slide

				if($(this).hasClass('arrow-left')) {

					animate('left');

				} else {

					animate('right');
				}

				//stop slider

				clearInterval(autoSlide);
				
				return false;
			});

			resize();
			
			setTimeout(function() {

				resize();
			
				//rotate slider

				if(options.pause != 0) {

					rotate();
				}
				
				//show slider

				list.addClass('visible');

			}, 500);
		}
		
		//load slider

		function load() {

			/*

			var $Images = slider.find('.CosSliderImage img');

			$Images.each(function() {

				var $Image = $(this); 

				var $SliderRow = $Image.parents('.row').eq(0);
				var ImageSrc = $Image.attr('src');
				var $CosSliderImage = $SliderRow.find('.CosSliderImage');
				
				$SliderRow.css({
					backgroundImage: 'url(' + ImageSrc + ')'
				});

				$CosSliderImage.remove();

				$SliderRow.wrapInner('<div class="Content"></div>');
			});

			*/
		}
		
		//rotate slider

		function rotate() {

			autoSlide = setInterval(function() { 

				animate('right') 
			}, options.pause+options.speed);
		}
				
		//show next slide

		function animate(direction) {
		
			if(disabled) {

				return;
			} else {

				//disable animation

				disabled = true;
			}
			
			//get current slide

			var currentSlide = list.children('li.current');			
			
			//get next slide for current direction

			if(direction == 'left') {

				if(list.children('li.current').prev('li').length) {

					nextSlide = list.children('li.current').prev('li');

				} else if(options.effect == 'fade') {

					nextSlide = list.children('li:last-child');
				}

			} else if(direction == 'right') {

				if(list.children('li.current').next('li').length) {

					nextSlide = list.children('li.current').next('li');

				} else if(options.effect == 'fade') {

					nextSlide = list.children('li:first-child');
				}				
			}
			
			//remove current slide class

			currentSlide.removeClass('current');
			
			//calculate position

			if(options.effect == 'slide') {

				var backgroundPos = getBackgroundPos(nextSlide.index());
				var sliderPos = -nextSlide.index()*slider.width();
				
				if(nextSlide.is(':last-child')) {

					backgroundPos = getBackgroundPos(1);

				} else if(nextSlide.is(':first-child')) {

					backgroundPos = getBackgroundPos(list.children('li').length-2);
				}
			
				list.animate(
					{
						'left':sliderPos,
						'height':nextSlide.outerHeight()
					},
					options.speed, function(){

						if(nextSlide.is(':last-child')) {

							list.children('li').eq(1).addClass('current');
							sliderPos = -slider.width();

						} else if(nextSlide.is(':first-child')) {

							list.children('li:last-child').prev('li').addClass('current');
							sliderPos = -(list.children('li').length-2)*slider.width();

						} else {

							nextSlide.addClass('current');
						}

						list.css('left',sliderPos);
						disabled = false;
					}
				);
				
				substrate.animate({'left':'-'+backgroundPos+'%'},options.speed);

			} else {				

				list.animate({'height':nextSlide.outerHeight()},options.speed);
				nextSlide.css({'position':'absolute','z-index':'2'}).fadeIn(options.speed, function() {	

					//set current slide class

					currentSlide.hide().removeClass('current');
					nextSlide.addClass('current').css({'position':'relative', 'z-index':'1'});	
						
					//enable animation

					disabled = false; 
				});
			}
		}
		
		//background position

		function getBackgroundPos(slideIndex) {	
					
			return (((substrate.width()-slider.width())/slider.width())*100/(list.children('li').length-3))*(slideIndex-1);
		}
		
		//resize slider

		function resize() {
 
			if(options.effect == 'slide') {

				list.children('li').width(slider.width());
				list.width(list.children('li').length*slider.width());
				
				list.find('.row > *').each(function() {

					$(this).css('top',($(this).parent().outerHeight()-$(this).outerHeight())/2);
				});
				
				if(list.children('li').length>1) {

					list.children('li').removeClass('current');
					list.children('li:first-child').next().addClass('current');
					list.css('left', -slider.width());
				}
			}
			
			list.height(list.find('li.current').outerHeight());
		}		
		
		//load slider

		// $(window).load(load);
		
		//window resize event

		$(window).bind('resize', resize);

		init();
	}
})(jQuery);