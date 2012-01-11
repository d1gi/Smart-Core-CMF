
function fieldsetsToTabs($) {
	//	Добавление табов
	$('fieldset').wrapAll('<div class="section" />');
	$('div.section').prepend($(document.createElement('ul')).addClass('tabs'));
	$('legend').each(function(){
		$('ul.tabs').append($(document.createElement('li')).text($(this).text()));
	});
	$('ul.tabs li:first').addClass('current');
	// Удаляем легенд
	$('fieldset legend').remove();
	// Назначие филдсетам видимость
	$('fieldset').addClass('box');
	$('fieldset:first').addClass('visible');
	
	$('ul.tabs').delegate('li:not(.current)', 'click', function() {
		$(this).addClass('current').siblings().removeClass('current').parents('div.section').find('fieldset.box').hide().eq($(this).index()).fadeIn(0);
	})
}
