Hello, Developer! Welcome to develop at "Art-cms".

<script type="text/javascript" src="<? echo $JS; ?>validator.js"></script>
<? CForm::begin('ajax', 'POST', '', '', '', true); ?>

	<label for="ajax_test">Ajax Validation Test</label>
	<? CTemplate::input('text', 'ajax_test', 'ajax_test'); ?>
	<div id="ajax_test_validator_message">&nbsp;</div>
	
<? CForm::end(); ?>