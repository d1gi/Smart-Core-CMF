<?php 
/* vim: set noexpandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * Класс для вывода данных в HTTP поток.
 * 
 * @author		Artem Ryzhkov
 * @package		Kernel
 * @copyright	Copyright &copy; 2010-2012 Smart Core CMF 
 * @link		http://smart-core.org/
 * @license		http://opensource.org/licenses/gpl-2.0
 * 
 * @uses		Cache
 * @uses		Settings
 * @uses		Template
 * 
 * @version 	2012-01-05.0
 */
class Response extends SingletonBase
{
	/**
	 * Массив с HTTP заголовками.
	 * 
	 * @access private
	 * @var array
	 * 
	 * @todo подумать над механизмом нескольких заголовков одного названия.
	 */
	private $headers;
	
	/**
	 * Ссылка для редиректа.
	 * @var string
	 */
	private $redirect;
	
	/**
	 * MIME-тип для формирования заголовка Content-Type.
	 * 
	 * @access private
	 * @var string
	 */
	private $mime_type;
	
	/**
	 * Charset.
	 * @var string
	 */
	private $charset;
	
	/**
	 * Данные для прямого вывода.
	 * @var mixed
	 */
	private $direct_data;
	
	/**
	 * Данные для вывода через шаблонизатор.
	 * @var array
	 */
	private $data;
	
	/**
	 * Компрессия. 
	 * @var int - Значение от 0 до 9.
	 */
	private $compress;
	
	/**
	 * Метод сжатия. (gzip или deflate)
	 * @var string
	 */
	private $compress_method;
	
	/**
	 * @var  array  An array of status codes and messages
	 */
	protected $status_texts = array(
		100 => 'Continue',
		101 => 'Switching Protocols',
		200 => 'OK',
		201 => 'Created',
		202 => 'Accepted',
		203 => 'Non-Authoritative Information',
		204 => 'No Content',
		205 => 'Reset Content',
		206 => 'Partial Content',
		207 => 'Multi-Status',
		300 => 'Multiple Choices',
		301 => 'Moved Permanently',
		302 => 'Found',
		303 => 'See Other',
		304 => 'Not Modified',
		305 => 'Use Proxy',
		307 => 'Temporary Redirect',
		400 => 'Bad Request',
		401 => 'Unauthorized',
		402 => 'Payment Required',
		403 => 'Forbidden',
		404 => 'Not Found',
		405 => 'Method Not Allowed',
		406 => 'Not Acceptable',
		407 => 'Proxy Authentication Required',
		408 => 'Request Timeout',
		409 => 'Conflict',
		410 => 'Gone',
		411 => 'Length Required',
		412 => 'Precondition Failed',
		413 => 'Request Entity Too Large',
		414 => 'Request-URI Too Long',
		415 => 'Unsupported Media Type',
		416 => 'Requested Range Not Satisfiable',
		417 => 'Expectation Failed',
		422 => 'Unprocessable Entity',
		423 => 'Locked',
		424 => 'Failed Dependency',
		500 => 'Internal Server Error',
		501 => 'Not Implemented',
		502 => 'Bad Gateway',
		503 => 'Service Unavailable',
		504 => 'Gateway Timeout',
		505 => 'HTTP Version Not Supported',
		507 => 'Insufficient Storage',
		509 => 'Bandwidth Limit Exceeded'
	);

	/**
	 * Constructor. Singleton pattern.
	 *
	 * @return void
	 */
	protected function __construct()
	{
		parent::__construct();
		$this->compress		= $this->Settings->getParam('http_compression_level');
		$this->compress_method = 'gzip';
		
		$this->headers		= array();
		$this->redirect		= false;
		
		$this->direct_data	= false;
		$this->data			= false;
		
		$this->mime_type	= 'text/html';
		$this->charset		= 'utf-8';
	}
		
	/**
	 * ОТправка ответа клиенту.
	 *
	 * @param void
	 * @return void
	 */
	public function send()
	{
		// Если запрошен редирект, то он и выполняется :)
		if ($this->redirect !== false) {
			header('Location: ' . strlen($this->redirect) == 0 ? $_SERVER['REQUEST_URI'] : $this->redirect);
			exit;
		}
		
		$charset = ($this->charset === false or strpos($this->mime_type, 'image/') !== false) ? '' : '; charset=' . $this->charset;
		
		$this->headerStatus(200);
		header('Content-type: ' . $this->mime_type . $charset);
		
		if ($this->direct_data !== false) {
			echo $this->direct_data;
			return;
		}
		
		// @todo включать компрессию только для текстовых mime типов.
		$is_need_cache_page	= (_IS_CACHE_PAGES and $this->Env->cache_enable and $this->Env->user_id == 0) ? true : false;
		$is_need_compress	= ($this->compress and $this->isSupportCompress() and $this->Cookie->cmf_frontend_mode !== 'edit') ? true : false;
		
		if ($is_need_cache_page or $is_need_compress) {
			ob_start();
			ob_implicit_flush(false);
			
			$Template = new Template();
			$Template->render();
			
			$compress_level 	 = $this->compress == 0 ? 9 : $this->compress;
			$content			 = ob_get_clean();
			$compressed_content	 = gzencode($content, $compress_level);
			$compressed_length	 = strlen($compressed_content);
			$uncompressed_length = strlen($content);
			
			if ($is_need_compress) {
				header('Content-Encoding: gzip');
				header('X-Uncompressed-Length: '. $uncompressed_length);
				header('Content-Length: ' 		. $compressed_length);
				echo $compressed_content;
			} else {
				echo $content;
			}
			
			if ($is_need_cache_page) {
				// @todo запаковать ключ parser_node_id внутрь последней ноды. задача #46.
				$folders = Kernel::getParserData();
				unset($folders['parser_node_id']);
				$this->Cache_Page->save(
					array(
						'id' => $_SERVER['REQUEST_URI'],
						'lifetime' => 600, // @todo настройку времени кеширования страниц.
						'folders' => $folders,
						'nodes' => Kernel::getNodesList(),
					),
					array(
						'content' => $compressed_content,
						'headers' => array(
							$this->getHeaderStatus(200),
							'Content-Encoding: gzip',
							'Content-type: ' . $this->mime_type . $charset,
							'Content-Length: ' . $compressed_length,
							'X-Uncompressed-Length: '. $uncompressed_length,
							),
					));
			}
			
		} else {
			$Template = new Template();
			$Template->render();
		}
	}
	
	/**
	 * Проверка на поддержку сжатия контента.
	 *
	 * @return bool
	 */
	public function isSupportCompress()
	{
		return (isset($_SERVER['HTTP_ACCEPT_ENCODING']) 
			and strstr($_SERVER['HTTP_ACCEPT_ENCODING'], 'gzip') !== false 
			and strstr($_SERVER['HTTP_ACCEPT_ENCODING'], 'gzip;q=0') === false 
			and extension_loaded('zlib')
		) ? true : false;
	}
	
	/**
	 * Отправка HTTP заголовка с кодом статуса.
	 *
	 * @param int $code
	 */
	public function headerStatus($code)
	{
		header($this->getHeaderStatus($code));
	}
	
	/**
	 * Получить строку HTTP заголовка с кодом статуса.
	 *
	 * @param int $code
	 */
	public function getHeaderStatus($code)
	{
		return $_SERVER['SERVER_PROTOCOL'] . ' ' . $code . ' ' . $this->status_texts[$code];
	}
	
	/**
	 * Установить данные для прямого вывода.
	 *
	 * @param mixed $direct_data - любые данные в любом формате.
	 */
	public function setDirectData($direct_data)
	{
		$this->direct_data = $direct_data;
	}
	
	/**
	 * Установить тип выходных данных.
	 *
	 * @param string $mime_type
	 */
	public function setMimeType($mime_type)
	{
		$this->mime_type = $mime_type;
	}
	
	/**
	 * Устновить кодировку.
	 *
	 * @param string $charset
	 */
	public function setCharset($charset)
	{
		$this->charset = $charset;
	}
	
	/**
	 * Установить уровень сжатия.
	 *
	 * @param int $compress_level
	 */
	public function setCompressLevel($compress_level)
	{
		$this->compress = $compress_level;
	}
	
	/**
	 * Проверка является ли вывод "прямым".
	 *
	 * @param void
	 * @return bool
	 */
	public function isDirect()
	{
		return $this->direct_data === false ? false : true;
	}
	
	/**
	 * Установить путь для редиректа.
	 *
	 * @param string|true $uri
	 * @return void
	 */
	public function setRedirect($uri)
	{
		$this->redirect = $uri;
	}
}