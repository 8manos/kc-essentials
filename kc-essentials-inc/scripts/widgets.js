(function($) {

	var inArray = function (needle, haystack) {
		var length = haystack.length;
		for (var i = 0; i < length; i++) {
			if (haystack[i] == needle) return true;
		}
		return false;
	};


	$.fn.kcReorder = function( mode, all ) {
		var rgx1	= new RegExp(mode+'\\]\\[(\\d+)'),
				rgx2	= new RegExp(mode+'\\-(\\d+)'),
				$el		= $(this);

		if ( all === true ) {
			var $els	= $el.children(),
					i			= 0;
		} else {
			var $els	= $el,
					i			= $el.index();
		}

		$els.each(function() {
			var $x = $(this);
			$x.find(':input').each(function() {
				this.name = this.name.replace(rgx1, function(str, p1) {
					return mode + '][' + i;
				});

				if ( this.id !== '' ) {
					this.id = this.id.replace(rgx2, function(str, p1) {
						return mode + '-' + i;
					});
				}
			});

			$x.find('label').each(function() {
				var $label 	= $(this),
						$atFor	= $(this).attr('for');

				if ( $atFor ) {
					$label.attr( 'for', $atFor.replace(rgx2, function(str, p1) {
						return mode + '-' + i;
					}) );
				}
			});

			i++;
		});

		return this;
	};


	$.fn.kcFormDep = function() {
		return this.each(function() {
			var $el		= $(this),
					val		= $el.val(),
					$dep	= ( $el.data('scope') !== undefined ) ?
										$el.closest( $el.data('scope') ).find( $el.data('child') ) :
										$( $el.data('child') );

			if ( !$dep.length )
				return;

			$dep.each(function() {
				var $c		= $(this),
						depon	= $c.data('dep');

				if ( (typeof depon === 'string' && depon === val)
							|| (typeof depon === 'object' && inArray(val, depon)) )
					$c.show();
				else
					$c.hide();
			});
		});
	};


	$(document).ready(function($) {
		var $widgets	= $('#widgets-right'),
				$fields		= $widgets.find('.kcw-control-block'),
				$heads		= $widgets.find('h5');

		$('.hasdep', $fields).live('change', function() {
			$(this).kcFormDep();
		}).change();


		$('.widgets-sortables').ajaxSuccess(function() {
			$('.hasdep', this).live('change', function() {
				$(this).kcFormDep();
			}).change();
		});


		$heads.live('click', function() {
			$(this).next('.kcw-control-block').slideToggle('slow');
		});


		// Delete tax/meta query row
		$('.kcw-control-block .del').live('click', function(e) {
			e.preventDefault();

			var $el			= $(this),
					$item		= $el.parent(),
					$block	= $item.parent(),
					$next		= $item.next('.row');

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

			var $el			= $(this),
					$item		= $el.parent().prev('.row');

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
