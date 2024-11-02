
var bible_books = new Array('Genesis', 'Exodus', 'Leviticus', 'Numbers', 'Deuteronomy', 'Joshua', 'Judges', 'Ruth', '1 Samuel', '2 Samuel', '1 Kings', '2 Kings', '1 Chronicles', '2 Chronicles', 'Ezra', 'Nehemiah', 'Esther', 'Job', 'Psalms', 'Proverbs', 'Ecclesiastes', 'Song of Songs', 'Isaiah', 'Jeremiah', 'Lamentations', 'Ezekiel', 'Daniel', 'Hosea', 'Joel', 'Amos', 'Obadiah', 'Jonah', 'Micah', 'Nahum', 'Habakkuk', 'Zephaniah', 'Haggai', 'Zechariah', 'Malachi', 'Matthew', 'Mark', 'Luke', 'John', 'Acts', 'Romans', '1 Corinthians', '2 Corinthians', 'Galatians', 'Ephesians', 'Philippians', 'Colossians', '1 Thessalonians', '2 Thessalonians', '1 Timothy', '2 Timothy', 'Titus', 'Philemon', 'Hebrews', 'James', '1 Peter', '2 Peter', '1 John', '2 John', '3 John', 'Jude', 'Revelation');
var translations = new Array('ASV', 'BBE', 'KJV', 'RSV');

function init() {
	tinyMCEPopup.resizeToInnerSize();

	document.getElementById("bookcontainer").innerHTML = getBibleBooksHTML();
	document.getElementById("transcontainer").innerHTML = getTranslationsHTML();
}

function getBibleBooksHTML() {
	if (typeof(bible_books) != "undefined" && bible_books.length > 0) {
		var html = "";

		html += '<select id="book" name="book" style="width: 120px" onfocus="tinyMCE.addSelectAccessibility(event, this, window);">';

		for (var i=0; i<bible_books.length; i++)
			html += '<option value="' + (i+1) + '">' + bible_books[i] + '</option>';

		html += '</select>';

		return html;
	}

	return "";
}

function getTranslationsHTML() {
	if (typeof(translations) != "undefined" && translations.length > 0) {
		var html = "";

		html += '<select id="translation" name="translation" style="width: 50px" onfocus="tinyMCE.addSelectAccessibility(event, this, window);">';

		for (var i=0; i<translations.length; i++)
			html += '<option value="' + translations[i] + '">' + translations[i] + '</option>';

		html += '</select>';

		return html;
	}

	return "";
}

function insertVerse() {
	var formObj = document.forms[0];
	var html = '';
	var book = formObj.book.value;
	var start_chapter = formObj.start_chapter.value;
	var start_num = formObj.start_num.value;
	var end_chapter = formObj.end_chapter.value;
	var end_num = formObj.end_num.value;
	var translation = formObj.translation.value;

	getVerse(book, start_chapter, start_num, end_chapter, end_num, translation);
}


function receivedText(verses) {
	tinyMCEPopup.execCommand("mceInsertContent", true, verses.text);
	tinyMCE.selectedInstance.repaint();
	tinyMCEPopup.close();
}

function getVerse(book, start_chapter, start_num, end_chapter, end_num, translation)
{
   var request_url = 'http://www.mychurch.org/xml/get_verse_json.php?b='+book+'&sc='+start_chapter+'&sn='+start_num+'&ec='+end_chapter+'&en='+end_num+'&t='+translation+'&callback=receivedText';
   json_obj = new JSONscriptRequest(request_url);
   json_obj.buildScriptTag();
   json_obj.addScriptTag();
} 


// Author: Jason Levitt
// Date: December 7th, 2005
// Constructor -- pass a REST request URL to the constructor
//
function JSONscriptRequest(fullUrl) {
    // REST request path
    this.fullUrl = fullUrl; 
    // Keep IE from caching requests
    this.noCacheIE = '&noCacheIE=' + (new Date()).getTime();
    // Get the DOM location to put the script tag
    this.headLoc = document.getElementsByTagName("head").item(0);
    // Generate a unique script tag id
    this.scriptId = 'YJscriptId' + JSONscriptRequest.scriptCounter++;
}

// Static script ID counter
JSONscriptRequest.scriptCounter = 1;

// buildScriptTag method
//
JSONscriptRequest.prototype.buildScriptTag = function () {

    // Create the script tag
    this.scriptObj = document.createElement("script");
    
    // Add script object attributes
    this.scriptObj.setAttribute("type", "text/javascript");
    this.scriptObj.setAttribute("src", this.fullUrl + this.noCacheIE);
    this.scriptObj.setAttribute("id", this.scriptId);
}
 
// removeScriptTag method
// 
JSONscriptRequest.prototype.removeScriptTag = function () {
    // Destroy the script tag
    this.headLoc.removeChild(this.scriptObj);  
}

// addScriptTag method
//
JSONscriptRequest.prototype.addScriptTag = function () {
    // Create the script tag
    this.headLoc.appendChild(this.scriptObj);
}
