<?php
/*
Plugin Name: Link To Bible 
Description: Links bible-references in posts automatically to the appropriate bible-verse(s) at bibleserver.com.
Version: 2.0.0
Plugin URI: https://wordpress.org/extend/plugins/link-to-bible/
Author: Thomas Kuhlmann
Min WP Version: 3.2.1 
Max WP Version: 3.3.1
*/

/*
	License: GPLv3, see 'license.txt'
	Published with the explicit approval of bibleserver.com / ERF Media e.V. (06.12.2011)
*/

// ---------- INCLUDES -----------------------------

include_once 'includes/LTBOptions.php';
include_once 'includes/LTBLinker.php';
include_once 'includes/LTBTools.php';


// ---------- INIT ---------------------------------

load_plugin_textdomain('ltb', false, basename( dirname( __FILE__ ) ) . '/languages' );


// ---------- PLUGIN-CLASS -------------------------

class LTBPlugin {

	protected $configuration; 
	protected $linker;
	
	public function __construct() {
		$this->configuration = new LTBOptions();
		$this->linker = new LTBLinker($this->configuration);
		add_action('admin_notices', array($this, 'show_admin_notices'));		
	}
	
	function show_admin_notices() {
		$hash = LTBTools::get_transient_hash();
		$error = get_transient($hash);
	
		if($error)
			echo sprintf('<div id="message" class="error"><p>%s</p></div>', $error);
	
		delete_transient($hash);
	}
			
}

$ltb_plugin = new LTBPlugin();
