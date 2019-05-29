( function( wp ) {
    let el = wp.element.createElement;
    function Component() {
        let meta = wp.data.select('core/editor').getEditedPostAttribute('meta');
        if(meta && meta['xn-wppe-expiration'].length > 0) {
            return el(wp.element.Fragment, {},
                el(wp.editPost.PluginPostStatusInfo, null, [
                        el('label', null, wp.i18n.__('Expires', 'wp-post-expires')),
                        el('div', null, [
                            el(wp.components.Dashicon, {icon: 'clock', className: 'xn-clock-icon'}, null),
                            wp.date.dateI18n(wp.date.__experimentalGetSettings()['formats']['datetime'],
                                meta['xn-wppe-expiration'] + ':00')
                        ])
                    ]
                )
            );
        }

        return '';
    }

    wp.plugins.registerPlugin('xn-wppe', {
        render: Component
    });

} )( window.wp );

jQuery(function($) {
    let selectField      = $('#xn-wppe-select-action');
    let addTextFieldWrap = $('#xn-wppe-add-prefix-wrap');
    let addTextField     = $('#xn-wppe-add-prefix');

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