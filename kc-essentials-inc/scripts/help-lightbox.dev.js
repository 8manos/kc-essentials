var win = window.dialogArguments || opener || parent || top;

(function($) {
	win.kcBody = $(document.body);

	win.kcPopHelp = function() {
		win.kcHelpBox.css({
			width: win.kcBody.width() * .85,
			height: win.kcBody.height() * .85
		})
		.lightbox_me({
			centered: true,
			destroyOnClose: true,
			showOverlay: true,
			overlaySpeed: 10,
			lightboxSpeed: 10,
			overlayCSS: {
				background: '#fff',
				opacity: '.1'
			}
		});
	};


	$(document).ready(function($) {
		var $main	= $('.contextual-help-tabs-wrap').children(),
				$side	= $('.contextual-help-sidebar'),
				help	= '';

		if ( $main.length ) {
			$main.each(function() {
				help += '<h3 class="title">'+ $.trim( $('#tab-link-'+this.id.replace('tab-panel-', '')).text() ) +'</h3>';
				help += $(this).html();
			});
		}
		// Side help
		if ( $side.children().length ) {
			help += '<hr />'+ $side.html();
		}

		if ( help !== '' ) {
			win.kcHelpBox = $('<div id="kc-help-lightbox" class="hidden"><div class="_wrap"><div class="_inside">'+help+'</div></div></div>').appendTo(win.kcBody);

			$(document).bind('keypress', function(e) {
				if ( !$(e.target).is(':input') && e.which == 63 ) {
					if ( win.kcHelpBox.is(':visible') )
						win.kcHelpBox.trigger('close');
					else
						win.kcPopHelp();

					e.preventDefault();
				}
			});
		}
	});
})(jQuery);

