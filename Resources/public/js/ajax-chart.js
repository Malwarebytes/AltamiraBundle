jQuery(document).ready(function(){
	jQuery('.ajax-chart-action').on( 'click', function() {
		var my = jQuery(this);
		var chart = jQuery(my.parent('div.ajax-chart-wrapper')[0]).find('div.chart');
		var params = {
				'name': chart.data('name'),
				'library': chart.data('library'),
				'series': jQuery(my.siblings('textarea')[0]).text()
		};
		if (chart.data('type') != undefined) {
			params = jQuery.extend(params, {'type':chart.data('type')});
		}
		jQuery.ajax({
				type: 'POST',
				url: ajaxurl,
				data: params,
				dataType: 'script',
		});
	});
});