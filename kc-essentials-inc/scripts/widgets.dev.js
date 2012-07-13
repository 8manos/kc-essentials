(function($, document) {
	var
	func = 'kcPostFinder',
	active = false,
	$_doc = $(document),
	selectors = ['.kc-find-post'],
	$_box, $_input, $_response, $_submit, $_close,
	getSelectors = function() {
		return selectors.join( ', ');
	}
	activate = function() {
		$_input = $('#find-posts-input');
		$_response = $('#find-posts-response');
		$_submit = $('#find-posts-submit');
		$_close = $('#find-posts-close');

		// Insert
		$_submit.on('click.kcPostFinder', function(e) {
			e.preventDefault();

			// Be nice!
			if ( !$_box.data('kcTarget') )
				return;

			var $selected = $_response.find('input:checked');
			if ( !$selected.length )
				return false;

			var $target = $_box.data('kcTarget'),
			    current = $target.val(),
			    current = current === '' ? [] : current.split(','),
			    newID   = $selected.val();

			if ( $target.is('.unique') ) {
				$target.val( newID );
			}
			else if ( $.inArray(newID, current) < 0 ) {
				current.push(newID);
				$target.val( current.join(',') );
			}
		});

		// Double click on the radios
		$_doc.on('dblclick.kcPostFinder', 'input[name="found_post_id"]', function() {
			$_submit.trigger('click.kcPostFinder');
		});

		// Close
		$_doc.on('click.kcPostFinder', '#find-posts-close', function() {
			$_input.val('');
			$_box.removeData('kcTarget');
		});

		active = true;
	},
	deactivate = function() {
		unbind();
		$_submit.off('click.kcPostFinder');
		$_doc.off('dblclick.kcPostFinder');
		$_doc.off('click.kcPostFinder');
		$_box = $_input = $_response = $_submit = $_close = null;
		active = false;
	},
	action = function(e) {
		$_box.data('kcTarget', $(this));
		findPosts.open();
	},
	bind = function() {
		$_doc.on( 'dblclick.kcPostFinder', getSelectors(), action );
	},
	unbind = function() {
		$_doc.off( 'dblclick.kcPostFinder', getSelectors(), action );
	},
	publicMethod = $[func] = function( sel ) {
		var $this = this;

		if ( active ) {
			if ( !sel )
				return;

			unbind();
		}
		else {
			$_box = $('#find-posts');
			if ( !$_box.length )
				return;

			activate();
		}

		if ( sel )
			selectors = selectors.concat( sel.split(',') );

		bind();

		return $this;
	};

	publicMethod.destroy = function() {
		deactivate();
	};
}(jQuery, document));


(function($) {
	var $doc = $(document);

	// Deps
	$('.widgets-sortables .hasdep').kcFormDep();
	$('.widgets-sortables').ajaxSuccess(function() {
		$('.hasdep', this).kcFormDep();
	});

	// Delete tax/meta query row
	$('.kcw-control-block .rm').live('click', function(e) {
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
			$nu.slideDown()
				.kcReorder( $el.attr('rel'), false )
				.find('.hasdep').kcFormDep();
		}
	});

	$.kcPostFinder();
})(jQuery);
