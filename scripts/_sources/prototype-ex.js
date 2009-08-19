/**
 * Расширение prototype,js.
 *
 * @version	 $Id$
 */
(function() {
	/**
	 * Шаблон регулярного выражения. соответствующий суффиксу в именах элементов
	 * с возможностью множественного выбора
	 *
	 * @access  private
	 * @var	 string
	 */
	var _NAME_MULTIPLE_REGEXP = /\[\]$/;

	/**
	 * Устанавливает значение для указанного элемента.
	 *
	 * @access  private
	 * @param   HTMLElement element
	 * @param   mixed  value
	 */
	Form.Element.setValue = function(form, name, value) {
		form = $(form);
		var element = form.elements.namedItem(name);

		// :KLUDGE: для радио-кнопок Firefox возвращает первую, а другие
		// браузеры коллекцию элементов
		if (!element.tagName) {
			// для определения типа берем первый из коллекции
			element = element.item(0);
		}

		// определяем стратегию в зависимости от типа элемента
		switch (element.type) {
			// поле типа чекбокс
			case 'checkbox' :
				// для чекбоксов, объединенных в группу определяем, есть ли
				// значение чекбокса в массиве-значении для даннного элемента,
				// а для простых - просто выставляем в зависимости от значения
				element.checked = _NAME_MULTIPLE_REGEXP.test(element.name)
					? value.indexOf(element.value) >= 0
					: (Boolean(value) && (value != '0'));
				break;

			// выпадающий список без возможности множественного выбора
			case 'select-one' :

				// по умолчанию ни одна опция не является выбранной
				var index = -1;

				// проходим по опциям списка
				$A(element.options).each(function(oOption, i) {

					// если значение опции равно значению для данного жлемента
					if (oOption.value == value) {

						// делаем текущий элемент выбранным в списке
						index = i;

						// прерываем обход
						throw $break;
					}
				});

				// устанавливаем выбранную опцию
				element.selectedIndex = index;

				break;

			// выпадающий список с возможностью множественного выбора
			case 'select-multiple' :

				// проходим по опциям списка
				$A(element.options).each(function (oOption) {
					// отмечаем опцию, если его значение содержится в
					// массиве-значении для даннного элемента
					oOption.selected = value.indexOf(oOption.value) > 0;
				});

				break;

			// радио-кнопка
			case 'radio' :

				$A(form.elements).findAll(function(element) {
					return element.name && element.name == name && 'radio' == element.type;
				}).each(function (element) {
					// отмечаем элемент, если его значение равно значению для
					// даннного элемента
					element.checked = (element.value == value);
				});

				break;

			// простые текстовые поля
			default :
				element.value = value;
				break;
		}
	};

	Abstract.EventObserver.prototype.registerCallback = function(element) {
	  if (element.type) {
		switch (element.type.toLowerCase()) {
		  case 'checkbox':
		  case 'radio':
			Event.observe(element, 'click', this.onElementEvent.bind(this));
			break;
		  case 'password':
		  case 'text':
		  case 'textarea':
			var self = this;
			Event.observe(element, 'keydown', (function() {
			  window.setTimeout(function() {
				self.onElementEvent.apply(self, arguments);
			  });
			}).bind(this));
			break;
		  case 'select-one':
		  case 'select-multiple':
			Event.observe(element, 'change', this.onElementEvent.bind(this));
			break;
		}
	  }
	};
})();
