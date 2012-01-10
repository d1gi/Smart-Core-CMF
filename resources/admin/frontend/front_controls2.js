/**
 * @todo сделать автоматическое применение разных лайтбоксов.
 */

/**
 * highslide
 */
/*
function addFrontControl(id, items) {
	items.reverse();
	$j(id).addClass('cmf-frontadmin-node').prepend('<br />');
	for (var i = 0; i < items.length; i++) {
		var href = '<a href="' + items[i].link + 
			'" onclick="return hs.htmlExpand(this, { objectType: \'iframe\', width: 900, height: 640, headingText: \'' 
			+ items[i].frame_title + '\' } )" title="' + items[i].frame_title + '">' + items[i].title + '</a>';
		
		$j(id).addClass('cmf-frontadmin-node').prepend('<div class="cmf-frontadmin-node-a">' + href + '</div>');
	}
}
*/

function addFrontControl2(id, key) {
	$j(id).addClass('cmf-frontadmin-node').prepend('<div id="cmf-draggable_node' + key + '" class="cmf-draggable-panel"><div class="cmf-draggable-split-menu" id="cmf-draggable-menu_node' + key + '"></div></div>');
}
 
 
function addFrontControl_test(id, key, items) {
	items.reverse();
	$j(id).addClass('cmf-frontadmin-node').prepend('<div id="cmf-draggable_node' + key + '" class="cmf-draggable-panel"><div class="cmf-draggable-split-menu" id="cmf-draggable-menu_node' + key + '"></div></div>');
//	$j(id).addClass('cmf-frontadmin-node').prepend('<div id="cmf-draggable_node' + key + '" class="cmf-draggable-panel"><div class="cmf-draggable-split-menu" id="cmf-draggable-menu_node' + key + '">');
//	$j(id).addClass('cmf-frontadmin-node').prepend('</div></div>');
	for (var i = 0; i < items.length; i++) {
		var href = '<a href="' + items[i].link + 
			'" class="lightview" rel="iframe" title="' + items[i].frame_title + 
			' :: ' +
			' :: width: 940, height: 600, keyboard: false, overlayClose: false" >'
			+ items[i].title + '</a>';
		
//		$j(id).addClass('cmf-frontadmin-node').prepend('<div class="cmf-frontadmin-node-a">' + href + '</div>');
		$j('cmf-draggable-menu_node' + key).addClass('cmf-draggable-split-menu').prepend('<div class="cmf-frontadmin-node-a">' + href + '</div>');
	}
//	$j(id).addClass('cmf-frontadmin-node').prepend('<br />123');
}


/**
 * lightview
 */
function addFrontControl(id, key, items) {
	var cnt = items.length;
	items.reverse();
	if(-[1,]){
		// Normal Brwser
		var start = 0;
	} else {
		// Fuckin' IE :(
		var start = 1;
	}

//	$j(id).addClass('cmf-frontadmin-node').prepend('<div class="cmf-frontend-controls-panel"></div>');
	
//	$j(id).addClass('cmf-frontend-controls-panel').prepend('<br />');
	$j(id).addClass('cmf-frontadmin-node').prepend('<br />');
	
	for (var i = start; i < cnt; i++) {
		if (items[i].node_action_mode + '' == 'built-in') {
			var href = '<a href="' + items[i].link + '" title="' 
				+ items[i].frame_title +  '" >'
				+ items[i].title + '</a>';
		} else {
			var href = '<a href="' + items[i].link + '" class="lightview" rel="iframe" title="' 
				+ items[i].frame_title + ' :: ' + ' :: width: 940, height: 720, keyboard: false, overlayClose: false" >'
				+ items[i].title + '</a>';
		}
		
//		$j(id).addClass('cmf-frontend-controls-panel').prepend('<div class="cmf-frontadmin-node-a">' + href + '</div>');
		$j(id).addClass('cmf-frontadmin-node').prepend('<div class="cmf-frontadmin-node-a">' + href + '</div>');
	}
	
}

/*
function addFrontControlExtjs(id, items) {
	items.reverse();
	$j(id).addClass('cmf-frontadmin-node').prepend('<br />');
	for (var i = 0; i < items.length; i++) {
		var href = '<a href="' + items[i].link + 
			'" class="lightview" rel="iframe" title="' + items[i].frame_title + 
			' :: ' +
			' :: width: 900, height: 600, keyboard: false, overlayClose: false" >'
			+ items[i].title + '</a>';
		
		$j(id).addClass('cmf-frontadmin-node').prepend('<div class="cmf-frontadmin-node-a">' + href + '</div>');
	}
	
	// Helper class for organizing the buttons
	ButtonPanel = Ext.extend(Ext.Panel, {
		layout:'table',
		defaultType: 'button',
		baseCls: 'x-plain',
		cls: 'btn-panel',
		renderTo : 'cmf-draggable-menu',
		menu: undefined,
		split: false,

		layoutConfig: {
			columns:3
		},

		constructor: function(desc, buttons){
			// apply test configs
			for(var i = 0, b; b = buttons[i]; i++){
				b.menu = this.menu;
				b.enableToggle = this.enableToggle;
				b.split = this.split;
				b.arrowAlign = this.arrowAlign;
			}
			var items = [{
				xtype: 'box',
				//autoEl: {tag: 'h3', html: desc, style:"padding:15px 0 3px;"},
				colspan: 3
			}].concat(buttons);

			ButtonPanel.superclass.constructor.call(this, {
				items: items
			});
		}
	});
}

*/