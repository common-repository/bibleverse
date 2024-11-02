/**
 * $RCSfile: editor_plugin_src.js,v $
 * $Revision: 1.5 $
 * $Date: 4/3/06 $
 *
 * @author Cary
 * @copyright Copyright 2007 Cary, All rights reserved.
 */

var d;
var m=0;

var count1=0;
var count2=0;

var TinyMCE_BiblePlugin = {
    getInfo : function() {
        return {
            longname : 'Bible Verse',
            author : 'Cary',
            authorurl : 'http://www.carystanley.com/',
            infourl : 'http://www.carystanley.com',
            version : tinyMCE.majorVersion + "." + tinyMCE.minorVersion
        };
    },
    
	initInstance : function(inst) {

	},

     // Returns the HTML contents of the verse control.
    getControlHTML : function(cn) {
        switch (cn) {
        
            case "verse":
                return tinyMCE.getButtonHTML(cn, '', '{$pluginurl}/images/verse.gif', 'mceVerse');
        }

        return "";
    },

     // Executes the verse command.

    execCommand : function(editor_id, element, command, user_interface, value)
   {
        // Handle commands
        switch (command)
        {
		case 'mceVerse':
			var template = new Array();
			var inst = tinyMCE.getInstanceById(editor_id);

			template['file']   = this.baseURL+'/verse.htm';
			template['width']  = 430;
			template['height'] = 115;

			template['width'] += tinyMCE.getLang('lang_flash_delta_width', 0);
			template['height'] += tinyMCE.getLang('lang_flash_delta_height', 0);

			tinyMCE.openWindow(template, {editor_id : editor_id, inline : "yes"});
			return true;
        }

        // Pass to next handler in chain
        return false;
    }
};

tinyMCE.addPlugin("bible", TinyMCE_BiblePlugin);
