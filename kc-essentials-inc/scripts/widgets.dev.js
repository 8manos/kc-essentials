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
			}
			else {
				$nu = $item.clone(true).hide();
				$item.after( $nu );
				$nu.slideDown().kcReorder( $el.attr('rel'), false );
			}
		});
	});


	// Find posts
	var $findBox = $('#find-posts');

	// Open
	$('input.kc-find-post').dblclick(function() {
		$findBox.data('kcTarget', $(this));
		findPosts.open();
	});

	// Insert
	$('#find-posts-submit').click(function(e) {
		e.preventDefault();

		// Be nice!
		if ( !$findBox.data('kcTarget') )
			return;

		var $selected = $('#find-posts-response').find('input:checked');
		if ( !$selected.length )
			return false;

		var $target = $findBox.data('kcTarget'),
		    current = $target.val(),
		    current = current === '' ? [] : current.split(','),
		    newID   = $selected.val();

		if ( $.inArray(newID, current) < 0 ) {
			current.push(newID);
			$target.val( current.join(',') );
		}
	});

	// Close
	$( '#find-posts-close' ).click(function() {
		$findBox.removeData('kcTarget');
	});
})(jQuery);
