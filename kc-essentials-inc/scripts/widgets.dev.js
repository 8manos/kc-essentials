/* Post Finder dialog */
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



/* Form row cloner */
(function($, document) {
	var
	func = 'kcRowCloner',
	active = false,
	$_doc = $(document),
	callbacks = {
		add: [],
		del: []
	},

	activate = function() {
		bind();
		active = true;
	},

	deactivate = function() {
		unbind();
		active = false;
		callbacks = {
			add: [],
			del: []
		};
	},

	action = function(e) {
		var $anchor = $(e.target), func;

		if ( $anchor.is('a.add') )
			func = add;
		else if ( $anchor.is('a.del') )
			func = del;
		else
			return;

		e.preventDefault();
		var $item  = $(e.currentTarget);
		    $block = $item.parent();

		func.call( e, {
			'anchor': $anchor,
			'item': $item,
			'block': $block
		} );
	},

	add = function( args ) {
		console.log ( args );
	},

	del = function( args ) {
		var e = this;
		args.isLast = !args.item.next('li.row').length;
		args.removed = true;

		args.item.slideUp(function() {
			if ( !args.item.siblings('.row').length ) {
				args.item.find('input[type="text"]').val('');
				args.item.find('input[type="checkbox"]').prop('checked', false);
				args.item.find('.hasdep').trigger('change');
				args.removed = false;
			}
			else {
				args.item.remove();
			}

			for ( var i=0; i < callbacks.del.length; i++ ) {
				callbacks.del[i].call( e, args );
			}
		});
	},

	bind = function() {
		$_doc.on( 'click.kcRowCloner', 'li.row', action );
	},

	unbind = function() {
		$_doc.off( 'click.kcRowCloner', 'li.row', action );
	},

	publicMethod = $[func] = function( ) {
		var $this = this;

		if ( active )
			return;

		activate();
		return $this;
	};

	publicMethod.destroy = function() {
		deactivate();
	};

	publicMethod.addCallback = function( mode, callback ) {
		if ( callbacks.hasOwnProperty(mode) && $.isFunction(callback) )
			callbacks[mode].push( callback );
	};
})(jQuery, document);



(function($) {
	var $_doc = $(document);

	// Deps
	$('.widgets-sortables .hasdep').kcFormDep();
	$('.widgets-sortables').ajaxSuccess(function() {
		$('.hasdep', this).kcFormDep();
	});

	// Add tax/meta query row
	$_doc.on( 'click', '.kcw-control-block a.add', function(e) {
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


	// Remove tax/meta query row
	$.kcRowCloner();
	$.kcRowCloner.addCallback(
		'del',
		function( args ) {
			if ( args.removed && !args.isLast )
				args.block.kcReorder( args.anchor.attr('rel'), true );
		}
	);


	$.kcPostFinder();
})(jQuery);
