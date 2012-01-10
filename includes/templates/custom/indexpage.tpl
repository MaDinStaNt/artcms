Hello, Developer! Welcome to develop at "Art-cms".

<script type="text/javascript" src="<?=$JS;?>validator.js"></script>
<? CForm::begin('ajax', 'POST', '', '', '', true); ?>

	<label for="ajax_test">Ajax Validation Test</label>
	<? CTemplate::input('text', 'ajax_test', 'ajax_test'); ?>
	<div id="ajax_test_validator_message">&nbsp;</div>
	
	<? echo stripcslashes(js_escape_string(('
		\\\
		<div><strong>hu</strong>i</div><br />
		<script type="text/javascript">
			alert(1);
		</script>
	'))); ?>
	<? print_arr($c_arr->get_value()); ?>
	<? foreach ($c_arr->get_value() as $val): ?>
		<div><? $val->escape(); ?></div>
	<? endforeach; ?>
	
<? CForm::end(); ?>