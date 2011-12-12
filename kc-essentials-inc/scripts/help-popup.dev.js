jQuery(document).ready(function($) {
	var $body	= $('body'),
			$main	= $('.contextual-help-tabs-wrap').children(),
			$side	= $('.contextual-help-sidebar'),
			help	= '',
			$box	= null;

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
		$box = $('<div id="kc-help-popup" class="hidden"><div class="_wrap"><div class="_inside">'+help+'</div></div></div>').appendTo('body');

		$(document).bind('keypress', function(e) {
			if ( !$(e.target).is(':input') && e.which == 63 ) {
				if ( $box.is(':visible') ) {
					$box.trigger('close');
				}
				else {
					$box.css({
						width: $body.width() * .85,
						height: $body.height() * .85
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
				}

				e.preventDefault();
			}
		});
	}
});
