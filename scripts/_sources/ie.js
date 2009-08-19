/**
 * Реализация :focus для IE<7.
 *
 * @version	 $Id$
 */
document.onactivate = document.ondeactivate = function() {
	var element = $(window.event.srcElement);
	var className = 'focus';
	if (/text|password|file|select/.test(element.type) && !element.getAttribute('readonly')) {
		switch (event.type) {
			case 'activate':
				element.addClassName(className);
				break;
			case 'deactivate':
				element.removeClassName(className);
				break;
		}
	}
};