<?php

class LTBOptions {
	
	const CURRENT_VERSION = 2.0;

	const OPTION_VERSION = 'configuration_version';
	const OPTION_APIKEY = 'apikey';
	const OPTION_SITEURL = 'siteurl';
	const OPTION_LOCALE = 'lang';
	const OPTION_TRANSLATION = 'translation';
	const OPTION_IGNORE_FALSE_POSITIV = 'ignore_false_positive';
	const OPTION_LAZY_UPDATE = 'lazy_update';

	private $options;

	public function __construct() {
		$this->options = get_option( "ltb_options" );
		$this->update_options();
	}


	protected function update_options() {
		$installed_version = $this->get_option(self::OPTION_VERSION);

		switch($installed_version) {
			case self::CURRENT_VERSION:
				return;
			default:
				$this->set_option(self::OPTION_IGNORE_FALSE_POSITIV, 1);
				$this->set_option(self::OPTION_LAZY_UPDATE, 1);
				break;
		}
		$this->set_option(self::OPTION_VERSION, self::CURRENT_VERSION);				
	}

	public function get_option($name) {
		return $this->options[$name];
	}
	
	public function set_option($name, $value) {
		$this->options[$name] = $value;
		update_option("ltb_options", $this->options);
	}
	
	public function get_locale() {
		// Check, if there is a locale defined in ltb-options, otherwise use system-locale, if this is not defined, use 'en' as default
		$locale = $this->get_option(self::OPTION_LOCALE);
		if(empty($locale))
			$locale=$GLOBALS['LTBLANG'];
		if(empty($locale))
			$locale = get_locale();
		if(empty($locale))
			$locale = 'en';
	
		// Shorten locale, because bibleserver.com needs that this way
		if((strlen($locale) > 2) and (strpos($locale, "_")))
			$locale = substr($locale, 0, strpos($locale, "_"));
	
		// Check, if locale is supported by bibleserver.com, otherwise return 'en' as default locale
		if(in_array($locale, array('de', 'en', 'fr', 'it', 'nl', 'es', 'pt', 'no', 'sv', 'da', 'pl', 'cs', 'sk', 'hu', 'ro', 'bg', 'hr', 'ru', 'tr', 'zh', 'ar')))
			return $locale;
		return 'en';
	}

}

?>