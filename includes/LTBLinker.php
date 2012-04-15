<?php

class LTBLinker {

	protected $configuration;

	public function __construct(LTBOptions $configuration) {
		$this->configuration = $configuration;
		add_filter('content_save_pre', array($this, 'add_links_on_save'));
	}

	public function add_links_on_save($content) {
		// Filter
		$content = $this->mark_to_ignore_false_positive($content);
		$result = $this->ask_bibleserver($content);

		// Check, that there is no empty result
		if(!$result)
			return $content;

		// Check, that the result is no error-string
		$result_start = substr($result, 10);
		if($result_start==substr($content, 10) or (strpos($result_start,"<")))
			return $result;

		// If result is an error, print it, and return orig-content
		$error = sprintf('%s: "%s"', __('Error while linking to bible', 'ltb'), $result);
		set_transient(LTBTools::get_transient_hash(), $error, 10);
		return $content;
	}

	// Mark any content, that should not be linked (well known problems, like "Am 1.1.1970")
	protected function mark_to_ignore_false_positive($content) {
		if(!$this->configuration->get_option(LTBOptions::OPTION_IGNORE_FALSE_POSITIV))
			return $content;

		$patterns = array (
				"am\s+[0-3]?\d\.[0-1]?\d.\d{0,4}",
		);

		foreach($patterns as $pattern) {
			$content = preg_replace(
					"/(<span class=.*nolink.*>)?($pattern)(<\/span>)?/i",
					"<span class=\"nolink\">$2</span>",
					$content);
		}

		return $content;
	}

	protected function ask_bibleserver($content) {
		// Check, if configured
		if(!$this->configuration->get_option(LTBOptions::OPTION_APIKEY))
			return __("You need to set an API-Key", "ltb");

		// POST-Daten definieren
		$param = array(
				'key' => $this->configuration->get_option(LTBOptions::OPTION_APIKEY),
				'text' => $content,
				'lang' => $this->configuration->get_locale(),
				'trl' => $this->configuration->get_option(LTBOptions::OPTION_TRANSLATION),
		);

		// Doing POST-Request
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, 'http://www.bibleserver.com/api/parser');
		curl_setopt($ch, CURLOPT_REFERER, $this->configuration->get_option(LTBOptions::OPTION_SITEURL));
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $param);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

		$result = curl_exec($ch);

		curl_close($ch);

		return $result;
	}

}
?>