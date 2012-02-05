<?php
/**
 * Всплывающие календарики
 * 
 * @todo сделать возможность использования разных библиотек, например xin_calendar_2, jQuery DatePicher и т.д.
 */
class Component_DatePicker extends Container
{
	public function __construct()
	{
		$this->Html->addHeadData('<style type="text/css"> @import "' . HTTP_SCRIPTS . 'xin_calendar_2/css/xc2_default.css"; </style>
		<script type="text/javascript" src="' . HTTP_SCRIPTS . 'xin_calendar_2/config/xc2_default.js"></script>
		<script type="text/javascript" src="' . HTTP_SCRIPTS . 'xin_calendar_2/script/xc2_inpage.js"></script>	
		<script type="text/javascript" src="' . HTTP_SCRIPTS . 'xin_calendar_2/script/mod_time.js"></script>
		<script type="text/javascript" src="' . HTTP_SCRIPTS . 'xin_calendar_2/script/mod_tiles.js"></script>
		<style type="text/css">
		.panel { border-width:0px; padding:16px; position:relative; z-index:2; background-color:transparent; }
		.row_head { border-width:0px; background-color:transparent; }
		.head { color:#ffffff; background-color:transparent; }
		.row_week { border-color:#ffff99 #999933 #999933 #ffff99; }
		.weekday { color:#ffffff; background-color:#cccc66; border:1px solid #cccc66;}
		.row_day { border-width:1px; border-style:solid; border-color:#ffffff #c0c0c0 #c0c0c0 #ffffff; padding:0px; }
		.tileTL { background:url("' . HTTP_SCRIPTS . 'xin_calendar_2/images/1.gif") no-repeat; }
		.tileTC { background:url("' . HTTP_SCRIPTS . 'xin_calendar_2/images/2.gif") repeat-x; }
		.tileTR { background:url("' . HTTP_SCRIPTS . 'xin_calendar_2/images/3.gif") no-repeat; }
		.tileCL { background:url("' . HTTP_SCRIPTS . 'xin_calendar_2/images/4.gif") repeat-y; }
		.tileCC { background:url("' . HTTP_SCRIPTS . 'xin_calendar_2/images/5.gif") repeat; }
		.tileCR { background:url("' . HTTP_SCRIPTS . 'xin_calendar_2/images/6.gif") repeat-y; }
		.tileBL { background:url("' . HTTP_SCRIPTS . 'xin_calendar_2/images/7.gif") no-repeat; }
		.tileBC { background:url("' . HTTP_SCRIPTS . 'xin_calendar_2/images/8.gif") repeat-x; }
		.tileBR { background:url("' . HTTP_SCRIPTS . 'xin_calendar_2/images/9.gif") no-repeat; }
		</style>
		<script type="text/javascript">
		xcDateFormat="yyyy-mm-dd hr:mi:ss";
		xcMods[7].order=1;
		xcMods[9].order=1;
		xcHours=["00", "01", "02", "03", "04", "05", "06", "07", "08", "09", "10", "11", "12", "13", "14", "15", "16", "17", "18", "19", "20", "21", "22", "23"];
		xcMinutes=["00", "01", "02", "03", "04", "05", "06", "07", "08", "09", "10", "11", "12", "13", "14", "15", "16", "17", "18", "19", "20", "21", "22", "23", "24", "25", "26", "27", "28", "29", "30", "31", "32", "33", "34", "35", "36", "37", "38", "39", "40", "41", "42", "43", "44", "45", "46", "47", "48", "49", "50", "51", "52", "53", "54", "55", "56", "57", "58", "59"];
		xcSeconds=["00", "01", "02", "03", "04", "05", "06", "07", "08", "09", "10", "11", "12", "13", "14", "15", "16", "17", "18", "19", "20", "21", "22", "23", "24", "25", "26", "27", "28", "29", "30", "31", "32", "33", "34", "35", "36", "37", "38", "39", "40", "41", "42", "43", "44", "45", "46", "47", "48", "49", "50", "51", "52", "53", "54", "55", "56", "57", "58", "59"];
		xcWeekStart=1;
		xcStickyMode=1;
		xcMonthNames=["Январь", "Февраль", "Март", "Апрель", "Май", "Июнь", "Июль", "Август", "Сентябрь", "Октябрь", "Ноябрь", "Декабрь"];
		xcMonthShortNames=["Янв", "Фев", "Мар", "Апр", "Май", "Июн", "Июл", "Авг", "Сен", "Окт", "Ноя", "Дек"];
		xcWeekdayDisplay=["Вс", "Пн", "Вт", "Ср", "Чт", "Пт", "Сб", "Вс"];
		xc_Close_the_calendar="Закрыть календарь";
		xc_Today="Сегодня";
		xcFootTags=["Сегодня", "Clear", "Back", "Закрыть", "Reset", "_Today_", "_Back_", "_Reset_"];
		xcFootTagSwitch=[0, 0, 0, 2, 0, 0, 0, 0];
		addTiles("conf",20,20,"tileTL","tileTC","tileTR","tileCL","tileCC","tileCR","tileBL","tileBC","tileBR");
		</script>');
	}
	
	/**
	 * 
	 */
	public function onfocus()
	{
		return "showCalendar('conf',this,this,'','holder',0,10,1)";
	}
	
}