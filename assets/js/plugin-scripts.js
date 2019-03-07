jQuery(function($) {
	let xnSelectField      = $('#xn-wppe-select-action');
	let xnAddTextFieldWrap = $('#xn-wppe-add-prefix-wrap');
	let xnAddTextField     = $('#xn-wppe-add-prefix');
	let xnDatetimeField    = $('#xn-wppe-datetime');
	let xnPrevDatetime     = xnDatetimeField.val();

	let xnDatetimepicker = xnDatetimeField.datepicker({
		minDate: new Date(),
		dateFormat: 'yy-mm-dd 00:00',
	}).datepicker('widget');

    xnDatetimepicker.wrap('<div class="datepicker ll-skin-latoja"/>');

	if (xnSelectField.val() != 'add_prefix') {
		xnAddTextFieldWrap.slideUp();
		xnAddTextField.prop('disabled', true);
	}

	xnSelectField.on('change', function() {
		if ($(this).val() == 'add_prefix') {
			xnAddTextFieldWrap.slideDown('fast');
			xnAddTextField.prop('disabled', false);
		} else {
			xnAddTextFieldWrap.slideUp('fast');
			xnAddTextField.prop('disabled', true);
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