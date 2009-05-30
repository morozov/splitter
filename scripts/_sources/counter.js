/**
 * Таймер для скачивания с файловых сервисов.
 *
 * @version	 $Id$
 */
Splitter.Counter = Class.create();
Splitter.Counter.prototype = {

	/**
	 * Флажок, показывающий, что счетчик был отображен на кнопке
	 */
	_displayed: false,

	/**
	 * Конструктор.
	 *
	 * @access  public
	 * @param   HTMLElement  button
	 * @param   integer  timeout
	 * @param   boolean  autostart
	 */
	initialize: function(button, timeout, autostart)
	{
		var form = button.form;

		// сохраняем поведение формы
		var onsubmit = form.onsubmit;

		var self = this,
			now = this._getTimestamp();

		// отключаем отправку формы
		form.onsubmit = function()
		{
			self._onsubmit(button, now, timeout);
			return false;
		};

		if (autostart) {
			form.onsubmit();
		}

		// запускаем задачу восстановления исходного поведения формы
		setTimeout(function()
		{
			form.onsubmit = onsubmit;
		}, timeout * 1000);
	},

	/**
	 * Вызывается при отправке формы, на которую поставлен счетчик.
	 *
	 * @access  private
	 * @param   HTMLElement  button
	 * @param   integer  start
	 * @param   integer  timeout
	 */
	_onsubmit: function(button, start, timeout)
	{
		var now = this._getTimestamp();

		// сколько времени осталось до срабатывания счетчика
		var remain = timeout + start - now;

		if (!this._displayed && remain > 0)
		{
			this._display(button, remain);
			this._displayed = true;
		}
	},

	/**
	 * Отображает счетчик на кнопке.
	 *
	 * @access  private
	 * @param   HTMLElement  button
	 * @param   integer  value
	 */
	_display: function(button, value)
	{
		var text = button.value;

		// для верности добавляем единичку, иначе возможен случай, когда форма,
		// освободится позже, чем сработает таймер на кнопке
		var current = value + 1;

		var handler = function()
		{
			button.value = text + ' ( ' + current + ' )';

			if (current <= 0)
			{
				window.clearInterval(interval);
				button.value = text;

				// не просто отправляем форму, а кликаем кнопку, т.к. ее имя
				// должно быть передано в запрос
				button.click();
			}

			current--;
		};

		// сразу вызываем обработчик, т.к. автоматически он выполнится только
		// через секунду
		handler.call(window);

		var interval = window.setInterval(handler, 1000);
	},

	/**
	 * Возвращает текущую временную метку в формате Unix timestamp.
	 *
	 * @access  private
	 * @return  integer
	 */
	_getTimestamp: function()
	{
		return Math.round(new Date().getTime() / 1000);
	}
};
