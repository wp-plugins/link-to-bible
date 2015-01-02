=== Link To Bible ===
Contributors: Thomas Kuhlmann
Tags: bible, bible verse, bible reference, bibleserver.com, bibelvers, bibel
Requires at least: 3.2.1
Tested up to: 4.1
Stable tag: 2.2.0
License: GPLv3 or later
License URI: https://www.gnu.org/copyleft/gpl.html

Links bible-references in posts automatically to the appropriate bible-verse(s) at bibleserver.com.

== Description ==

Link-To-Bible links any bible reference (e.g. "Genesis 20,1" (en) or "Lukas 5,3" (de)) you write in a post automatically to the appropriate bible verse(s) at bibleserver.com.

You can hereby choose from many different bible versions in different languages. (See http://www.bibleserver.com/webmasters/index.php for all availabe languages and bible versions.)

Bibleserver.com detects language specific the most common notations of bible references.

= Notes =
- This plugin uses the webservice of bibleserver.com
- The links to bibleserver.com are added while saving a post, because the requests to bibleserver.com are limited per day and site. For posts created before activating 'Link-To-Bible' and never saved since then, the links to bibleserver.com are added when the post is viewed the first time after the activation of Link-To-Bible.  
- Changing an already linked bible reference (e.g. "Gen 1,2" -> "Gen 2,1"), saving the post will automatically update the link to bibleserver.com.

= Privacy =
The following information is transmitted to bibleserver.com to add the links:<ul>
<li>Your API-Key</li>
<li>The URL of your blog</li>
<li>The selected bible version</li>
<li>The text of your post including all markups (and may be content or markup added by other plugins or themes)</li>
</ul>

= License =
- This plugin is licensed under the GPLv3. (http://www.gnu.org/licenses/gpl.html). You can use it free of charge on your personal or commercial blog.
- It is published with the explicit permission of bibleserver.com (ERF Medien e.V.)

= Translation =
Although the bible versions at bibleserver.com are available in many different languages, the plugin itself is just available in english and german language. If you would like to contribute a translation for another language, please contact <mail@thomas-kuhlmann.de> .

== Installation ==

= Prerequisites =
- Link-To-Bible uses the php5-curl-library (http://php.net/curl; Debian/Ubuntu/RPM-Package: 'php5-curl') - You need to restart your webserver after installing.

= Steps =
1. Search for link-to-bible in your WordPress backend and click install, or download the link-to-bible.zip file and unzip it.
2. If you have downloaded the zip, move the 'link-to-bible' folder into [WORDPRESS]/wp-content/plugins folder 
3. Activate the plugin in your WordPress Admin area.
4. Select "Settings" to set the API-Key for bibleserver.com (no registration is needed!) and to choose a bible version.

== Frequently Asked Questions ==

= How can I disable the linking of a bible-reference? =
You can mark any text with the css-class 'nolink' to avoid linking it to bibleserver.com; e.g. <code><span class="nolink">Mt 2,10 or Gen 5,11 will not be linked to bibleserver.com</span></code>.
To disable the linking of a whole post, just add the metadata 'LTB_DISABLE' to the post. (Adding 'LTB_DISABLE' to an existing post with existing links to bibleserver.com will not remove these existing links.)

= I got the error (in the log of the webserver): 'Call to undefined function curl_init()' =
Please install the curl-library for php5 (package 'php5-curl' using Debian) and restart the webserver.

= Can I set the bible version per post? =
Yes. You can set the bible version using the metadata of a post with 'LTB_BIBLE_VERSION' and the abbreviation codes from bibleserver.com (http://www.bibleserver.com/webmasters/), e.g. set the metadata LTB_BIBLE_VERSION=KJV to use the 'King James Version' for this post. 

= I have a question / The plugin does not work for me / I have a feature request ... =
If you have any issues with the plugin, please write to mail@thomas-kuhlmann.de (german or english).

== Changelog ==

= 2.2.0 =

- Link To Bible can be disabled for a single post using metadata 'LTB_DISABLE'
- Performance optimizations

= 2.1.1 =

- Fixed typos, translation and html problems. 

= 2.1.0 =

- The language of the available bible versions can be set in the settings now. 
- The bible version per post can be set using post's metadata ('LTB_BIBLEVERSION') now.
- Link-To-Bible now checks for the availability of needed curl-php5-library
- Revised error-handling, some refactorings
- Some minor bugfixes, changes
- Improved documentation
- Updated biblelist from bibleserver.com

= 2.0.1 =

- Link-To-Bible now adds the links to bibleserver.com also to old posts, when they are viewed the first time.
- Link-To-Bible now changes the links, if the selected bible version is changed. (The links are changed the first time the post is viewed or saved.)

= 1.1.3 =

- Bugfix regarding issue "unexpected end of file'"
- Some minor bugfixes, changes 

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
