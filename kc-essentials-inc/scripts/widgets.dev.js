(function($) {

	$(document).ready(function($) {
		var $widgets = $('#widgets-right'),
		    $fields  = $widgets.find('.kcw-control-block, .kcwe'),
		    $heads   = $widgets.find('h5');

		$('.hasdep', $widgets).kcFormDep();

		$('.widgets-sortables', $widgets).ajaxSuccess(function() {
			$('.hasdep', this).kcFormDep();
		});


		$heads.live('click', function() {
			$(this).next('.kcw-control-block').slideToggle('slow');
		});


		// Delete tax/meta query row
		$('.kcw-control-block .del').live('click', function(e) {
			e.preventDefault();

			var $el    = $(this),
			    $item  = $el.parent(),
			    $block = $item.parent(),
			    $next  = $item.next('.row');

			$item.slideUp(function() {
				if ( !$item.siblings('.row').length ) {
					$item.find('input[type="text"]').val('');
					$item.find('input[type="checkbox"]').prop('checked', false);
					$item.find('.hasdep').trigger('change');
				} else {
					$item.remove();
					if ( $next.length )
						$block.kcReorder( $el.attr('rel'), true );
				}
			});
		});


		// Add tax/meta query row
		$('.kcw-control-block .add').live('click', function(e) {
			e.preventDefault();

			var $el   = $(this),
			    $item = $el.parent().prev('.row');

			if ( $item.is(':hidden') ) {
				$item.slideDown();
			} else {
				$nu = $item.clone(true).hide();
				$item.after( $nu );
				$nu.slideDown().kcReorder( $el.attr('rel'), false );
			}
		});
	});

})(jQuery);
