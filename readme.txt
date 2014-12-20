=== Link To Bible ===
Contributors: Thomas Kuhlmann
Tags: bible, bible-verse, bible-reference, bibleserver.com, bibelvers, bibel
Requires at least: 3.2.1
Tested up to: 4.1
Stable tag: 1.1.3

Links bible-references in posts automatically to the appropriate bible-verse(s) at bibleserver.com.

== Description ==

Link-To-Bible links any bible-reference (e.g. "Genesis 20,1" (en) or "Lukas 5,3" (de)) you write in a post automatically to the appropriate bible-verse(s) at bibleserver.com.

You can hereby choose from many different bible-versions in different languages. (See http://www.bibleserver.com/webmasters/index.php for all availabe languages and versions.)

Bibleserver.com detects language-specifc the most common notations of bible-references.

= Notes =
- This plugin uses the webservice of bibleserver.com
- The links to bibleserver.com are added while saving a post, because the requests to bibleserver.com are limited per day and site. 
- Changing an already linked bible-reference (e.g. "Gen 1,2" -> "Gen 2,1"), saving the post will automatically update the link to bibleserver.com.
- Choosing a different bible-version in the settings will not change any existing links to bibleserver.com in existing articles.

= Privacy =
The following information is transmitted to bibleserver.com to add the links.<ul>
<li>Your API-Key</li>
<li>The URL of your blog</li>
<li>The selected bible-translation</li>
<li>The language-code</li>
<li>The text of your post including all markups</li>
</ul>

= License =
- This plugin is licensed under the GPLv3. (http://www.gnu.org/licenses/gpl.html). You can use it free of charge on your personal or commercial blog.
- It is published with the explicit permission of bibleserver.com (ERF Media e.V.)

= Translation =
Allthough the bible-versions at bibleserver.com are available in many different languages, the plugin itselfs is just available in english and german language. If you like to contribute a translation for another language, please contact <thomas@thkuhlmann.de> .

== Installation ==

1. Search for link-to-bible in your WordPress backend and click install, or download the link-to-bible.zip file and unzip it.
2. If you downloaded the zip, upload the 'link-to-bible' folder into wp-content/plugins folder 
3. Activate the plugin in your WordPress Admin area.
4. Select "Settings" to set the API-Key for bibleserver.com and to choose a bible-version.

== Frequently Asked Questions ==

= How can I disable the linking of a bible-reference? =
You can mark any text with the css-class 'nolink' to avoid linking it to bibleserver.com; e.g. <code><span class="nolink">Mt 2,10 or Gen 5,11 will not be linked to bibleserver.com</span></code>.

= I have a question =
If you have any issues with the plugin, please write to thomas@thkuhlmann.de (german or english).

== Changelog ==

= 1.1.3 =

- Bugfix regarding issue "unexpected end of file'"
- Some minor bugfixes 

= 1.1.2 =

- Update bible list
- Some refactoring

= 1.1.1 =

- Some minor bugfixes
- A new major version will be released in the next months.

= 1.1.0 =

- Add the option to ignore false-positive-linkings
- Fix some minor bugs
- Added and translated some error-messages

= 1.0.4 =

- Fix regarding issue with "Cannot modify header information"

= 1.0.2 =

- Fix of some minor bugs

= 1.0.0 =

- Initial version
