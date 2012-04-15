<?php

class LTBPreferences {
	
	protected $options;
	protected $linker;
	
	public function __construct(LTBOptions $options, LTBLinker $linker) {
		$this->options = $options;
		$this->linker = $linker;
		add_options_page('Link To Bible', 'Link To Bible', 'manage_options', 'ltb_plugin', array(this, 'ltb_options_page'));
	}

	public function ltb_options_page() { ?>
		<div class="wrap">
			<h2>Link To Bible</h2>
	
			<!-- <form action="options.php"  method="post"> -->
			<form method="post">
				<?php settings_fields('ltb_plugin_options'); ?>
				<?php $translations = $this->ltb_get_available_bible_translations(); ?>
			
				<div id="poststuff" class="poststuff">
				<div class="postbox">
	
					<h3><?php _e('Settings', 'ltb'); ?> </h3>
					
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
					
		</form> 
			
		</div>
	<?php }
		
	
	// Returns the available bible-translations for the set locale
	protected function ltb_get_available_bible_translations() {
		$locale = $this->options.get_locale();
	
		switch($locale) {
			case "de":
				return array(
				"SLT" => "Schlachter 2000",
				"LUT" => "Luther 1984",
				"NGÃœ" => "Neue Genfer Übersetzung",
				"ELB" => "Rev. Elberfelder",
				"HFA" => "Hoffnung für alle",
				"GNB" => "Gute Nachricht Bibel",
				"EU" => "Einheitsübersetzung",
				"NL" => "Neues Leben",
				"NeÃœ" => "Neue evangelistische Übersetzung",
				);
	
			case "fr":
				return array(
				"BDS" =>	"Bible du Semeur",
				"S21" => 	"Segond 21",
				);
	
			case "it":
				return array(
				"ITA"	=> "La Parola è Vita",
				"NRS" => "Nuova Riveduta 2006",
				);
	
			case "nl":
				return array(
				"HTB"	=> "Het Boek",
				);
	
			case "es":
				return array(
				"CST"	=> "Version La Biblia al Dia",
				"NVI" => "Nueva Versión Internacional",
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
				"DK" =>	"Bibelen på hverdagsdansk",
				);
	
			case "pl":
				return array(
				"POL"	=> "Słowo Życia",
				);
	
			case "cs":
				return array(
				"CEP"	=> "Český ekumenický překlad",
				"SNC" => "Slovo na cestu",
				"B21" => "Bible, překlad 21. století",
				);
	
			case "sk":
				return array(
				"NPK"	=> "Nádej pre kazdého",
				);
	
			case "hu":
				return array(
				"KAR" => "IBS-fordítás (Új Károli)",
				"HUN"	=> "Hungarian",
				);
	
			case "ro":
				return array(
				"NTR"	=> "Noua traducere în limba românã",
				);
	
			case "bg":
				return array(
				"BLG" => "Българската Библия",
				);
	
			case "ru":
				return array(
				"RUS" => "Новый перевод на русский язык",
				"CRS" => "Священное Писание",
				);
	
			case "tr":
				return array(
				"TR" => "Türkçe",
				);
	
			case "hr":
				return array(
				"CRO" => "Hrvatski",
				);
	
			case "ar":
				return array(
				"ARA" => "عربي",
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
	
}

?>