(function($) {
	$.fn.rating2 = function() {
		return $(this).each(function() {
			var select = -1;
			var stars  = $('.star', this);
			var inp    = $('input[type=hidden]', this);
			
			var drain = function() {
				stars.removeClass('on').removeClass('hover');
			};
			var fill  = function() {
				drain();
				$(this).prevAll('.star').andSelf().addClass('hover');
			};
			var reset = function() {
				drain();
				if (select >= 0) {
					stars.slice(0, select + 1).addClass('on');
				}
			};
			
			stars.hover(fill, reset).focus(fill).blur(reset).click(function() {
				select = stars.index(this);
				var link = $('a', $(this)).get(0);
				var rating = link.hash.substr(1);
				inp.val(rating);
				reset();
				
				return false;
			});
		});
	};
})(WDN.jQuery);
