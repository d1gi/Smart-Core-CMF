<?php
/**
 *  
 * @todo пока компонент неработает! надо избавить от Helper_Head
 */
class Component_Popup_Highslide
{
	/**
	 * Получение ссылки для открытия в окошке. 
	 */
	public function getLink($href, $title, $width, $height)
	{
		return "<a href=\"$href\" onclick=\"return hs.htmlExpand(this, { objectType: 'iframe', width: $width, height: $height, headingText: '$title' } )\" title=\"$title\" ";
	}

	
	/**
	 * Устанавливаем данные для html head
	 * 
	 */
	public function setHtmlHead()
	{
		$Head = Helper_Head::getInstance();
		$Head->useScript('highslide');
		$Head->setData('highslide-extra', "	<script type=\"text/javascript\">
	hs.graphicsDir = '{$Head->getScriptPath('highslide', 'graphics_dir')}';
	hs.showCredits = false;
	hs.dimmingOpacity = 0.75;
	hs.outlineType = 'rounded-white';
	hs.align = 'center';
	hs.dragByHeading = false;

	hs.transitions = ['expand', 'crossfade'];
	hs.wrapperClassName = 'draggable-header';
	//hs.fadeInOut = true;
	//hs.numberPosition = 'caption';
	hs.lang = {
		loadingText :     'Загружается...',
		loadingTitle :    'Нажмите для отмены',
		focusTitle :      'Нажмите чтобы поместить на передний план',
		fullExpandTitle : 'Развернуть до оригинального размера',
		fullExpandText :  'Оригинальный размер',
		creditsText :     'Использует <i>Highslide JS</i>',
		creditsTitle :    'Перейти на домашнюю страницу Highslide JS',
		previousText :    'Предыдущее',
		previousTitle :   'Предыдущее (стрелка влево)',
		nextText :        'Следующее',
		nextTitle :       'Следующее (стрелка вправо)',
		moveTitle :       'Переместить',
		moveText :        'Переместить',
		closeText :       'Закрыть',
		closeTitle :      'Закрыть (esc)',
		resizeTitle :     'Изменить размер',
		playText :        'Слайдшоу',
		playTitle :       'Начать слайдшоу (пробел)',
		pauseText :       'Пауза',
		pauseTitle :      'Приостановить слайдшоу (пробел)',   
		number :          'Изображение %1 из %2',
		restoreTitle :    'Нажмите чтобы закрыть изображение, нажмите и перетащите для изменения местоположения. Для просмотра изображений используйте стрелки.'
	};
	</script>");
	}
	
}