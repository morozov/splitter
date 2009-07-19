/**
 * Лог закачки.
 *
 * @version	 $Id$
 */
Splitter.Log = Class.create();
Splitter.Log.prototype = {

	/**
	 * Таблица, в которую будут добавляться записи
	 */
	_table: null,

	/**
	 * Количество записей в таблице
	 */
	_count: 0,

	/**
	 * Конструктор.
	 *
	 * @access  public
	 * @param   HTMLElement  button
	 * @param   integer  timeout
	 */
	initialize: function(container)
	{
		var table = document.createElement('table');
		table.cellSpacing = 0;
		table.cellPadding = 0;

		$(container).appendChild(table);

		this._table = table;
	},

	/**
	 * Регистрирует сообщение в логе.
	 *
	 * @access  public
	 * @param   integer  type
	 * @param   string   date
	 * @param   string	message
	 */
	register: function(type, date, message)
	{
		var first = true,
			self = this;

		var count = ++self._count;

		$A(message.split(/[\r\n]+/)).each(function(line)
		{
			// добавляем в таблицу лога строку записи
			var row = self._table.insertRow(-1);
			row.className = type;

			// добавляем ячейку с иконкой
			var cell1 = row.insertCell(-1);

			// добавляем ячейку с датой
			var cell2 = row.insertCell(-1);

			// добавляем ячейку с сообщением
			var cell3 = row.insertCell(-1);

			if (first)
			{
				// рисуем иконку только для первой записи из блока
				cell1.className = 'icon';
				cell1.appendChild(document.createTextNode(count));

				// выводим дату только для первой записи из блока
				cell2.appendChild(document.createTextNode(date));

				first = false;
			}

			cell2.className = 'datetime';
			$(cell3.appendChild(document.createElement('pre'))).update(line);
		});

		// прокручиваем лог вниз
		this._table.parentNode.scrollTop = this._table.scrollHeight;
	},

	/**
	 * Очищает лог.
	 *
	 * @access  public
	 */
	clear: function()
	{
		while (this._table.rows.length > 0)
		{
			this._table.deleteRow(0);
		}

		this._count = 0;
	}
};
