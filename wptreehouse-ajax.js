jQuery(document).ready(function($) {
	var data = {
		'action': 'wptreehouse_badges_refresh_profile'	
	};
	// We can also pass the url value separately from ajaxurl for front end AJAX implementations
	jQuery.post(ajax_object.ajax_url, data, function(response) {
		console.log('AJAX was successful');
	});
});