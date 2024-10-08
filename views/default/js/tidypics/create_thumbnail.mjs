	import 'jquery';
	import elgg from 'elgg';
	import Ajax from 'elgg/Ajax';
	import spinner from 'elgg/spinner';

	// manage Spinner manually
	var ajax = new Ajax(false);

	$(document).on('submit', '.elgg-form-photos-admin-create-thumbnail', function(e) {
		var $form = $(this);

		spinner.start();
		ajax.action($form.prop('action'), {
			data: ajax.objectify($form)
		}).done(function(json, status, jqXHR) {
			if (jqXHR.AjaxData.status == -1) {
				$('input[name=guid]', $form).val('').focus();
				spinner.stop();
				return;
			}

			if (json) {
				spinner.stop();

				var html = '<img class="elgg-photo tidypics-photo" src="'
					+ json.thumbnail_src + '" alt="' + json.title
					+ '" />';
				$("#elgg-tidypics-im-results").html(html);
			}
		});

		e.preventDefault();
	});