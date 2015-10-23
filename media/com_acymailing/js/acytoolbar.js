/**
 * @package    AcyMailing for Joomla!
 * @version    5.0.0
 * @author     acyba.com
 * @copyright  (C) 2009-2015 ACYBA S.A.R.L. All rights reserved.
 * @license    GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

(function () {
	var topMenu, leftMenu, initialTopMenuAcyaffix;
	var affixOption = function () {
		var element;
		var topValue = 0;
		var elementsFixed = [];
		var elementsToAffix = [];
		var scroll = window.scrollY || document.documentElement.scrollTop;

		elementsFixed = elementsFixed.concat(_convertToArray(document.getElementsByClassName('navbar-fixed-top')));
		elementsFixed = elementsFixed.concat(_convertToArray(document.getElementsByClassName('affix')));

		for (var i = 0; i < elementsFixed.length; i++) {
			element = elementsFixed[i].getBoundingClientRect();
			topValue += element.bottom;
		}


		elementsToAffix = elementsToAffix.concat(_convertToArray(document.getElementsByClassName('acyaffix-top')));
		elementsToAffix = elementsToAffix.concat(_convertToArray(document.getElementsByClassName('acyaffix')));

		for (var i = 0; i < elementsToAffix.length; i++) {
			element = elementsToAffix[i].getBoundingClientRect();
			if (element.top <= topValue && scroll != 0) {
				element = elementsToAffix[i];
				element.className = element.className.replace('acyaffix-top', 'acyaffix');
				element.style.top = topValue + 'px';
			}
			if (scroll == 0 || scroll < initialTopMenuAcyaffix - topValue) {
				element = elementsToAffix[i];
				if (element.className.indexOf('acyaffix-top') == -1) {
					element.className = element.className.replace('acyaffix', 'acyaffix-top');
				}
				element.style.top = 0;
			}
		}
	};

	var menuOption = function () {
		var menu = document.getElementsByClassName('acytoolbarmenu_menu')[0];
		menu.style.top = menu.parentElement.getBoundingClientRect().top + 'px';
	}

	window.addEvent('domready', function () {
		topMenu = document.getElementById('acymenu_top');
		leftMenu = document.getElementById('acymenu_leftside');
		initialTopMenuAcyaffix = topMenu.getBoundingClientRect().top;

		affixOption();


		window.addEvent("scroll", affixOption);
		window.addEvent("scroll", menuOption);
	});

	function _convertToArray(collection) {
		return [].slice.call(collection);
	}
})();
