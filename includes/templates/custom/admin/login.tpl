<?CForm::begin('login_form');?>
	<h4>Панель Администратора</h4>
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
		<label for="remember_me"> - запомнить меня</label>
	</div>
	<div class="row">
		<div class="inpwrapper"><?CTemplate::button('login_butt', 'login_butt', CTemplate::get_loc_string('login'), 'butt');?></div>
	</div>
<?CForm::end();?>