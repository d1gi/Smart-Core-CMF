<?php 
/**
 * Интерфейс для всех модулей.
 * 
 */
interface Admin_ModuleInterface
{
	/**
	 * Получить элементы управления нодой.
	 * 
	 * Не являются обязательными.
	 * 
	 * @return array
	 */
//	public function getFrontControls();
//	public function getFrontControlsDefaultAction();
//	public function getFrontControlsInnerDefaultAction();
	
	/**
	 * Обработка дейсвий над нодой.
	 * 
	 * Не являются обязательными.
	 * 
	 * @return void
	 */
//	public function nodeAction($params);
	
	/**
	 * Получить параметры подключения модуля.
	 * 
	 * @return array
	 */
	public function getParams();

	/**
	 * Вызывается при создании ноды.
	 * 
	 * @return array params
	 * 
	 * @todo мультиязычность.
	 */
	public function createNode();
}