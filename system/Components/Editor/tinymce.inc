<?php 
/* <script type="text/javascript" src="<?php echo HTTP_SCRIPTS?>tinymce/tiny_mce.js"></script>	*/

/*
<script type="text/javascript" src="<?php echo HTTP_SCRIPTS?>tinymce/tiny_mce_gzip.js"></script>

<script type="text/javascript">

tinyMCE_GZ.init({
	plugins : "safari,pagebreak,style,table,save,advhr,advimage,advlink,emotions,iespell,inlinepopups,insertdatetime,preview,media,searchreplace,print,contextmenu,paste,directionality,fullscreen,noneditable",
	themes : 'simple,advanced',
	languages : 'en,ru',
	disk_cache : true,
	debug : false
});

function fileBrowserCallBack(field_name, url, type, win)
{
	var connector = "<?php echo HTTP_ROOT?>scripts/filemanager2/browser/default/browser.html?Connector=<?php echo HTTP_ROOT?>scripts/filemanager2/connectors/php/connector.php";
	var enableAutoTypeSelection = true;
	
	var cType;
	tinyfck_field = field_name;
	tinyfck = win;
	
	switch (type) {
		case "image":
			cType = "Image";
			break;
		case "flash":
			cType = "Flash";
			break;
		case "file":
			cType = "File";
			break;
		case "media":
			cType = "Media";
			break;
	}
	
	if (enableAutoTypeSelection && cType)
	{
		connector += "&Type=" + cType;
	}
	
	window.open(connector, "tinyfck", "modal,width=600,height=400");
}



	
tinyMCE.init({
	// General options 
	mode : "textareas", 
	theme : "advanced", 
	plugins : "safari,spellchecker,pagebreak,style,layer,table,save,advhr,advimage,advlink,emotions,iespell,inlinepopups,insertdatetime,preview,media,searchreplace,print,contextmenu,paste,directionality,fullscreen,noneditable,visualchars,nonbreaking,xhtmlxtras,template,imagemanager,filemanager", 

	language : "ru",
	force_br_newlines : false,
	forced_root_block : '',
	convert_urls : false,
	file_browser_callback : "fileBrowserCallBack",		
			 
	// Theme options 
	theme_advanced_buttons1 : "save,newdocument,|,bold,italic,underline,strikethrough,|,justifyleft,justifycenter,justifyright,justifyfull,|,styleselect,formatselect,fontselect,fontsizeselect", 
	theme_advanced_buttons2 : "cut,copy,paste,pastetext,pasteword,|,search,replace,|,bullist,numlist,|,outdent,indent,blockquote,|,undo,redo,|,link,unlink,anchor,image,cleanup,help,code,|,insertdate,inserttime,preview,|,forecolor,backcolor", 
	theme_advanced_buttons3 : "tablecontrols,|,hr,removeformat,visualaid,|,sub,sup,|,charmap,emotions,iespell,media,advhr,|,print,|,ltr,rtl,|,fullscreen", 
//	theme_advanced_buttons4 : "insertlayer,moveforward,movebackward,absolute,|,styleprops,spellchecker,|,cite,abbr,acronym,del,ins,attribs,|,visualchars,nonbreaking,template,blockquote,pagebreak,|,insertfile,insertimage", 
	theme_advanced_buttons4 : "",
	theme_advanced_toolbar_location : "top",
	theme_advanced_toolbar_align : "left",
	theme_advanced_statusbar_location : "bottom",
	theme_advanced_resizing : true,
	theme_advanced_resize_horizontal : false,
	theme_advanced_resizing_use_cookie : true, 
	theme_advanced_styles : "Только при свете=light_on, Только в темноте=light_off", // iglow fix
	 
	relative_urls : false,
	width : "<?php echo $this->width?>",
	height : <?php echo $this->height?>,
	 
	content_css : "<?php echo HTTP_STYLES?>tinymce.css",
	 
	// Drop lists for link/image/media/template dialogs 
	template_external_list_url : "js/template_list.js", 
	external_link_list_url : "js/link_list.js", 
	external_image_list_url : "js/image_list.js", 
	media_external_list_url : "js/media_list.js", 
	 
	// Replace values for the template plugin 
	template_replace_values : { 
		username : "Some User", 
		staffid : "991234" 
	} 
}); 		

</script>

var connector = "<?php echo HTTP_SCRIPTS?>filemanager2/browser/default/browser.html?Connector=<?php echo HTTP_SCRIPTS?>filemanager2/connectors/php/connector.php";
var connector = "<?php echo HTTP_ROOT?>filemanager/";
 
*/
?>
	<script type="text/javascript"> 
	function toggletinyMCE(id) {
		if (!tinyMCE.getInstanceById(id))
			tinyMCE.execCommand('mceAddControl', false, id);
		else
			tinyMCE.execCommand('mceRemoveControl', false, id);
	}
	
	function fileBrowserCallBack(field_name, url, type, win) {
		var connector = "<?php echo $options['filemanager']?>";
		var enableAutoTypeSelection = false;
		var cType;
		tinyfck_field = field_name;
		tinyfck = win;
		
		switch (type) {
			case "image":
				cType = "Image";
				break;
			case "flash":
				cType = "Flash";
				break;
			case "file":
				cType = "File";
				break;
			case "media":
				cType = "Media";
				break;
		}
		if (enableAutoTypeSelection && cType) {
			connector += "&Type=" + cType;
		}
		
		window.open(connector, "tinyfck", "modal,width=600,height=400");
	}
	
	tinyMCE.init({
		// General options 
		mode : "textareas", 
		theme : "advanced", 
		plugins : "safari,pagebreak,style,layer,table,save,advhr,advimage,advlink,emotions,inlinepopups,insertdatetime,preview,media,searchreplace,print,contextmenu,paste,directionality,fullscreen,noneditable,visualchars,nonbreaking,xhtmlxtras,template,spellchecker",

		spellchecker_languages : "+English=en,Russian=ru",
		extended_valid_elements : "iframe[src|width|height|name|align|frameborder|id|scrolling]",
		// spellchecker,imagemanager,filemanager,iespell,
		language : "ru",
		force_br_newlines : false,
		forced_root_block : "",
		convert_urls : false,
		file_browser_callback : "fileBrowserCallBack",		
		// Theme options 
		theme_advanced_buttons1 : "save,newdocument,|,bold,italic,underline,strikethrough,|,justifyleft,justifycenter,justifyright,justifyfull,|,styleselect,formatselect,fontselect,fontsizeselect", 
		theme_advanced_buttons2 : "cut,copy,paste,pastetext,pasteword,|,search,replace,|,bullist,numlist,|,outdent,indent,blockquote,|,undo,redo,|,link,unlink,anchor,image,cleanup,help,code,|,insertdate,inserttime,preview,|,forecolor,backcolor", 
		theme_advanced_buttons3 : "tablecontrols,|,hr,removeformat,visualaid,|,sub,sup,|,charmap,emotions,iespell,media,advhr,|,print,|,ltr,rtl,|,spellchecker,fullscreen", 
		//theme_advanced_buttons4 : "insertlayer,moveforward,movebackward,absolute,|,styleprops,spellchecker,|,cite,abbr,acronym,del,ins,attribs,|,visualchars,nonbreaking,template,blockquote,pagebreak,|,insertfile,insertimage",
		theme_advanced_buttons4 : "",
		theme_advanced_toolbar_location : "top",
		theme_advanced_toolbar_align : "left",
		theme_advanced_statusbar_location : "bottom",
		theme_advanced_resizing : true,
		theme_advanced_resize_horizontal : false,
		theme_advanced_resizing_use_cookie : true, 
		// theme_advanced_styles : "Только при свете=light_on, Только в темноте=light_off", // @todo убрать
		theme_advanced_styles : false,
		relative_urls : false,
		width : "<?php echo $this->width?>",
		height : "<?php echo $this->height?>",
		content_css : "<?php echo $this->editor_css?>",
		// Drop lists for link/image/media/template dialogs 
		template_external_list_url : "js/template_list.js",
		external_link_list_url : "js/link_list.js",
		external_image_list_url : "js/image_list.js",
		media_external_list_url : "js/media_list.js"
	}); 		
	</script>	