// Can't merge with previous script block since this relies on jQuery and that
// was not loaded until the previous script block ends
$(function () {
	var $min, $remove, $apply, $uniformed;

	// Debugging code to check for multiple click events
	$selects = $("select").click(function () {
		if (typeof console !== 'undefined' && typeof console.log !== 'undefined') {
			console.log($(this).attr('id') + " clicked");
		}
	});

	$uniformed = $(".styleThese").find("input, textarea, select, button, a.uniformTest").not(".skipThese");
	$uniformed.uniform();

	$("#optionsForm input, #optionsForm select").change(function () {
		this.form.submit();
	});

	if (optionValues.min) {
		$min = $("#optionsMin");

		if ($min.prop) {
			$min.prop("checked", true);
		} else {
			$min.attr("checked", "checked");
		}
	}

	$("#optionsJquery").val(optionValues.jquery);
	$("#optionsTheme").val(optionValues.theme);
	$("#jqueryCurrentVersion").text("Using " + jQuery.fn.jquery);
	$remove = $("#remove");
	$apply = $("#apply");
	$remove.click(function () {
		$uniformed.uniform.restore();
		$remove.hide();
		$apply.show();
		return false;
	});
	$apply.click(function () {
		$uniformed.uniform();
		$apply.hide();
		$remove.show();
		return false;
	});
});