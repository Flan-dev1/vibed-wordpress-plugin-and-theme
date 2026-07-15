(function ($) {
	'use strict';

	function showPropertyResults(picker, properties) {
		var results = picker.find('.starflan-property-results').empty();
		if (!properties.length) {
			results.text(StarFlanAdmin.noResults);
			return;
		}
		properties.forEach(function (property) {
			$('<button type="button" class="button starflan-add-property"></button>')
				.attr('data-property-id', property.id)
				.attr('data-property-title', property.title)
				.text(property.title + ' (#' + property.id + ')')
				.appendTo(results);
		});
	}

	$(document).on('click', '.starflan-select-media', function (event) {
		event.preventDefault();
		var button = $(this);
		var frame = wp.media({ title: 'Choose an image', library: { type: 'image' }, multiple: false });
		frame.on('select', function () {
			var attachment = frame.state().get('selection').first().toJSON();
			button.siblings('.starflan-media-id').val(attachment.id);
			button.siblings('.starflan-media-label').text(attachment.title + ' (#' + attachment.id + ')');
		});
		frame.open();
	});

	$(document).on('click', '.starflan-search-properties', function () {
		var picker = $(this).closest('.starflan-property-picker');
		var results = picker.find('.starflan-property-results').text('…');
		$.get(StarFlanAdmin.ajaxUrl, {
			action: 'starflan_search_estatik_properties',
			nonce: StarFlanAdmin.nonce,
			search: picker.find('.starflan-property-search').val()
		}).done(function (response) {
			if (response.success) {
				showPropertyResults(picker, response.data);
			} else {
				results.text(response.data && response.data.message ? response.data.message : StarFlanAdmin.searchError);
			}
		}).fail(function () {
			results.text(StarFlanAdmin.searchError);
		});
	});

	$(document).on('keydown', '.starflan-property-search', function (event) {
		if (event.key === 'Enter') {
			event.preventDefault();
			$(this).siblings('.starflan-search-properties').trigger('click');
		}
	});

	$(document).on('click', '.starflan-add-property', function () {
		var button = $(this);
		var picker = button.closest('.starflan-property-picker');
		var assigned = picker.find('.starflan-assigned-properties');
		var id = String(button.data('property-id'));
		if (assigned.find('input[value="' + id + '"]').length) {
			picker.find('.starflan-property-results').text(StarFlanAdmin.alreadyAdded);
			return;
		}
		var row = $('<div class="starflan-assigned-property"></div>');
		$('<input type="hidden">').attr('name', 'starflan[' + picker.data('field') + '][]').val(id).appendTo(row);
		$('<span></span>').text(button.data('property-title') + ' (#' + id + ')').appendTo(row);
		$('<button type="button" class="button-link-delete starflan-remove-property"></button>').text(StarFlanAdmin.remove).appendTo(row);
		row.appendTo(assigned);
		button.remove();
	});

	$(document).on('click', '.starflan-remove-property', function () {
		$(this).closest('.starflan-assigned-property').remove();
	});
}(jQuery));
