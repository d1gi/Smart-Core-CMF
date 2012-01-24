<?php
/* vim: set noexpandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * Компонент, предоставляющий модулям интерфейс работы с визуальным редакторами текста.
 * 
 * @uses EE
 */
class Component_Editor extends Controller
{
	private $type;
	private $width;
	private $height;
	private $editor_css;

//	public function __construct($type = 'ckeditor')
	public function __construct($opt = array())
	{
		$options = array(
			'type'			=> 'tinymce',
			'filemanager'	=> HTTP_ROOT . 'filemanager/',
			'editor_css'	=> HTTP_SYS_RESOURCES . 'styles/editor.css',
			);
		foreach ($opt as $key => $value) {
			$options[$key] = $value;
		}
		
		$this->type			= $options['type'];
		$this->editor_css	= $options['editor_css'];
		$this->height		= 380;
		$this->width		= "100%";
		
		switch ($this->type) {
			case 'tinymce':
				$this->EE->useScriptLib('tinymce');
				ob_start();
				include DIR_COMPONENTS . 'Editor/' . $this->type . '.inc';
				$this->EE->addHeadData(ob_get_clean());
				break;
			case 'ckeditor':
				$this->EE->useScriptLib('jquery');
				$this->EE->useScriptLib('ckeditor');
				ob_start();
				include DIR_COMPONENTS . 'Editor/' . $this->type . '.inc';
				$this->EE->addHeadData(ob_get_clean());
				break;
			case 'fckeditor':
				include DIR_COMPONENTS . 'Editor/' . $this->type . '.inc';
				break;
		}
	}
	
	public function setHeight($value)
	{
		$this->height = $value;
	}
	
	public function setWidth($value)
	{
		$this->width = $value;
	}
	
	public function show($name, $text)
	{
		switch ($this->type) {
			case 'tinimce':
				echo "<textarea name=\"$name\" rows=\"10\">$text</textarea>\n";
				break;
			case 'ckeditor':
				break;
			case 'fckeditor':
				?>
				<script type="text/javascript">
				<!--
				var sBasePath = '<?php echo HTTP_SCRIPTS?>fckeditor/';

				var oFCKeditor = new FCKeditor( '<?php echo $name?>' ) ;
				oFCKeditor.BasePath	= sBasePath ;
				oFCKeditor.Height	= "<?php echo $this->height?>";
				oFCKeditor.Width	= "<?php echo $this->width?>";

				oFCKeditor.Config['EnterMode'] = 'br';
				oFCKeditor.Config['ShiftEnterMode']	= 'p';
				oFCKeditor.Config['ToolbarStartExpanded'] = false;
				oFCKeditor.Config['resize_dir'] = 'vertical';

				oFCKeditor.Value = '<?php echo str_replace("\r\n", '', $text)?>' ;
				oFCKeditor.Create() ;
				//-->
				</script>
				<?php 
				break;
			case 'html':
				if ($this->width != "100%") {
					$this->width .= $this->width . "px";
				}
				echo "<textarea name=\"$name\" style=\"width: {$this->width}; height: 150px;\">$text</textarea>";
				break;
			default:
		}
	}
	
}