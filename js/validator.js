$(function(){
	$("form.ajax_validation input").change(function() {
		$("#"+$(this).attr('name')+"_validator_message").html('<div id="loader"><img src="/images/ajax-loader_sm.gif" /></div>');
		 call('AjaxValidator', 'validate', [ $("#page_class").val(), $(this).parents('form').attr('id'), $(this).attr('name'), $(this).val()]).listen(validate); 
	}); 
});
function validate(data) {
	if (data && data.response) {
		if (data.response.message.length > 0) {
			$("#"+data.response.field+"_validator_message").html('<div class="haserror">'+data.response.message+'</div>'); 
		} else { 
			$("#"+data.response.field+"_validator_message").html('<div class="good">&nbsp;</div>'); 
		} 
	} else {
		alert('Validators not described!'); 
	} 
}