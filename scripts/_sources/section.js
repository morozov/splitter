/**
 * Инкапсулирует состояние закачки.
 *
 * @version	 $Id$
 */
Splitter.Section = Class.create();

Splitter.Section.prototype = {

	/**
	 * Имя закачиваемого файла
	 */
	_name: null,

	/**
	 * Размер закачиваемого файла
	 */
	_size: null,

	/**
	 * Слепок предыдущего состояния
	 */
	_state1: null,

	/**
	 * Слепок текущего состояния
	 */
	_state2: null,

	/**
	 * Конструктор.
	 *
	 * @access  public
	 */
	initialize: function()
	{
	},

	/**
	 * Возвращает имя файла
	 */
	getName: function()
	{
		return this._name;
	},

	/**
	 * Устанавливает имя файла
	 */
	setName: function(name)
	{
		this._name = name;
	},

	/**
	 * Возвращает размер файла
	 */
	getSize: function()
	{
		return this._size;
	},

	/**
	 * Устанавливает размер файла
	 */
	setSize: function(size)
	{
		this._size = parseInt(size);
	},

	/**
	 * Возвращает размер скачанных данных
	 */
	getProgress: function()
	{
		return this._state2 ? this._state2.progress : null;
	},

	/**
	 * Устанавливаеи прогресс скачивания.
	 */
	setProgress: function(progress)
	{
		// копируем актуальное состояния в предыдущее
		this._state1 = this._state2;

		// изменяем актуальное состояние
		this._state2 = this._createState(progress);
	},

	/**
	 * Возвращает прогресс скачивания в процентах.
	 */
	getPercent: function()
	{
		return this._size > 0 && this._state2
			// используем округление в меньшую сторону для того, чтобы показания
			// индикатора "100%" соответствовали моменту, когда файл полностью
			// закачан, и ни байтом меньше
			? Math.floor(100 * this._state2.progress / this._size) : null;
	},

	/**
	 * Возвращает текущую (мгновенную) скорость скачивания, байт/сек.
	 */
	getSpeed: function()
	{
		return this._state1
			? 1000 * (this._state2.progress - this._state1.progress)
				/ (this._state2.timestamp - this._state1.timestamp)
			: null;
	},

	/**
	 * Возвращает оценку времени, через которое закончится закачка, сек.
	 */
	getRemaining: function()
	{
		var speed = this.getSpeed();

		return speed > 0 && this._size > 0
			? (this._size - this._state2.progress) / speed
			: null;
	},

	/**
	 * Создает объект текущего состояния.
	 */
	_createState: function(progress)
	{
		return new Splitter.Section.State((new Date).getTime(), progress);
	}
};

Splitter.Section.State = function(timestamp, progress)
{
	this.timestamp = timestamp;
	this.progress = progress;
};
