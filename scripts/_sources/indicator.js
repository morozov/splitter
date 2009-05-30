/**
 * Индикатор скачивания.
 *
 * @version	 $Id$
 */
Splitter.Indicator = Class.create();
Splitter.Indicator.prototype = {

	/**
	 * Исходное значение заголовка окна.
	 *
	 * @access  private
	 * @var	 string
	 */
	_caption: document.title,

	/**
	 * Конструктор.
	 *
	 * @access  public
	 */
	initialize: function()
	{
	},

	/**
	 * Обновдяет показатели индикатора в соответствии с параматрами указанной
	 * скачиваемой секции.
	 *
	 * @access  public
	 * @param   section section
	 */
	update: function(section)
	{
		var name = section.getName(),
			size = section.getSize(),
			progress = section.getProgress(),
			percent = section.getPercent(),
			speed = section.getSpeed(),
			remaining = section.getRemaining();

		this._updateCaption(name, percent);
		this._updateFilename(name);
		this._updateSize(size);
		this._updateProgress(progress);
		this._updatePercent(percent);
		this._updateSpeed(speed);
		this._updateRemaining(remaining);
	},

	/**
	 * Обновляет заголовок окна браузера.
	 *
	 * @access  private
	 * @param   string  name
	 * @param   float   percent
	 */
	_updateCaption: function(name, percent)
	{
		var caption = '';

		if (percent > 0)
		{
			caption += percent + '% of ';
		}

		if (name != null)
		{
			caption += name + ' - ';
		}

		document.title = caption + this._caption;
	},

	/**
	 * Обновляет индикатор имени файла.
	 *
	 * @access  private
	 * @param   string value
	 */
	_updateFilename: function(value)
	{
		$('filename-value').update(value || '');
	},

	/**
	 * Обновляет индикатор размера файла.
	 *
	 * @access  private
	 * @param   integer value
	 */
	_updateSize: function(value)
	{
		$('size-value').update(this._formatNumber(value));
	},

	/**
	 * Обновляет индикатор количества скачанных данных.
	 *
	 * @access  private
	 * @param   integer value
	 */
	_updateProgress: function(value)
	{
		$('progress-value').update(this._formatNumber(value));
	},

	/**
	 * Обновляет индикатор процента скачанных данных.
	 *
	 * @access  private
	 * @param   integer value
	 */
	_updatePercent: function(value)
	{
		$('progress-bar').style.width = (value || 0) + '%';
		$('percent-value').update(value !== null ? this._formatNumber(Math.round(value)) : '');
	},

	/**
	 * Обновляет индикатор скорости скачивания.
	 *
	 * @access  private
	 * @param   integer value
	 */
	_updateSpeed: function(value)
	{
		$('speed-value').update(value !== null ? this._formatNumber(Math.round(value)) : '');
	},

	/**
	 * Обновляет индикатор оценки времени до окончания скачивания.
	 *
	 * @access  private
	 * @param   integer value
	 */
	_updateRemaining: function(value)
	{
		$('remaining-value').update(value !== null ? this._formatTime(Math.round(value)) : '');
	},

	/**
	 * Форматирует числовое значение.
	 *
	 * @access  private
	 * @param   integer value
	 * @return  string
	 */
	_formatNumber: function(value)
	{
		var string = value > 0 ? String(value) : '',
			length = string.length,
			formatted = '';

		for (var i = 0; i < length; i++)
		{
			formatted += string.substr(i, 1);

			if ((i < length - 1) && (1 == (length - i) % 3))
			{
				formatted += ' ';
			}
		}

		return formatted;
	},

	/**
	 * Форматирует значение времени.
	 *
	 * @access  private
	 * @param   integer value
	 * @return  string
	 */
	_formatTime: function(value)
	{
		var hours = Math.floor(value / 3600),
			minutes = Math.floor((value - hours * 3600) / 60),
			seconds = value - hours * 3600 - minutes * 60;

		return this._lz(hours) + ':' + this._lz(minutes) + ':' + this._lz(seconds);
	},

	/**
	 * Добавляет ноль в начало числа.
	 *
	 * @access  private
	 * @param   integer value
	 * @return  string
	 */
	_lz: function(value)
	{
		return String(value < 10 ? '0' + value : value);
	}
};
