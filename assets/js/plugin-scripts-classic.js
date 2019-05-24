jQuery(function($) {
	let selectField      = $('#xn-wppe-select-action');
	let addTextFieldWrap = $('#xn-wppe-add-prefix-wrap');
	let addTextField     = $('#xn-wppe-add-prefix');
	let dtField          = $('#xn-wppe-datetime');
	let prevDatetime     = dtField.val();

	let dtpicker = dtField.datepicker({
		minDate: new Date(),
		dateFormat: 'yy-mm-dd 00:00',
	}).datepicker('widget');

    dtpicker.wrap('<div class="datepicker ll-skin-latoja"/>');

	if (selectField.val() != 'add_prefix') {
		addTextFieldWrap.hide();
		addTextField.prop('disabled', true);
	}

	selectField.on('change', function() {
		if ($(this).val() == 'add_prefix') {
			addTextFieldWrap.slideDown('fast');
			addTextField.prop('disabled', false);
		} else {
			addTextFieldWrap.slideUp('fast');
			addTextField.prop('disabled', true);
		}
	});
});

/* FUTURE
( function( wp ) {
    let el = wp.element.createElement;
    function Component() {
        let meta = wp.data.select('core/editor').getEditedPostAttribute('meta');
        if(meta['xn-wppe-expiration'].length > 0) {
            return el(wp.element.Fragment, {},
                el(wp.editPost.PluginPostStatusInfo, {},
                    wp.i18n.__('Expires', 'wp-post-expires') + ' ' + meta['xn-wppe-expiration'],
                )
            );
        }

        return '';
    }

    wp.plugins.registerPlugin('xn-wppe', {
        render: Component
    });

} )( window.wp );
*/