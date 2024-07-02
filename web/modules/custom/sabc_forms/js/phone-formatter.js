document.addEventListener("DOMContentLoaded", function(event) {

	var formatted = new Formatter(document.getElementById('phonenumber'), {
		'pattern': '({{999}}) {{999}}-{{9999}}',
	});

});