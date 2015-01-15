<?php
/*
 * Plugin Name: Link To Bible
 * Description: Automatically links bible references in posts to the appropriate bible verse(s) at bibleserver.com
 * Version: 2.3.2
 * Plugin URI: https://wordpress.org/extend/plugins/link-to-bible/
 * Author: Thomas Kuhlmann
 * Author URI: http://oss.thk-systems.de
 * Min WP Version: 3.2.1
 * Max WP Version: 4.1
 * Text Domain: ltb
 */

/*
 * License: GPLv3, see 'license.txt'
 * Published with the explicit approval of bibleserver.com / ERF Medien e.V. (06.12.2011)
 */

// --------------------------------------------------
// -------- DEFINITIONS ----------------------------
// --------------------------------------------------
$LTB_VERSION = 23;

// --------------------------------------------------
// ---------- INIT ---------------------------------
// -------------------------------------------------
load_plugin_textdomain ( 'ltb', false, basename ( dirname ( __FILE__ ) ) . '/languages' );

// ------------------------------------------------
// --------- ADMIN --------------------------------
// ------------------------------------------------

// Show errors
add_action ( 'admin_notices', 'ltb_show_admin_notices' );

function ltb_show_admin_notices() {
	$hash = ltb_get_transient_hash ();
	$message = get_transient ( $hash );
	
	if ($message) {
		echo sprintf ( '<div id="message" class="error"><p>%s</p></div>', $message );
	}
	delete_transient ( $hash );
}

function ltb_get_transient_hash() {
	return md5 ( sprintf ( 'LTB_%s_%s', get_the_ID (), wp_get_current_user ()->ID ) );
}

// --------------------------------------------------
// ----------- DE-/ACTIVATION -----------------------
// --------------------------------------------------

register_activation_hook ( __FILE__, 'ltb_on_activation' );

function ltb_on_activation() {
	$options = ltb_get_options ();
	if (! $options ['apikey']) {
		set_transient ( ltb_get_transient_hash (), sprintf ( __ ( "<b>Link To Bible</b>: Please go to the %ssettings-page%s to set the API-Key and select the bible version. (No registration is needed!)", "ltb" ), '<a href="options-general.php?page=ltb_plugin">', '</a>' ), 120 );
	}
}

// -------------------------------------------------
// ---------- CONTENT-FILTERING --------------------
// -------------------------------------------------
add_filter ( 'the_content', 'ltb_show_post' );

// ... on showing a post
function ltb_show_post($content) {
	$options = ltb_get_options ();
	global $post;
	if (! get_post_meta ( $post->ID, 'LTB_DISABLE', true ) && (! get_post_meta ( $post->ID, '_ltb_last', true ) || (get_post_meta ( $post->ID, '_ltb_translation', true ) != ltb_get_bible_version ( $options, $post )))) {
		wp_insert_post ( $post ); // Do the filtering by saving the post to avoid side-effects with other filtering plugins
		return ltb_add_links ( $content, $post, $options, true ); // Also use the filter here because of some filters of other plugins
	} else {
		return $content;
	}
}

add_filter ( 'content_save_pre', 'ltb_save_post' );

// ... on saving a post
function ltb_save_post($content) {
	global $post;
	if (! get_post_meta ( $post->ID, 'LTB_DISABLE', true )) {
		$options = ltb_get_options ();
		$version = ltb_get_bible_version ( $options, $post );
		update_post_meta ( $post->ID, '_ltb_last', time () );
		update_post_meta ( $post->ID, '_ltb_translation', $version );
		update_post_meta ( $post->ID, '_ltb_version', $GLOBALS ['LTB_VERSION'] );
		update_post_meta ( $post->ID, '_ltb_lang', ltb_get_language_for_bible_version ( $version, $options ) );
		return ltb_add_links ( $content, $post, $options );
	} else if (get_post_meta ( $post->ID, '_ltb_last', true )) {
		delete_post_meta ( $post->ID, '_ltb_last' );
		delete_post_meta ( $post->ID, '_ltb_translation' );
		delete_post_meta ( $post->ID, '_ltb_version' );
		delete_post_meta ( $post->ID, 'ltb_lang' );
		return $content;
	} else {
		return $content;
	}
}

// Add the links to the content
function ltb_add_links($content, $post, $options, $ignore_errors = false) {
	$content = ltb_mark_to_ignore_false_positive ( $options, $content );
	$result = ltb_ask_bibleserver ( $options, $content, $post );
	
	// Check, that there is no empty result
	if (! $result) {
		if (! $ignore_errors) {
			set_transient ( ltb_get_transient_hash (), 'Link-To-Bible Error: Error while connecting bibleserver.com', 10 );
		}
		return $content;
	}
	
	// Check, that the result is no error-string (application-level)
	$result_start = substr ( $result, 10 );
	if ($result_start == substr ( $content, 10 ) or (strpos ( $result_start, "<" ))) {
		return $result;
	}
	
	// If result is an error, print it, and return orig-content
	if (! $ignore_errors) {
		$error = sprintf ( '%s: "%s"', 'Link-To-Bible Error', $result );
		set_transient ( ltb_get_transient_hash (), $error, 10 );
	}
	return $content;
}

// Mark any content, that should not be linked (well known problems, like "Am 1.1.1970")
function ltb_mark_to_ignore_false_positive($options, $content) {
	if (! $options ['ignore_false_positive'])
		return $content;
	$patterns = array (
			"am\s+[0-3]?\d\.[0-1]?\d.\d{0,4}" 
	);
	foreach ( $patterns as $pattern ) {
		$content = preg_replace ( "/(<span class=.*nolink.*>)?($pattern)(<\/span>)?/i", "<span class=\"nolink\">$2</span>", $content );
	}
	
	return $content;
}

function ltb_ask_bibleserver($options, $content, $post) {
	// Check, if configured
	if (! $options ['apikey'])
		return __ ( "You need to set an API-Key", "ltb" );
		
		// POST-Daten definieren
	$version = ltb_get_bible_version ( $options, $post );
	$params = array (
			'key' => $options ['apikey'],
			'text' => $content,
			'lang' => ltb_get_language_for_bible_version ( $version, $options ),
			'trl' => $version 
	);
	
	// return ltb_http_post_request ( 'http://www.thomas-kuhlmann.de/php/ltb-log.php', $params );
	return ltb_http_post_request ( 'http://www.bibleserver.com/api/parser', $params );
}

// ---------------------------------------------
// ------------- OPTIONS -----------------------
// ---------------------------------------------
function ltb_get_options() {
	$options = get_option ( 'ltb_options' );
	$options = ltb_check_for_options_update ( $options );
	return $options;
}

function ltb_check_for_options_update($options) {
	// New installation
	if (! $options) {
		$options = array (
				'ignore_false_positive' => '1',
				'refformatlang' => '1' 
		);
		update_option ( 'ltb_options', $options );
	}
	// Retrieve api-key
	if (! $options ['apikey']) {
		$apikey = ltb_retrieve_apikey ();
		if ($apikey) {
			$options ['apikey'] = $apikey;
			$options ['aak_on'] = '1';
			$options ['aak_domain'] = get_option ( 'siteurl' );
			update_option ( 'ltb_options', $options );
		}
	}
	// API-Key: Check for changed domain-name
	if ($options ['aak_on'] && $options ['aak_domain'] != get_option ( 'siteurl' )) {
		$apikey = ltb_retrieve_apikey ();
		if ($apikey) {
			$options ['apikey'] = $apikey;
			$options ['aak_domain'] = get_option ( 'siteurl' );
			update_option ( 'ltb_options', $options );
		}
	}
	// Set ltb-version
	if (! $options ['ltbver']) {
		$options ['ltbver'] = $GLOBALS ['LTB_VERSION'];
		update_option ( 'ltb_options', $options );
	}
	// Set bible-language
	if (! $options ['biblelang']) {
		$options ['biblelang'] = ltb_get_locale ();
		update_option ( 'ltb_options', $options );
	}
	return $options;
}

// Get locale
function ltb_get_locale() {
	// Check, if the language is set for ltb in globals, otherwise use system-locale, if this is not defined, use 'en' as default
	$locale = $GLOBALS ['LTBLANG'];
	if (empty ( $locale )) {
		$locale = get_locale ();
	}
	if (empty ( $locale )) {
		$locale = 'en';
	}
	// Shorten locale, because bibleserver.com needs that this way (ISO 639)
	if ((strlen ( $locale ) > 2) and (strpos ( $locale, "_" ))) {
		$locale = substr ( $locale, 0, strpos ( $locale, "_" ) );
	}
	// Check, if locale is supported by bibleserver.com, otherwise return 'en' as default locale
	if (in_array ( $locale, array_keys ( ltb_get_masterdata () ) )) {
		return $locale;
	}
	return 'en';
}

function ltb_retrieve_apikey() {
	$params = array (
			'apikey_content' => get_option ( 'siteurl' ),
			'apikey_send' => 'API-Key+generieren' 
	);
	$html = ltb_http_post_request ( "http://www.bibleserver.com/webmasters/index.php#apikey", $params );
	
	$pattern = "/<input.*name=\"apikey_result\".*value=\"(.+)\".*\/>/";
	if (preg_match ( $pattern, $html, $matches )) {
		return $matches [1];
	} else {
		return null;
	}
}

// ---------------------------------------------
// --------------- DATA ------------------------
// ---------------------------------------------
function ltb_get_masterdata($jsonfile = "bibleversions.json") {
	$json = file_get_contents ( plugin_dir_path ( __FILE__ ) . "resources/$jsonfile" );
	return json_decode ( $json, true );
}

// Return the used bible version
function ltb_get_bible_version($options, $post) {
	$bibleversion = $options ['translation'];
	if ($post) {
		$post_bible_version = get_post_meta ( $post->ID, 'LTB_BIBLEVERSION', true );
		if (! empty ( $post_bible_version )) {
			$bibleversion = $post_bible_version;
		}
	}
	$masterdata = ltb_get_masterdata ( "versionsmapping.json" );
	if (array_key_exists ( $bibleversion, $masterdata )) {
		$bibleversion = $masterdata [$bibleversion];
	}
	return $bibleversion;
}

// Returns the language of the given bible version
function ltb_get_language_for_bible_version($searched_version_key, $options) {
	if ($options ['refformatlang']) {
		foreach ( ltb_get_masterdata () as $lang => $lang_data ) {
			foreach ( $lang_data ['bible_versions'] as $version_key => $version_name ) {
				if ($version_key == $searched_version_key) {
					return $lang;
				}
			}
		}
	}
	return ltb_get_locale ();
}

// function ltb_create_dbtables() {
// global $wpdb;
// $table_name = $wpdb->prefix . 'ltb_bibleverse_post';
// $charset_collate = $wpdb->get_charset_collate ();
//
// $sql = "CREATE TABLE $table_name (
// `id` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
// `post_id` BIGINT(20) UNSIGNED NOT NULL,
// `bible_book` INT(3) UNSIGNED NOT NULL,
// `bible_chapter` INT(3) UNSIGNED,
// PRIMARY KEY (`id`),
// INDEX `verse_path` (`bible_book`, `bible_chapter`),
// INDEX `post` (`post_id`),
// UNIQUE KEY id (id)
// ) $charset_collate;";
//
// require_once (ABSPATH . 'wp-admin/includes/upgrade.php');
// dbDelta ( $sql );
// }

// ------------------------------------------------------
// --------------- SETTINGS-PAGE ------------------------
// ------------------------------------------------------
add_action ( 'wp_enqueue_script', 'ltb_load_jquery' );

// Use jquery within
function ltb_load_jquery() {
	wp_enqueue_script ( 'jquery' );
}

add_action ( 'admin_init', 'ltb_admin_init' );
add_action ( 'admin_menu', 'ltb_add_admin_page' );

function ltb_admin_init() {
	register_setting ( 'ltb_plugin_options', 'ltb_options', 'ltb_validate_options' );
}

function ltb_validate_options($input) {
	if ($input ['aak_on']) {
		$apikey = ltb_retrieve_apikey ();
		if ($apikey) {
			$input ['apikey'] = $apikey;
			$input ['aak_domain'] = get_option ( 'siteurl' );
		} else {
			add_settings_error ( 'apikey', 'error', __ ( 'The API-Key could not be retrieved.', 'ltb' ) );
			$options = ltb_get_options ();
			$input ['apikey'] = $options ['apikey_man'];
			unset ( $input ['aak_on'] );
		}
	} else {
		if (! $input ['apikey']) {
			add_settings_error ( 'apikey', 'error', __ ( 'The API-Key must be set.', 'ltb' ) );
		}
	}
	return $input;
}

function ltb_add_admin_page() {
	add_options_page ( 'Link To Bible', 'Link To Bible', 'manage_options', 'ltb_plugin', 'ltb_options_page' );
}

function ltb_options_page() {
	$options = ltb_get_options ();
	
	if (! $options ['aak_on']) {
		$options ['apikey_man'] = $options ['apikey'];
		update_option ( 'ltb_options', $options );
	}
	?>

<script type="text/javascript">
	jQuery(document).ready(function($) {
		// Set bibleversions depending on selected language
		jQuery("#langsel").on('change keyup', function() {
			var $langsel = $(this);
			jQuery.getJSON('<?php print plugin_dir_url ( __FILE__ ) . "resources/bibleversions.json" ?>', function(data) {
				var $key = $langsel.val();
				var $vals = data[$key].bible_versions;
				var $bversel = jQuery("#bversel");
				var $curlang = "<?php print $options['translation']; ?>";
				$bversel.empty();
				jQuery.each($vals, function(key, value) {
					$bversel.append("<option value='" + key + "'" + ($curlang==key ? "selected" : "") +  ">" + value + "</option>");
				});
			});
		}).trigger('change');
		
		// Auto-Set of API-Key
		jQuery("#ltb_aak_cb").change(function() {
			jQuery("#ltb_apikey_inp").prop("disabled", this.checked);
			jQuery("#ltb_apikeynote").css("display", this.checked ? "none" : "");
		}).trigger('change');
	});
</script>

<div class="wrap">
	<h2><?php _e('Link To Bible Settings', 'ltb'); ?> </h2>

	<form action="options.php" id="ltb_options_form" method="post">
			<?php settings_fields('ltb_plugin_options'); ?>

			<table class="form-table">
			<tr>
				<th scope="row">Bibleserver.com API-Key</th>
				<td><p>
						<input type="checkbox" id="ltb_aak_cb" name="ltb_options[aak_on]" value="1" <?php checked( 1 == $options['aak_on'] ); ?> /> <?php _e("Retrieve API-Key automatically", "ltb")?>
					</p>
					<p>
						<input type="text" id="ltb_apikey_inp" size="60" name="ltb_options[apikey]" value="<?php echo $options['apikey']; ?>" />
					</p>
					<p class="description" id="ltb_apikeynote"><?php printf(__('The API-Key can be get %shere%s. No registration is needed!<br>You need to use the address of your blog (%s) as the domainname.', 'ltb'), '<a href="http://www.bibleserver.com/webmasters/#apikey" target="_blank">', '</a>', get_option('siteurl')) ?></p>
			
			</tr>

			<tr>
				<th scope="row"><?php _e('Bible Version', 'ltb') ?></th>
				<td><p>
						<select id='langsel' name='ltb_options[biblelang]'>
							<?php foreach(ltb_get_masterdata() as $key => $value) { ?>
								<option value='<?php echo $key ?>' <?php selected($key, $options['biblelang']); ?>><?php echo $value['name'] ?></option>
							<?php } ?>	
						</select>&nbsp;&nbsp;&nbsp;<select id='bversel' name='ltb_options[translation]'></select>
					</p>
					<p class="description"><?php _e('Attention: Some bible versions may not contain the text of the whole bible.', 'ltb') ?></p></td>
			</tr>

			<tr>
				<th scope="row"><?php _e('Other settings', 'ltb') ?></th>
				<td><input type="checkbox" name="ltb_options[ignore_false_positive]" value="1"
					<?php checked( 1 == $options['ignore_false_positive'] ); ?> /> <?php _e("Ignore False-Positives", "ltb")?>
						<p class="description"><?php _e('Some statements are detected by bibleserver.com as bible references which are no ones.', 'ltb') ?></p>
					<br> <input type="checkbox" name="ltb_options[refformatlang]" value="1" <?php checked( 1 == $options['refformatlang'] ); ?> /> <?php _e("Use the language of a post's bible version for detecting bible references", "ltb")?>
					<p class="description"><?php printf(__("The format of bible references depends on the used language. (e.g. English &#8594; 'Gen 1:20', German &#8594; 'Mose 1,20')<br>Therefore you can use the language of wordpress [%s] for all posts, or the language of the bible version of the particular post.", 'ltb'), ltb_get_locale())  ?></p>
				</td>
			</tr>
		</table>

		<p class="submit">
			<input name="ltb_submit" type="submit" class="button-primary" value="<?php _e('Submit Changes', 'ltb') ?>" />
		</p>

	</form>
</div>
<?php
}

// Display a Settings link on the main Plugins page
add_filter ( 'plugin_action_links', 'ltb_plugin_action_links', 10, 2 );

function ltb_plugin_action_links($links, $file) {
	if ($file == plugin_basename ( __FILE__ )) {
		$ltb_links = '<a href="' . get_admin_url () . 'options-general.php?page=ltb_plugin">' . __ ( 'Settings' ) . '</a>';
		array_unshift ( $links, $ltb_links );
	}
	return $links;
}

// ------------------------------------------------------
// ------------------ COMMONS ---------------------------
// ------------------------------------------------------
function ltb_http_post_request($url, $params) {
	if (function_exists ( 'curl_init' )) {
		$ch = curl_init ();
		curl_setopt ( $ch, CURLOPT_URL, $url );
		curl_setopt ( $ch, CURLOPT_REFERER, get_option ( 'siteurl' ) );
		curl_setopt ( $ch, CURLOPT_POST, true );
		curl_setopt ( $ch, CURLOPT_POSTFIELDS, $params );
		curl_setopt ( $ch, CURLOPT_RETURNTRANSFER, true );
		curl_setopt ( $ch, CURLOPT_USERAGENT, "Wordpress_LinkToBible_" . $GLOBALS ['LTB_VERSION'] );
		$result = curl_exec ( $ch );
		curl_close ( $ch );
		return $result;
	} else {
		$http = array (
				'http' => array (
						'method' => 'POST',
						'content' => http_build_query ( $params ),
						'user_agent' => "Wordpress_LinkToBible_" . $GLOBALS ['LTB_VERSION'],
						'header' => "Referer: " . get_option ( 'siteurl' ) 
				) 
		);
		$ctx = stream_context_create ( $http );
		$fp = fopen ( $url, 'rb', false, $ctx );
		$result = stream_get_contents ( $fp );
		fclose ( $fp );
		return $result;
	}
}

?>
