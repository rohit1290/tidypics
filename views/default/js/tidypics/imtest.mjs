	import 'jquery';
	import elgg from 'elgg';
	import Ajax from 'elgg/Ajax';
	import spinner from 'elgg/spinner';


	// manage Spinner manually
	var ajax = new Ajax(false);

	$(document).on('submit', '.elgg-form-photos-admin-imtest', function(e) {
		var $form = $(this);

		spinner.start();
		ajax.action($form.prop('action'), {
			data: ajax.objectify($form)
		}).done(function(json, status, jqXHR) {
			if (jqXHR.AjaxData.status == -1) {
				$('input[name=im_location]', $form).val('').focus();
				spinner.stop();
				return;
			}

			if (json && (typeof json.result === 'string')) {
				spinner.stop();
				$("#tidypics-im-results").html(json.result);
			} else {
				spinner.stop();
				$("#tidypics-im-results").html(elgg.echo('tidypics:lib_tools:error'));
			}
		});

		e.preventDefault();
	});