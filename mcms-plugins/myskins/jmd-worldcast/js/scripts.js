(function ($, root, undefined) {
	
	$(document).ready(function() {
		
		'use strict';
		
		var touch 	= $('#touch-menu');
		var menu 	= $('.site-navigation');

		$(touch).on('click', function(e) {
			e.preventDefault();
			menu.slideToggle();
			touch.toggleClass("on");
		});
		
		$(window).resize(function(){
			var w = $(window).width();
			if(w > 992 && menu.is(':hidden')) {
				menu.removeAttr('style');
			}
		});

		function fullWindow() {
			$(".fullwindow").css("min-height", $(window).height() - $(".header").height() - $(".footer").height());
		};
		fullWindow();

		$(window).resize(function() {
			fullWindow();
		});

		var homeSlider = new Swiper('.home-slider', {
			loop: true,
			speed: 800,
			autoplay: {
				delay: 30000,
				disableOnInteraction: false,
			},
			navigation: {
				nextEl: '.slide-next',
				prevEl: '.slide-prev',
			},
		});

	  $(function() {
	  		var adjustArticleHeights = (function () {
		    var leftColumnHeight = 0,
		      rightColumnHeight = 0,
		      $articles = $('.feed-wrap article');
		    for (var i = 0; i < $articles.length; i++) {
		      if (leftColumnHeight > rightColumnHeight) {
		        rightColumnHeight += $articles.eq(i).addClass('right').outerHeight(true);
		      } else {
		        leftColumnHeight += $articles.eq(i).outerHeight(true);
		      }
		    }
		    return $articles;
		  })();
		});
		
	});
	
})(jQuery, this);
