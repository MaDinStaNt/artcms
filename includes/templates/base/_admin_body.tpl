<?if ($is_logged && !$is_404page):?>
	</head>
	<body>
	<div class="popupbg">&nbsp;</div>
	<div id="wrapper">
		<table cellpadding="0" cellspacing="0" class="overhidd maxh maxw" style="padding-bottom:30px;">
		<tbody><tr>
			<td id="left_nav" valign="top">
				<? CControl::process('Navi'); ?>
			</td>
			<td valign="middle" class="vert_line">
				<script type="text/javascript">
				$(function(){
					$('#show_panel').hide();
					$('#hide_panel, #show_panel').click(function(){
						$('#left_nav').toggle('slow');
						$('#hide_panel').toggle();
						$('#show_panel').toggle();
					});
				});
				</script>
				<div id="hide_panel" class="hand" style="display: block;"><a href="javascript: void(0);" class="hide"><span class="inv"><? CTemplate::loc_string('hide_panel'); ?></span></a></div>
				<div id="show_panel" class="hand"><a href="javascript: void(0);" class="show"><span class="inv"><? CTemplate::loc_string('show_panel'); ?></span></a></div>
			</td>
			<td class="overhidd maxh maxw">
				<div id="body">
					<div class="head">
						<div class="row">
							<div class="left_block"><? CTemplate::loc_string('you_logged_as'); ?>: <a href="<? echo $HTTP; ?>admin/?r=users.user_edit&id=<? echo $logged_user_id; ?>" class="ico-man" title="your name"><? echo $logged_user_name; ?></a> &nbsp; <a href="<? echo $HTTP; ?>admin/?r=registry" class="ico-sett" title="<? CTemplate::loc_string('settings'); ?>"><? CTemplate::loc_string('settings'); ?></a></div>
							<div class="right_block"><a href="JavaScript: if (confirm('<? CTemplate::loc_string('are_you_sure_you_want_to_logout'); ?>?')) document.forms.logout_form.submit();" title="<? CTemplate::loc_string('logout'); ?>" class="ico-logout"><? CTemplate::loc_string('logout'); ?></a></div>
							<? CForm::begin('logout_form'); ?>&nbsp;<? CForm::end(); ?>
						</div>
					</div>
					<div id="container">
						<? CControl::process('AdminBreadcrumb'); ?>
						<? CSimpleArrayOutput::create_html('_info', '<div class="info"><ul>'); ?>
						<? CSimpleArrayOutput::create_html('_errors', '<div class="errors"><ul>'); ?>
						<? if($_return_to): ?>
							<script type="text/javascript">
							$(function(){
								setTimeout(function(){window.location = "<? echo $_return_to; ?>"}, 1500);
							})
							</script>
						<? endif; ?>
<?else:?>
	<div id="page" class="maxh maxw">
		<div class="head">&nbsp;</div>
		<div id="login">
			<? CSimpleArrayOutput::create_html('_info', '<div class="info"><ul>'); ?>
			<? CSimpleArrayOutput::create_html('_errors', '<div class="errors"><ul>'); ?>
<?endif;?>