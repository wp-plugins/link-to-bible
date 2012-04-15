<?php

class LTBTools {
	
	public static function get_transient_hash() {
		return md5( sprintf('LTB_%s_%s', get_the_ID(), wp_get_current_user()->ID));
	}
	
}