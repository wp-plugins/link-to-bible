<?php
/*
Plugin Name: Link To Bible 
Description: Links bible-references in posts automatically to the appropriate bible-verse(s) at bibleserver.com.
Version: 1.2.0
Plugin URI: https://wordpress.org/extend/plugins/link-to-bible/
Author: Thomas Kuhlmann
Min WP Version: 3.2.1 
Max WP Version: 3.3
*/

/*
	License: GPLv3, see 'license.txt'
	Published with the explicit approval of bibleserver.com / ERF Media e.V. (06.12.2011)
*/

// ---------- DEFS ---------------------------------

global $ltb_cfg_version;
$ltb_cfg_version = 1.1;


// ---------- INIT ---------------------------------

load_plugin_textdomain('ltb', false, basename( dirname( __FILE__ ) ) . '/languages' );

register_activation_hook(__FILE__, 'ltb_init');

function ltb_init() {
	ltb_update_options();
}


// ---------- UPDATE ---------------------------------

add_action('plugins_loaded', 'ltb_update');

function ltb_update() {
	ltb_update_options();
}

function ltb_update_options() {
	global $ltb_cfg_version;
	$installed_version = get_option( "ltb_cfg_version" );
			 
	$options = get_option('ltb_options');
	switch($installed_version) {
		case $ltb_cfg_version:
			break;
		default:
			$options['ignore_false_positive'] = 1;
			break;
	}							
	update_option('ltb_options', $options);

	update_option("ltb_cfg_version", $ltb_cfg_version);	
}


// ---------- LINKING TO BIBLESERVER.COM --------------------

add_filter('content_save_pre', 'ltb_add_links');

function ltb_add_links($content) {
	$options = get_option('ltb_options');

	// Filter
	$content = ltb_mark_to_ignore_false_positive($options, $content);
	$result = ltb_ask_bibleserver($options, $content);

	// Check, that there is no empty result
	if(!$result)
		return $content;

	// Check, that the result is no error-string
	$result_start = substr($result, 10);
	if($result_start==substr($content, 10) or (strpos($result_start,"<")))
		return $result;	

	// If result is an error, print it, and return orig-content
	$error = sprintf('%s: "%s"', __('Error while linking to bible', 'ltb'), $result);
	set_transient(ltb_get_transient_hash(), $error, 10);
	return $content;
}

// Mark any content, that should not be linked (well known problems, like "Am 1.1.1970")
function ltb_mark_to_ignore_false_positive($options, $content) {
	if(!$options['ignore_false_positive'])
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

function ltb_ask_bibleserver($options, $content) {
	// Check, if configured
	if(!$options['apikey'])
		return __("You need to set an API-Key", "ltb");

	// POST-Daten definieren
	$param = array(
		'key' => $options['apikey'],
		'text' => $content,
		'lang' => ltb_get_locale(),
		'trl' => $options['translation'], 
	);
    
	// Doing POST-Request
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, 'http://www.bibleserver.com/api/parser');
	curl_setopt($ch, CURLOPT_REFERER, get_option('siteurl'));
	curl_setopt($ch, CURLOPT_POST, true);
	curl_setopt($ch, CURLOPT_POSTFIELDS, $param);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
     
	$result = curl_exec($ch);
     
	curl_close($ch);

	return $result;
}

// Show errors
add_action('admin_notices', 'ltb_show_admin_notices');

function ltb_show_admin_notices() {
	$hash = ltb_get_transient_hash();
	$error = get_transient($hash);
		
	if($error)
		echo sprintf('<div id="message" class="error"><p>%s</p></div>', $error);

	delete_transient($hash);
}


// --------------- OPTIONS-PAGE ------------------------

add_action('admin_init', 'ltb_admin_init' );
add_action('admin_menu', 'ltb_add_admin_page');

function ltb_admin_init(){
	register_setting( 'ltb_plugin_options', 'ltb_options', 'ltb_validate_options' );
}

function ltb_validate_options($input) {
	return $input;
}

function ltb_add_admin_page() {
	add_options_page('Link To Bible', 'Link To Bible', 'manage_options', 'ltb_plugin', 'ltb_options_page');
}

function ltb_options_page() { ?>
	<?php $options = get_option('ltb_options'); ?>
	
	<div class="wrap">
		<h2>Link To Bible</h2>

		<form action="options.php" method="post">
			<?php settings_fields('ltb_plugin_options'); ?>
			<?php $translations = ltb_get_available_bible_translations(); ?>
		
			<div id="poststuff" class="poststuff">
			<div class="postbox">

				<h3><?php _e('Settings', 'ltb'); ?> </h2>
				
				<div class="inside">				

					<table class="form-table">
						<tr>
							<th scope="row">Bibleserver.com API-Key</th>
							<td>
								<input type="text" size="60" name="ltb_options[apikey]" value="<?php echo $options['apikey']; ?>" />
								<p class="description"><?php printf(__('The API-Key can be get %shere%s. You need to use the address of you blog (%s) as the domainname.', 'ltb'), '<a href="http://www.bibleserver.com/webmasters/#apikey" target="_blank">', '</a>', get_option('siteurl')) ?></p>
							</td>
						</tr>

						<tr>
							<th scope="row"><?php _e('Bible-Version', 'ltb') ?></th>
							<td>
								<select name='ltb_options[translation]'>
									<?php foreach($translations as $key => $value) { ?>
										<option value='<?php echo $key ?>' <?php selected($key, $options['translation']); ?>><?php echo $value ?></option>
							<? } ?>	
								</select>
								<p class="description"><?php _e('Attention: Some bible-versions may not contain the text of the whole bible.', 'ltb') ?></p>
							</td>
						</tr>

						<tr>
							<th scope="row"><?php _e('Other settings', 'ltb') ?></th>
							<td>
								<input type="checkbox" name="ltb_options[ignore_false_positive]" value="1" <?php checked( 1 == $options['ignore_false_positive'] ); ?> /> <? _e("Ignore False-Positives", "ltb") ?>
								<p class="description"><?php _e('Some statements are detected by bibleserver.com as bible-references which are no ones.', 'ltb') ?></p>
							</td>
						</tr>

					</table>
			
					<p class="submit">
						<input name="ltb_submit" type="submit" class="button-primary" value="<?php _e('Submit Changes', 'ltb') ?>" />
					</p>
	
				</div>

			</div>
		</div>

		
		<div id="poststuff" class="poststuff">
		<div class="postbox">

			<h3><?php _e('Link existing articles', 'ltb'); ?></h3>

			<div class="inside">
			
				<table class="form-table">
					<tr>
						<td colspan="2">
							<p class="description"><?php _e('Note to linking.', 'ltb') ?></p>
						</td>
					</tr>

					<tr>
						<th scope="row"><?php _e('Count of newest article to link (max. 100)', 'ltb') ?></th>
						<td>
							<input type="text" size="10" name="ltb_link_count" value="100" />
						</td>	
					</tr>
				</table>
			
				<p class="submit">
					<input name="ltb_index" type="submit" class="button-primary" value="<?php _e('Link existing articles', 'ltb') ?>" />
				</p>
		
			</div>

		</div>
		</div>
		
	</div> 
<?php }


// Returns the available bible-translations for the set locale
function ltb_get_available_bible_translations() {
	$locale = ltb_get_locale();

	switch($locale) { 
		case "de":           
		return array(
			"SLT" => "Schlachter 2000",
			"LUT" => "Luther 1984",             
			"NGÃœ" => "Neue Genfer Ãœbersetzung", 
			"ELB" => "Rev. Elberfelder",
			"HFA" => "Hoffnung fÃ¼r alle",       
			"GNB" => "Gute Nachricht Bibel",    
			"EU" => "EinheitsÃ¼bersetzung",      
			"NL" => "Neues Leben",
			"NeÃœ" => "Neue evangelistische Ãœbersetzung",
		);                                    

		case "fr":
		return array(
			"BDS" =>	"Bible du Semeur",
			"S21" => 	"Segond 21",
		);

		case "it":
		return array(
			"ITA"	=> "La Parola Ã¨ Vita",
			"NRS" => "Nuova Riveduta 2006",
		);

		case "nl":
		return array(
			"HTB"	=> "Het Boek",
		);	 

		case "es":
		return array(
			"CST"	=> "Version La Biblia al Dia",
			"NVI" => "Nueva VersiÃ³n Internacional",
			"BTX" => "La Biblia Textual",
		);

		case "pt":
		return array( 
			"PRT" => "O Livro",
		);

		case "no":
		return array(
			"NOR"	=> "En Levende Bok",
		);

		case "sv":
		return array(
			"SVL" => "En Levande Bok",
		);

		case "da":
		return array(
			"DK" =>	"Bibelen pÃ¥ hverdagsdansk",
		);

		case "pl":
		return array(
			"POL"	=> "SÅ‚owo Å»ycia",
		);

		case "cs":
		return array(
			"CEP"	=> "ÄŒeskÃ½ ekumenickÃ½ pÅ™eklad",
			"SNC" => "Slovo na cestu",
			"B21" => "Bible, pÅ™eklad 21. stoletÃ­",
		);

		case "sk":
		return array(
			"NPK"	=> "NÃ¡dej pre kazdÃ©ho",
		);

		case "hu":
		return array(
			"KAR" => "IBS-fordÃ­tÃ¡s (Ãšj KÃ¡roli)",
			"HUN"	=> "Hungarian",
		);

		case "ro":
		return array(
			"NTR"	=> "Noua traducere Ã®n limba romÃ¢nÃ£",
		);

		case "bg":
		return array(
			"BLG" => "Ð‘ÑŠÐ»Ð³Ð°Ñ€Ñ�ÐºÐ°Ñ‚Ð° Ð‘Ð¸Ð±Ð»Ð¸Ñ�",
		);

		case "ru":
		return array(
			"RUS" => "Ð�Ð¾Ð²Ñ‹Ð¹ Ð¿ÐµÑ€ÐµÐ²Ð¾Ð´ Ð½Ð° Ñ€ÑƒÑ�Ñ�ÐºÐ¸Ð¹ Ñ�Ð·Ñ‹",
			"CRS" => "Ð¡Ð²Ñ�Ñ‰ÐµÐ½Ð½Ð¾Ðµ ÐŸÐ¸Ñ�Ð°Ð½Ð¸Ðµ",
		);

		case "tr":
		return array(
			"TR" => "TÃ¼rkÃ§e",
		);

		case "hr":
		return array(
			"CRO" => "Hrvatski",
		);

		case "ar":
		return array(
			"ARA" => "Ø¹Ø±Ø¨ÙŠ",
		);

		case "zh":
		return array(
			"CUVS" =>	"ä¸­æ–‡å’Œå�ˆæœ¬ï¼ˆç®€ä½“ï¼‰",
		);


		default:                        
		return array(          
			"ESV" => "English Standard Version",
			"NIV" => "New International Version",
			"TNIV" => "Today's New International Version",
			"NIRV" => "New Int. Readers Version",
			"KJV" => "King James Version",
		);                                    
	}
}

// Display a Settings link on the main Plugins page
add_filter( 'plugin_action_links', 'ltb_plugin_action_links', 10, 2 );

function ltb_plugin_action_links( $links, $file ) {
	if ( $file == plugin_basename( __FILE__ ) ) {
		$ltb_links = '<a href="'.get_admin_url().'options-general.php?page=ltb_plugin">'.__('Settings').'</a>';
		array_unshift( $links, $ltb_links );
	}
	return $links;
}


// --------------- TOOLS ------------------------

// Get locale 
function ltb_get_locale() {
	// Check, if there is a locale defined in ltb-options, otherwise use system-locale, if this is not defined, use 'en' as default
	$options = get_option('ltb_options');
	$locale = $options['lang'];

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

function ltb_get_transient_hash() {
	return md5( sprintf('LTB_%s_%s', get_the_ID(), wp_get_current_user()->ID));
}

?>
