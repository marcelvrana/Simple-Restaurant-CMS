/**
 * Custom edit to nette forms library - disabled alerts and validation, but toggle functionality works.
 */

Nette.validateControl = function(elem, rules, onlyCheck, value, emptyOptional) {
	return true;
}

Nette.validateForm = function(sender, onlyCheck) {
	return true;
}

Nette.toggle = function (id, visible) {
	var el = $('#' + id);
	if (visible) {
		el.slideDown(250);
	} else {
		el.slideUp(250);
	}
};