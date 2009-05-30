var Splitter = {

	'outputSettings': function()
	{
		Splitter.Settings.getParams().each(function(pair)
		{
			Form.Element.setValue('the-form', pair.key, pair.value);
		});

		Splitter.FormObserver();
	},

	'settingsFromForm': function( )
	{
		Splitter.Settings.getParams().keys().each(function(param)
		{

			// :KLUDGE: morozov для радио-кнопок
			// Form.serialize ведет себя не совсем так, как нам надо для чекбоксов
			if ('storage' == param || 'encoding' == param)
			{
				value = radio_value('the-form', param);
			}
			else
			{
				var element = $(param);

				if (element)
				{
					value = 'checkbox' == element.type
						? element.checked : element.value;
				}
			}

			Splitter.Settings.setParam(param, value);
		});
	},

	'Element': {

		'disable': function( element )
		{
			Splitter.Element.setAbilility( element, false )
		},

		'enable': function( element )
		{
			Splitter.Element.setAbilility( element, true ) ;
		},

		'setAbilility': function( element, ability )
		{
			var element = $( element ) ;

			var className = element.type + '-disabled';

			var method = ability ? 'removeClassName' : 'addClassName' ;

			Element[ method ]( element, className ) ;
			element.disabled = ! ability ;
		}
	},

	'FormObserver': function( )
	{
		// запрещаем указывать имя файла, если скачивается несколько файлов
		Splitter.Element.setAbilility( 'save-as', ! $F( 'url' ).match( '\n' ) ) ;

		/*var isSplittingOff = $F( 'split-size' ) > 0 ;
		Splitter.Element.setAbilility( 'part-from', isSplittingOff ) ;
		Splitter.Element.setAbilility( 'part-to', isSplittingOff ) ;*/

		// параметры прокси-сервера
		var useProxy = $( 'use-proxy' ).checked ;
		Splitter.Element.setAbilility( 'proxy-host', useProxy ) ;
		Splitter.Element.setAbilility( 'proxy-port', useProxy ) ;
		Splitter.Element.setAbilility( 'proxy-user', useProxy ) ;
		Splitter.Element.setAbilility( 'proxy-password', useProxy ) ;

		// параметры автоматического перезапуска
		var autoResume = $( 'use-auto-resume' ).checked ;
		Splitter.Element.setAbilility( 'auto-resume-interval', autoResume ) ;
		Splitter.Element.setAbilility( 'auto-resume-count', autoResume ) ;

		//
		var isPost = radio_value( 'the-form', 'method') == 'post' ;
		Element[ isPost ? 'show' : 'hide' ]( 'post-params' ) ;

		// прячем-показываем поля, специфичные для выбранного типа хранилища
		var storage = radio_value('the-form', 'storage');
		Element[ storage == 'email' ? 'show' : 'hide' ]( 'field-email' ) ;
		Element[ storage == 'file' ? 'show' : 'hide' ]( 'field-file' ) ;

		// переименование по маске
		var rename = $('rename').checked ;
		Splitter.Element.setAbilility('rename-search', rename);
		Splitter.Element.setAbilility('rename-replace', rename);
		Splitter.Element.setAbilility('rename-regexp', rename);
	},

	'onload': function()
	{
		controller = new Splitter.Controller();

		new Form.EventObserver
		(
			'the-form', Splitter.FormObserver
		) ;

		$('the-form').onsubmit = function()
		{
			Splitter.settingsFromForm();
			controller.reset();
		};

		$('the-form').reset();

		// настройки выводим в самом конце
		Splitter.outputSettings();
	}
} ;

function radio_value(form, name)
{
	var checked = Form.getElements(form).detect(function(element)
	{
		return element.name == name && element.checked;
	});

	return checked ? checked.value : null;
}

//
var controller;

function switchOptions()
{
	Element.toggle('options');
}
