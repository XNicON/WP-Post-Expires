jQuery(function($){
	var selectField      = $('#xn-wppe-select-action');
	var addTextFieldWrap = $('#xn-wppe-add-prefix-wrap');
	var addTextField     = $('#xn-wppe-add-prefix');
	var editBtn          = $('#xn-wppe-edit');
	var hideBtn          = $('.xn-wppe-hide-expiration');
	var allFields        = $('#xn-wppe-fields');
	var datetimeField    = $('#xn-wppe-datetime');
	var datetimePreview  = $('#xn-wppe-currentsetdt');
	var prevDatetime     = datetimeField.val();
	var langs            = $.fn.datepicker.language;
	var datetimepicker = datetimeField.datepicker({
		minDate: new Date(),
		dateFormat: 'yyyy-mm-dd',
		timepicker: true,
		timeFormat:'hh:ii',
		position: "bottom right",
		language: langs[Object.keys(langs)[Object.keys(langs).length - 1]]
	}).data('datepicker');

	if($(window).width() < 768 ){
		datetimepicker.update('position','bottom left');
	}

	if(selectField.val() != 'add_prefix'){
		addTextFieldWrap.slideUp();
		addTextFieldWrap.prop('disabled',true);
	}

	selectField.on('change',function(){
		if($(this).val() == 'add_prefix'){
			addTextFieldWrap.slideDown('fast');
			addTextField.prop('disabled',false);
		}else{
			addTextFieldWrap.slideUp('fast');
			addTextField.prop('disabled',true);
		}
	});

	editBtn.on('click',function(e){
		e.preventDefault();
		editBtn.hide();
		allFields.slideDown('fast');
	});

	hideBtn.on('click',function(e){
		e.preventDefault();
		editBtn.show();
		allFields.slideUp('fast');

		if(!$(this).hasClass('cancel')){
			if(datetimeField.val().length > 0){
				datetimePreview.text(datetimeField.val());
			}else{
				datetimePreview.text(datetimePreview.data('never'));
			}
		}else{
			datetimeField.val(prevDatetime);
		}
	});
});
