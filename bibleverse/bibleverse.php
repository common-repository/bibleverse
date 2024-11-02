<?php
/*
Plugin Name: Bible Verse
Version: 1.0
Plugin URI: http://www.mychurch.org/
Description: Changes Bible references to hyperlinks for Wordpress 1.5 and above. If you have a higher version of Wordpress you might want to upgrade the plugin. Thanks to <a href="http://dev.wp-plugins.org/wiki/Scripturizer">Scripturizer</a> Team for the inspiration.
Author: MyChurch.org Team
Author URI: http://www.mychurch.org/
*/

$bibleverse_translations=array(
	'ASV'=>'American Standard Version',
	'RSV'=>'Revised Standard Version',
	'KJV'=>'King James Version',
	'BBE'=>'Bible in Basic English'
	);

##### ADMIN CONSOLE #

if (! function_exists('bibleverse_add_options')) {
  function bibleverse_add_options() {
    if (function_exists('add_options_page')) {
      add_options_page('Options', 'BibleVerse', 9, basename(__FILE__), 'bibleverse_options_subpanel');
    }
  }
}

// Show the admin page content
function bibleverse_options_subpanel() {
global $bibleverse_translations;

    if (isset($_POST['info_update'])) {
        update_option('bibleverse_default_translation', $_POST['bibleverse_default_translation']);
    ?>
        <div class="updated"><p><strong>
            <?php _e('Updates saved!', 'BibleVerse');?>
        </strong></p></div>
    <?php
    } ?>
    <div class="wrap">
        <h2><?php _e('BibleVerse 1.0', 'BibleVerse'); ?></h2>
        <form method="post">
        <fieldset class="options">
            <legend><b><?php _e('General Options', 'BibleVerse'); ?></b></legend>

            <p>
            <label for="bibleverse_default_translation">
           <b><?php _e('Default Bible Translation', 'BibleVerse'); ?></b>

           <br /><select name="bibleverse_default_translation">
		   <?php
			if (get_option('bibleverse_default_translation') == ''){
                    $bibleverse_default_translation='RSV';
                }else {
                 $bibleverse_default_translation=get_option('bibleverse_default_translation');// this is now defunct -- mess with it
            }
			ksort($bibleverse_translations);
			$translations=array_keys($bibleverse_translations);
			foreach ($translations as $translation) {
				if (strcmp($translation,$bibleverse_default_translation)) {
					echo "<option value='$translation'>".$translation.' ('.$bibleverse_translations[$translation].')</option>';
				} else {
					echo "<option value='$translation' selected>".$translation.' ('.$bibleverse_translations[$translation].')</option>';
				}
			}
			?>
			</select>

           </label>
           </p>

        </fieldset>

    <p class="submit">
    <input type="submit" name="info_update" value="<?php _e('Update Options', 'BibleVerse') ?>" />
    </p>
    </form>

    <fieldset class="options">
        <legend><b><?php _e('Credits: "Give honor to whom honor is due"', 'BibleVerse'); ?></b></legend>

        <p><?php _e('The story is too complicated to tell here. The main props go to <a href="http://www.healyourchurchwebsite.com/">Dean Peters</a> and you can learn more at the <a href="http://dev.wp-plugins.org/wiki/BibleVerse">BibleVerse central page</a>.', 'BibleVerse'); ?></p>
    </fieldset>

    </div>

<?php
} // close bibleverse_options_subpanel()
# END ADMIN CONSOLE #####

function verse($text = '',$bible = NULL) {

	if (!isset($bible)) {
		$bible = get_option('bibleverse_default_translation');
	}
    // skip everything within a hyperlink, a <pre> block, a <code> block, or a tag
    // we skip inside tags because something like <img src="nicodemus.jpg" alt="John 3:16"> should not be messed with
	$anchor_regex = '<a\s+href.*?<\/a>';
	$pre_regex = '<pre>.*<\/pre>';
	$code_regex = '<code>.*<\/code>';
	$other_plugin_regex= '\[bible\].*\[\/bible\]'; // for the ESV Wordpress plugin (out of courtesy)
	$other_plugin_block_regex='\[bibleblock\].*\[\/bibleblock\]'; // ditto
	$tag_regex = '<(?:[^<>\s]*)(?:\s[^<>]*){0,1}>'; // $tag_regex='<[^>]+>';
	$split_regex = "/((?:$anchor_regex)|(?:$pre_regex)|(?:$code_regex)|(?:$other_plugin_regex)|(?:$other_plugin_block_regex)|(?:$tag_regex))/i";
	$parsed_text = preg_split($split_regex,$text,-1,PREG_SPLIT_DELIM_CAPTURE);
	$linked_text = '';

  while (list($key,$value) = each($parsed_text)) {
      if (preg_match($split_regex,$value)) {
         $linked_text .= $value; // if it is an HTML element or within a link, just leave it as is
      } else {
        $linked_text .= verseAddLinks($value,$bible); // if it's text, parse it for Bible references
      }
  }

  return $linked_text;
}


function verseAddLinks($text = '',$bible = NULL) {
global $bibleverse_translations;

	if (!isset($bible)) {
		$bible=get_option('bibleverse_default_translation');
	}

    $volume_regex = '1|2|3|I|II|III|1st|2nd|3rd|First|Second|Third';

    $book_regex  = 'Genesis|Exodus|Leviticus|Numbers|Deuteronomy|Joshua|Judges|Ruth|Samuel|Kings|Chronicles|Ezra|Nehemiah|Esther';
    $book_regex .= '|Job|Psalms?|Proverbs?|Ecclesiastes|Songs? of Solomon|Song of Songs|Isaiah|Jeremiah|Lamentations|Ezekiel|Daniel|Hosea|Joel|Amos|Obadiah|Jonah|Micah|Nahum|Habakkuk|Zephaniah|Haggai|Zechariah|Malachi';
    $book_regex .= '|Mat+hew|Mark|Luke|John|Acts?|Acts of the Apostles|Romans|Corinthians|Galatians|Ephesians|Phil+ippians|Colossians|Thessalonians|Timothy|Titus|Philemon|Hebrews|James|Peter|Jude|Revelations?';

	// I split these into two different variables from Dean's original Perl code because I want to be able to have an optional period at the end of just the abbreviations

    $abbrev_regex  = 'Gen|Ex|Exo|Lev|Num|Nmb|Deut?|Josh?|Judg?|Jdg|Rut|Sam|Ki?n|Chr(?:on?)?|Ezr|Neh|Est';
    $abbrev_regex .= '|Jb|Psa?|Pr(?:ov?)?|Eccl?|Song?|Isa|Jer|Lam|Eze|Dan|Hos|Joe|Amo|Oba|Jon|Mic|Nah|Hab|Zeph?|Hag|Zech?|Mal';
    $abbrev_regex .= '|Mat+|Mr?k|Lu?k|Jh?n|Jo|Act|Rom|Cor|Gal|Eph|Col|Phil?|The?|Thess?|Tim|Tit|Phile|Heb|Ja?m|Pe?t|Ju?d|Rev';

    $book_regex='(?:'.$book_regex.')|(?:'.$abbrev_regex.')\.?';

    $verse_regex="\d{1,3}(?::\d{1,3})?(?:\s?(?:[-&,]\s?\d+))*";

	// non Bible Gateway translations are all together at the end to make it easier to maintain the list
	$translation_regex = implode('|',array_keys($bibleverse_translations)); // makes it look like 'NIV|KJV|ESV' etc

	// note that this will be executed as PHP code after substitution thanks to the /e at the end!
    $passage_regex = '/(?:('.$volume_regex.')\s)?('.$book_regex.')\s('.$verse_regex.')(?:\s?[,-]?\s?((?:'.$translation_regex.')|\s?\((?:'.$translation_regex.')\)))?/e';

    $replacement_regex = "verseLinkReference('\\0','\\1','\\2','\\3','\\4','$bible')";

    $text=preg_replace($passage_regex,$replacement_regex,$text);

    return $text;
}

function mychurch_book($rawbook) {
	// ultimately I need to restore all abbreviations to the full book.
	// perhaps take the first three letters and expand?
	$book = strtolower(trim($rawbook));
    $book = preg_replace('/\s+/', '', $book); //strip whitespace
	$book= substr($book,0,3);
	switch ($book) {
		case 'gen': $book='genesis'; break;
		case 'exo': case 'ex': $book='exodus'; break;
		case 'lev': case 'lv': $book='leviticus'; break;
		case 'num': $book='numbers'; break;
		case 'deu': case 'dt': $book='deuteronomy'; break;
		case 'jos': $book='joshua'; break;
		case 'jud': case 'jd':
			// could be either Judges or Jude
			// abbreviations for Judges should always have a g in them
			$judges=strpos($rawbook,'g');
			if ($judges===FALSE) {
				$book='jude';
			} else {
				$book='judges';
			}
			break;
		case 'rut': case 'rth': $book='ruth'; break;
		case '1sa': $book='1samuel'; break;
		case '2sa': $book='2samuel'; break;
		case '1ki': $book='1kings'; break;
		case '2ki': $book='2kings'; break;
		case '1ch': $book='1chronicles'; break;
		case '2ch': $book='2chronicles'; break;
		case 'ezr': case 'ez': $book='ezra'; break;
		case 'neh': case 'nh': $book='nehemiah'; break;
		case 'est': $book='esther'; break;
		case 'job': case 'jb': $book='job'; break;
		case 'psa': case 'ps': $book='psalms'; break;
		case 'pro': case 'pr': $book='proverbs'; break;
		case 'ecc': $book='ecclesiastes'; break;
		case 'son': case 'sos': $book='songofsongs'; break;
		case 'isa': case 'is': $book='isaiah'; break;
		case 'jer': $book='jeremiah'; break;
		case 'lam': $book='lamentations'; break;
		case 'eze': case 'ez': $book='ezekiel'; break;
		case 'dan': case 'dn': $book='daniel'; break;
		case 'hos': $book='hosea'; break;
		case 'joe': $book='joel'; break;
		case 'amo': case 'am': $book='amos'; break;
		case 'oba': case 'ob': $book='obadiah'; break;
		case 'jon': $book='jonah'; break;
		case 'mic': $book='micah'; break;
		case 'nah': $book='nahum'; break;
		case 'hab': $book='habakkuk'; break;
		case 'zep': $book='zephaniah'; break;
		case 'hag': $book='haggai'; break;
		case 'zec': $book='zechariah'; break;
		case 'mal': $book='malachi'; break;
		case 'mat': case 'mt': $book='matthew'; break;
		case 'mar': case 'mk': $book='mark'; break;
		case 'luk': case 'lk': $book='luke'; break;
		case 'joh': case 'jn': $book='john'; break;
		case 'act': $book='acts'; break;
		case 'rom': case 'rm': $book='romans'; break;
		case '1co': $book='1corinthians'; break;
		case '2co': $book='2corinthians'; break;
		case 'gal': $book='galatians'; break;
		case 'eph': $book='ephesians'; break;
		case 'phi': $book='philippians'; break;
		case 'col': $book='colossians'; break;
		case '1th': $book='1rhessalonians'; break;
		case '2th': $book='2thessalonians'; break;
		case '1ti': $book='1timothy'; break;
		case '2ti': $book='2timothy'; break;
		case 'tit': case 'ti': $book='titus'; break;
		case 'phi': $book='philemon'; break;
		case 'heb': $book='hebrews'; break;
		case 'jam': $book='james'; break;
		case '1pe': $book='1peter'; break;
		case '2pe': $book='2peter'; break;
		case '1jo': $book='1john'; break;
		case '2jo': $book='2john'; break;
		case '3jo': $book='3john'; break;
		// jude is handled up by judges
		case 'rev': $book='revelation'; break;
		default:
			$book=$rawbook;
	}
	return $book;
}


function verseLinkReference($reference='',$volume='',$book='',$verse='',$translation='',$user_translation='') {
    if ($volume) {
       $volume = str_replace('III','3',$volume);
	   $volume = str_replace('Third','3',$volume);
       $volume = str_replace('II','2',$volume);
	   $volume = str_replace('Second','2',$volume);
       $volume = str_replace('I','1',$volume);
	   $volume = str_replace('First','1',$volume);
       $volume = $volume{0}; // will remove st,nd,and rd (presupposes regex is correct)
    }

	//catch an obscure bug where a sentence like "The 3 of us went downtown" triggers a link to 1 Thess 3
	if (!strcmp(strtolower($book),"the") && $volume=='' ) {
		return $reference;
	}

   if(!$translation) {
         if (get_option('bibleverse_default_translation')) {
             $translation = get_option('bibleverse_default_translation');
         } else {
             $translation = 'RSV';
         }
   } else {
       $translation = trim($translation,' ()'); // strip out any parentheses that might have made it this far
   }

   // if necessary, just choose part of the verse reference to pass to the web interfaces
   // they wouldn't know what to do with John 5:1-2, 5, 10-13 so I just give them John 5:1-2
   // this doesn't work quite right with something like 1:5,6 - it gets chopped to 1:5 instead of converted to 1:5-6
   if ($verse) {
       $verse = strtok($verse,',& ');
   }

   $book = mychurch_book($volume.$book);
   $pieces = split('[:-]', $verse, 3);
   $chapter = $pieces[0];
   $num = $pieces[1];

   $link = "http://www.mychurch.org/bible/";
   $link = sprintf('<a href="%s%s">%s</a>',$link,htmlentities(trim("$translation/$book/$chapter/#$num")),trim($reference));

   return $link;
}

// Add buttons in WordPress v2.1+, thanks to An-archos
function mce_plugins($plugins) {
	array_push($plugins, '-bible');
	return $plugins;
}

function mce_buttons($buttons) {
	array_push($buttons, 'separator');
	array_push($buttons, 'verse');
	return $buttons;
}

function tinymce_before_init() {
	echo "tinyMCE.loadPlugin('bible', '" . get_bloginfo('wpurl') . "/wp-content/plugins/bibleverse/resources/tinymce/');\n";
}

function addbiblebutton() {
	global $wp_version;
	// Don't bother doing this stuff if the current user lacks permissions as they'll never see the pages
	if ( !current_user_can('edit_posts') && !current_user_can('edit_pages') ) return;

	// If WordPress 2.1+ and using TinyMCE, we need to insert the buttons differently
	if ( TRUE == version_compare($wp_version, '2.1.0', '>=') && 'true' == get_user_option('rich_editing') ) {
		// Load and append our TinyMCE external plugin
		add_filter('mce_plugins', 'mce_plugins');
		add_filter('mce_buttons', 'mce_buttons');
		add_action('tinymce_before_init', 'tinymce_before_init');
		//add_action('admin_head', 'buttonhider');
	}
}

##### ADD ACTIONS AND FILTERS

	add_filter('the_content','verse');
	add_filter('comment_text','verse');
	add_action('init', 'addbiblebutton');
	add_action('admin_menu', 'bibleverse_add_options');
?>
