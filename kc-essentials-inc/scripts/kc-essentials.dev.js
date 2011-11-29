jQuery(document).ready(function($) {
	$('#kc-metabox-settings_page_kc-settings-kc_essentials-general').find(':checkbox').change(function() {
		var id		= '#kc-metabox-settings_page_kc-settings-kc_essentials-',
				$target = $( id+this.value );

		if ( $target.length )
			$(id+this.value+'-hide').prop('checked', this.checked).triggerHandler('click');
	});
});