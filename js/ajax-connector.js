function call(bll_class_name, method_name, parameters) {
	var o = new Observer;
	$.post("/ajax/handler.php", { 'ajax': 1, 'bll_class_name': bll_class_name, 'method_name': method_name, 'parameters': parameters }, function(data, status){
		o.notify(data);
	}, "json");
	return o;
}
