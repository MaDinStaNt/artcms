<?CForm::begin('login_form');?>
	<h4><? CTemplate::loc_string('administator_panel'); ?></h4>
	<div class="row">
		<label for="email">E-mail:</label>
		<?CTemplate::input('text', 'email', 'email', 'inp')?>
	</div>
	<div class="row">
		<label for="password">Password:</label>
		<?CTemplate::input('password', 'password', 'password', 'inp')?>
	</div>
	<div class="row-chkbx">
		<?CTemplate::input('checkbox', 'remember_me', 'remember_me')?>
		<label for="remember_me"> - <? CTemplate::loc_string('remember_me'); ?></label>
	</div>
	<div class="row">
		<div class="inpwrapper"><?CTemplate::submit('login_butt', 'login_butt', CTemplate::get_loc_string('login'), 'butt');?></div>
	</div>
<?CForm::end();?>