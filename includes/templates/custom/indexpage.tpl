Hello, Developer! Welcome to develop at "Art-cms".

<script type="text/javascript" src="<?=$JS;?>validator.js"></script>
<? CForm::begin('ajax', 'POST', '', '', '', true); ?>

	<label for="ajax_test">Ajax Validation Test</label>
	<? CTemplate::input('text', 'ajax_test', 'ajax_test'); ?>
	<div id="ajax_test_validator_message">&nbsp;</div>
	
	<? escape($test_var); ?>
	<div>
		<? if($is_logged): ?>
			<? if($is_VKlogged): ?>
				
				<p>VK login success!!!</p>
				<p>hello, <?=$logged_user_first_name; ?> <?=$logged_user_nickname; ?> <?=$logged_user_last_name; ?></p>
				<p>Your avatar: <img src="<?=$HTTP;?>pub/VKusers/<?=$logged_user_id;?>/<?=$logged_user_photo_filename;?>" title="<? CTemplate::loc_string('your_avatar'); ?>" /></p>
			<? else: ?>
				<p>You are logged manually (admin panel)</p>
			<? endif;?>
			<p><a href="javascript:document.forms.logout_form.submit();">logout</a></p>
		<? else: ?>
			<a href="<?=$HTTP;?>vk-auth">VK login</a>
		<? endif; ?>
	</div>
	
<? CForm::end(); ?>
<? CForm::begin('logout_form'); ?><? CForm::end(); ?>