<? CForm::begin($_table); ?>
	<div class="note">
		<span class="note_title">Warning!</span> Fields marked with <strong>bold</strong> are obligatory
	</div>	

	<? if($id): ?>
		<h2>User: <? echo $name; ?></h2>
	<? else: ?>
		<h2>New User</h2>
	<? endif; ?>
	
	<div class="col">
		<div class="row">
			<label for="email"><strong><? CTemplate::loc_string('email'); ?>:</strong></label>
			<? CTemplate::input('text', 'email', 'email', 'inp'); ?>
		</div>
		<div class="row">
			<? if(!$id): ?>
				<label for="password"><strong><? CTemplate::loc_string('password'); ?>:</strong></label>
			<? else: ?>
				<label for="password"><? CTemplate::loc_string('password'); ?>:</label>
			<? endif;?>
			<? CTemplate::input('password', 'password', 'password', 'inp'); ?>
		</div>
		<div class="row">
			<label for="name"><strong><? CTemplate::loc_string('name'); ?>:</strong></label>
			<? CTemplate::input('text', 'name', 'name', 'inp'); ?>
		</div>
		<div class="row">
			<label for="address"><? CTemplate::loc_string('address'); ?>:</label>
			<? CTemplate::input('text', 'address', 'address', 'inp'); ?>
		</div>
		<div class="row">
			<label for="city"><? CTemplate::loc_string('city'); ?>:</label>
			<? CTemplate::input('text', 'city', 'city', 'inp'); ?>
		</div>
		<div class="row">
			<label for="state_id"><? CTemplate::loc_string('state_id'); ?>:</label>
			<? CTemplate::input('select', 'state_id', 'state_id', 'sel'); ?>
		</div>
		<div class="row">
			<label for="zip"><? CTemplate::loc_string('zip'); ?>:</label>
			<? CTemplate::input('text', 'zip', 'zip', 'inp inp1'); ?>
		</div>
		<div class="row">
			<label for="user_role_id"><strong><? CTemplate::loc_string('user_role_id'); ?>:</strong></label>
			<? CTemplate::input('select', 'user_role_id', 'user_role_id', 'sel'); ?>
		</div>
		<div class="row">
			<label for="status"><strong><? CTemplate::loc_string('status'); ?>:</strong></label>
			<? CTemplate::input('select', 'status', 'status', 'sel'); ?>
		</div>
		<div class="buttons">
			<div class="inpwrapper"><? CTemplate::submit('save', 'save', CTemplate::get_loc_string('btn_save'), 'butt'); ?></div>
			<div class="inpwrapper"><? CTemplate::reset('reset', 'reset', CTemplate::get_loc_string('btn_reset'), 'butt'); ?></div>
			<div class="inpwrapper"><? CTemplate::button('close', 'close', CTemplate::get_loc_string('btn_close'), 'butt'); ?></div>
		</div>
	</div>

<? CForm::end(); ?>