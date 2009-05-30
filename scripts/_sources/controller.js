/**
 * Контроллер, он же фасад к компонентам приложения
 *
 * @version	 $Id$
 */
Splitter.Controller = Class.create();
Splitter.Controller.prototype = {

	/**
	 * Таблица, в которую будут добавляться записи
	 */
	_log: null,

	/**
	 * Индикатор состояния закачки
	 */
	_indicator: null,

	/**
	 * Состояние закачки
	 */
	_section: null,

	/**
	 *
	 */
	_interactive: false,

	/**
	 * Конструктор.
	 *
	 * @access  public
	 * @param   HTMLElement  button
	 * @param   integer  timeout
	 */
	initialize: function(container)
	{
		this._log = new Splitter.Log('log-container');
		this._indicator = new Splitter.Indicator();
		this.reset();
	},

	/**
	 * Выводит сообщение в лог.
	 *
	 * @access  public
	 * @param   string  type
	 * @param   string  date
	 * @param   string  message
	 */
	trace: function(type, date, message)
	{
		this._log.register(type, date, message);
	},

	/**
	 * Сбрасывает состояние закачки и очищает лог.
	 *
	 * @access  public
	 */
	clear: function()
	{
		this._log.clear();
		this.reset();
	},

	/**
	 * Сбрасывает состояние закачки.
	 *
	 * @access  public
	 */
	reset: function()
	{
		this._section = new Splitter.Section;
		this._update();
	},

	/**
	 * Выводит счетчик и блокирует отправку формы.
	 *
	 * @access  public
	 */
	counter: function(value)
	{
		new Splitter.Counter($('button-download'), value, !this._interactive);
	},

	/**
	 * Выводит картинку captcha и обновляет наименование параметра, которое
	 * нужно передать в запрос на сервер.
	 *
	 * @access  public
	 * @param   string  param
	 * @param   string  src
	 */
	captcha: function(param, src)
	{
		$('captcha-container').show();
		$('captcha-image').src = src;
		this.param('captcha-param', param);
		this._interactive = true;
	},

	/**
	 * Изменяет значение поля, отвечающего за указанный параметр на форме.
	 *
	 * @access  public
	 * @param   string  name
	 * @param   mixed   value
	 */
	param: function(name, value)
	{
		Form.Element.setValue('the-form', name, value);
		Splitter.FormObserver();
	},

	/**
	 * Обработчик события изменения имени файла.
	 *
	 * @access  public
	 * @param   string  name
	 */
	onFileNameChange: function(name)
	{
		this._section.setName(name);
		this._update();
	},

	/**
	 * Обработчик события изменения размера файла.
	 *
	 * @access  public
	 * @param   integer size
	 */
	onFileSizeChange: function(size)
	{
		this._section.setSize(size);
		this._update();
	},

	/**
	 * Обработчик события изменения размера скачанных данных.
	 *
	 * @access  public
	 * @param   integer progress
	 */
	onProgressChange: function(progress)
	{
		this._section.setProgress(progress);
		this._update();
	},

	/**
	 * Обновляет показания индикатора.
	 *
	 * @access  private
	 */
	_update: function()
	{
		this._indicator.update(this._section);
	}
};
