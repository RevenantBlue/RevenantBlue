// JavaScript Document
$(document).ready(function() {
	var elements, element, group, x, elements = new Array('article', 'comment', 'content', 'overlay', 'pages', 'photogallery', 'system', 'user');
	for(x = 0; x < elements.length; x++) {
		(function(element) {
			$('#' + element).click(function() {
				group = $('.' + element);
				if(group.is(':visible')) {
					$('.' + element).hide();
					document.getElementById(element).className = 'icon-20-expand';
				} else if(group.is(':hidden')) {
					$('.' + element).show();
					document.getElementById(element).className = 'icon-20-collapse';	
				}
			});
		})(elements[x]);
	}
	
	$('#expand-role-table').click(function() {
		var x;
		for(x = 0; x < elements.length; x++) {
			$('.' + elements[x]).show();
			document.getElementById(elements[x]).className = 'icon-20-collapse';	
		}
	});
	$('#collapse-role-table').click(function() {
		var x;
		for(x = 0; x < elements.length; x++) {
			$('.' + elements[x]).hide();
			document.getElementById(elements[x]).className = 'icon-20-expand';	
		}
	});
});