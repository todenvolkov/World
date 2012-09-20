(function(window) {
if (BX.translit) return;

var
	arTransTable = null,
	googleCache = [],
	defaultParams = {
		max_len: 100,
		change_case: 'L', // 'L' - toLower, 'U' - toUpper, false - do not change
		replace_space: '_',
		replace_other: '_',
		delete_repeat_replace: true,
		use_google: false
	},
	r = {
		en: /[A-Z0-9]/i,
		space: /\s/
	};

BX.ext({
	translit: function (str, params)
	{
		if (null == params) params = {};
		for (var i in defaultParams)
		{
			if (typeof params[i] == 'undefined')
				params[i] = defaultParams[i];
		}
		if (params.change_case)
			params.change_case = params.change_case.toUpperCase();

		if (params.use_google && params.callback)
		{
			return (new BX.CGoogleTranslator(str, params)).run();
		}
		else
		{
			var len = str.length;
			var str_new = '';
			var last_chr_new = '';

			for (var i = 0; i < len; i++)
			{
				var chr = str.charAt(i);

				if (r.en.test(chr))
				{
					chr_new = chr;
				}
				else if (r.space.test(chr))
				{
					if (
						!params.delete_repeat_replace
						||
						(i > 0 && last_chr_new != params.replace_space)
					)
						chr_new = params.replace_space;
					else
						chr_new = '';
				}
				else
				{
					var chr_new = __getChar(chr, params.change_case);

					if (null === chr_new)
					{
						if (
							!params.delete_repeat_replace
							||
							(i > 0 && i != len-1 && last_chr_new != params.replace_other)
						)
							chr_new = params.replace_other;
						else
							chr_new = '';
					}
				}

				if (null != chr_new && chr_new.length > 0)
				{
					switch(params.change_case)
					{
						case 'L': chr_new = chr_new.toLowerCase(); break;
						case 'U': chr_new = chr_new.toUpperCase(); break;
					}

					str_new += chr_new;
					last_chr_new = chr_new;
				}

				if (str_new.length >= params.max_len)
					break;
			}

			if (null != params.callback && BX.type.isFunction(params.callback))
			{
				params.callback(str_new)
				return str_new;
			}
			else
				return str_new;
		}
	}
});

BX.CGoogleTranslator = function(str, params)
{
	this.str = str;
	this.params = params;
}

BX.CGoogleTranslator.prototype.run = function()
{
	var res = __checkCache(this.str);
	if (res)
		this.result({translation: res}, true);
	else
		this.translate();
}

BX.CGoogleTranslator.prototype.translate = function()
{
	if (!window.google || typeof(window.google.load) != "function")
	{
		if (BX.browser.IsIE())
		{
			var cb_ie = BX.proxy(this.translate, this);
			var cb = function() {
				setTimeout(function() {
					cb_ie(arguments);
				}, 100);
			}
		}
		else
		{
			var cb = BX.proxy(this.translate, this);
		}

		BX.loadScript('http://www.google.com/jsapi?rnd=' + Math.random(), cb);
	}
	else if (!window.google.language)
	{
		google.load(
			'language', 1, {callback: BX.proxy(this.translate, this)}
		);
	}
	else
	{
		google.language.translate(
			this.str,
			BX.message('LANGUAGE_ID'),
			"en",
			BX.delegate(this.result, this)
		);
	}
}

BX.CGoogleTranslator.prototype.result = function(result, bSkipCache)
{
	if (!bSkipCache)
		googleCache[googleCache.length] = {original: this.str, translation: result.translation};

	this.params.use_google = false;
	BX.translit(result.translation, this.params);
}

/* private static functions */

function __checkCache(str)
{
	for (var i = 0, len = googleCache.length; i < len; i++)
	{
		if (googleCache[i].original == str)
			return googleCache[i].translation;
	}

	return null;
}

function __generateTransTable()
{
	var
		arFrom = (BX.message('TRANS_FROM') || '').split(','),
		arTo = (BX.message('TRANS_TO') || '').split(','),
		i, len;

	arTransTable = [];
	for (i = 0, len = arFrom.length; i < len; i++)
	{
		arTransTable[i] = [arFrom[i], arTo[i]];
	}
}

function __getChar(chr, change_case)
{
	if (null == arTransTable)
		__generateTransTable()

	for (var i=0, len = arTransTable.length; i < len; i++)
	{
		if (chr === arTransTable[i][0])
			return arTransTable[i][1];
	}

	return null;
}

})(window)