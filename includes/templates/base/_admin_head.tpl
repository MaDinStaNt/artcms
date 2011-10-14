<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml"> 
<head>
    <title><? echo $PAGE_TITLE; ?></title>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
	<link rel="stylesheet" href="<? echo $CSS; ?>admin.core.css" type="text/css" />
	<link rel="stylesheet" href="<? echo $CSS; ?>admin.navigator.css" type="text/css" />
	<link rel="stylesheet" href="<? echo $JS; ?>jquery/admin.ui/css/smoothness/jquery-ui-1.8.15.custom.css" type="text/css" />
	<!--[if IE]><link rel="stylesheet" href="css/admin.ie.css" type="text/css" /><![endif]-->
	<!--[if IE 7]><link rel="stylesheet" href="css/admin.ie7.css" type="text/css" /><![endif]-->
	<script type="text/javascript" src="<? echo $JS; ?>jquery/jquery-1.6.2.min.js"></script>
	<script type="text/javascript" src="<? echo $JS; ?>jquery/admin.ui/jquery-ui-1.8.15.custom.min.js"></script>
	<script type="text/javascript" src="<? echo $JS; ?>interface.js"></script>
	<script type="text/javascript" src="<? echo $JS; ?>interface.js"></script>
	<script type="text/javascript" src="<? echo $JS; ?>ajax-connector.js"></script>
	<script type="text/javascript" src="<? echo $JS; ?>observer.js"></script>
	<script type="text/javascript">
	$(function(){
		$( ".inpDate" ).each(function(){
			date = $(this).val();
			$(this).datepicker();
			$(this).datepicker("option", "dateFormat", 'yy-mm-dd');
			$(this).datepicker("setDate" , date);
		});
		$( ".inpDate" ).datepicker("option", "dateFormat", 'yy-mm-dd');
	});
	</script>