<?php
/* vim: set noexpandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * Хелпер по работе с изображениями.
 * 
 * Изменение размеров, преобразования в разные форматы, обрезание.
 * 
 * @uses http://wideimage.sourceforge.net/
 * 
 * @version 2011-12-03.1
 */
class Helper_Image
{
	/**
	 * Источник.
	 * Может быть как файл на диске, так и ссылка на удалённый ресурс.
	 * @var string
	 */
	protected $input_file;
	
	/**
	 * Файл в который будет помещен результат обработки.
	 * @var string
	 */
	protected $output_file;
	
	/**
	 * Выходной формат при ресайзе.
	 * 
	 * - bmp
	 * - gif
	 * - jpg|jpeg
	 * - png
	 * 
	 * @var string|false
	 */	
	protected $resized_output_format;
	
	/**
	 * Качество выходного JPEG.
	 * @var int
	 */
	protected $jpeg_quality;
	
	/**
	 * Сжатие PNG по умолчанию (от 0 до 9)
	 * @var int
	 */
	protected $png_compression;
	protected $png_filters;
	
	/**
	 * Constructor.
	 *
	 * @param string $input_file
	 * @param string $output_file
	 * @return
	 */
	public function __construct($input_file = false, $output_file = false)
	{
		require_once DIR_LIB . 'WideImage/lib/WideImage.php';
		
		$this->jpeg_quality			 = 82;
		$this->png_compression		 = 8;
		$this->input_file			 = $input_file;
		$this->output_file			 = $output_file;
		// Использовать тотже выходной формат при ресайзе, что и входной.
		$this->resized_output_format = false;
	}
	
	/**
	 * Проверка предварительных данных.
	 * 
	 * Проверяется установлены ли значения $this->input_file и $this->output_file
	 *
	 * @return bool
	 */
	protected function _check()
	{
		if ($this->input_file === false or !file_exists($this->input_file)) {
			return false;
		} else if ($this->output_file === false) {
			return false;
		}
		return true;
	}
	
	/**
	 * Конвертация в другой формат.
	 *
	 * - bmp
	 * - gif
	 * - jpg|jpeg
	 * - png
	 * 
	 * @param string $to_format
	 * @return
	 */
	public function convert($to_format)
	{
		if ($this->_check() === false) {
			return false;
		}
		
		return true;
	}
	
	/**
	 * Обрезка.
	 *
	 * @param mixed $left   - отступ слева от верхнего лёвого угла.
	 * @param mixed $top    - отступ сверху от верхнего лёвого угла.
	 * @param mixed $width  - высота новой кртинки.
	 * @param mixed $height - ширина новой кртинки.
	 */
	public function crop($left = 0, $top = 0, $width = '100%', $height = '100%')
	{
		$image = WideImage::load($this->input_file);
		$cropped = $image->crop($left, $top, $width, $height);
		
		switch ($ext) {
			case 'jpg':
			case 'jpeg':
				$cropped->saveToFile($this->output_file, $this->jpeg_quality);
				break;
			case 'png':
				$cropped->saveToFile($this->output_file, $this->png_compression);
				break;
			default;
				$cropped->saveToFile($this->output_file);
		}
	}
	
	/**
	 * Изменение размеров.
	 * 
	 * $width and $height are both smart coordinates. This means that you can pass any of these values in:
	 * - positive or negative integer (100, -20, ...)
	 * - positive or negative percent string (30%, -15%, ...)
	 * - complex coordinate (50% - 20, 15 + 30%, ...)
	 * 
	 * @param mixed $width
	 * @param mixed $height
	 * @param string $fit - подгонка:
	 *  - inside  по минимальной строне с сохранением пропорций.
	 *  - outside по максимальной стороне с сохранением пропорций.
	 *  - fill    заполнить исходной картинкой новую без сохранения пропорций.
	 * @param string $scale - мастабировать:
	 *  - 'down'  только уменьшение
	 *  - 'up'    только увеличение
	 *  - 'any'   в любом случае
	 */
	public function resize($width = null, $height = null, $fit = 'inside', $scale = 'down')
	{
		if ($this->_check() === false) {
			return false;
		}

		$image = WideImage::load($this->input_file);
		$resized = $image->resize($width, $height, $fit, $scale);

		// В зависимости от расширения файла, применяются разные методы сохранения.
		switch (strtolower(substr($this->output_file, strrpos($this->output_file, '.') + 1))) {
			case 'jpg':
			case 'jpeg':
				$resized->saveToFile($this->output_file, $this->jpeg_quality);
				break;
			case 'png':
				$resized->saveToFile($this->output_file, $this->png_compression);
				break;
			default;
				$resized->saveToFile($this->output_file);
		}
	}
	
	/**
	 * Поворот.
	 *
	 * @param
	 * @return
	 */
	public function rotate()
	{
	
		return true;
	}
	
	/**
	 * Наложение. Применяется например для нанесения "водяного знака".
	 *
	 * @param
	 * @return
	 */
	public function merge($watermark_file, $left = 0, $top = 0, $pct = 100)
	{
		$image = WideImage::load($this->input_file);
		$watermark = WideImage::load($watermark_file);
		$new = $img->merge($watermark, $left, $top, $pct);
		$new->saveToFile($this->output_file, $this->jpeg_quality);
	}
	
	/**
	 * Устновить качество JPEG.
	 *
	 * @param int $val
	 */
	public function setJpegQuality($val)
	{
		$this->jpeg_quality = $val;
	}
	
	/**
	 * Устновить сжатие PNG.
	 *
	 * @param int $val
	 */
	public function setPngCompression($val)
	{
		$this->png_compression = $val;
	}
	
	/**
	 * Установка входного файла.
	 *
	 * @param string $input_file
	 */
	public function setInputFile($input_file)
	{
		$this->input_file = $input_file;
	}
	
	/**
	 * Установка выходного файла.
	 *
	 * @param string $output_file
	 */
	public function setOutputFile($output_file)
	{
		$this->output_file = $output_file;
	}
}