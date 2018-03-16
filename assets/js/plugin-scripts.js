jQuery(function($){
	var xnSelectField      = $('#xn-wppe-select-action');
	var xnAddTextFieldWrap = $('#xn-wppe-add-prefix-wrap');
	var xnAddTextField     = $('#xn-wppe-add-prefix');
	var xnEditBtn          = $('#xn-wppe-edit');
	var xnHideBtn          = $('.xn-wppe-hide-expiration');
	var xnAllFields        = $('#xn-wppe-fields');
	var xnDatetimeField    = $('#xn-wppe-datetime');
	var xnDatetimePreview  = $('#xn-wppe-currentsetdt');
	var xnPrevDatetime     = xnDatetimeField.val();
	var xnSetLang; for(xnSetLang in $.fn.datepicker.language); xnSetLang;

	var xnDatetimepicker = xnDatetimeField.datepicker({
		minDate: new Date(),
		dateFormat: 'yyyy-mm-dd',
		timepicker: true,
		timeFormat:'hh:ii',
		position: "bottom right",
		language: xnSetLang
	}).data('datepicker');

	if($(window).width() < 768 ){
		xnDatetimepicker.update('position','bottom left');
	}

	if(xnSelectField.val() != 'add_prefix'){
		xnAddTextFieldWrap.slideUp();
		xnAddTextField.prop('disabled',true);
	}

	xnSelectField.bind('change',function(){
		if($(this).val() == 'add_prefix'){
			xnAddTextFieldWrap.slideDown('fast');
			xnAddTextField.prop('disabled',false);
		}else{
			xnAddTextFieldWrap.slideUp('fast');
			xnAddTextField.prop('disabled',true);
		}
	});

	xnEditBtn.bind('click',function(e){
		e.preventDefault();
		xnEditBtn.hide();
		xnAllFields.slideDown('fast');
	});

	xnHideBtn.bind('click',function(e){
		e.preventDefault();
		xnEditBtn.show();
		xnAllFields.slideUp('fast');

		if(!$(this).hasClass('cancel')){
			if(xnDatetimeField.val().length > 0){
				xnDatetimePreview.text(xnDatetimeField.val());
			}else{
				xnDatetimePreview.text(xnDatetimePreview.data('never'));
			}
		}else{
			xnDatetimeField.val(xnPrevDatetime);
		}
	});
});