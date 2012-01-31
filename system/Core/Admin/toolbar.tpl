<div id="cmf-toolbar">

<div class="cmf-toolbar-user">
	<!--<a href="#"><img src="/SmartCore/user_files/images/q3_avatar.jpg" width="50" height="50" title="Изменить профиль пользователя" id="cmf-toolbar-user-image"/></a>-->
	Добро пожаловать,<br /><b><?php echo $user_data['nickname']?></b>
	<br /><a href="<?php echo $logout_link?>">Выход</a>
</div>

<div id="cmf-toolbar-start">
<ul class="cmf-toolbar-menu">
	<li><a>Меню</a>
	<ul>
		<li><a>Структура</a>
		<ul>
			<li><a href="<?php echo $new_folder['link']?>" class="lightview" rel="iframe" title="<?php echo $new_folder['title']?> ::  :: width: 900, height: 680, keyboard: false, overlayClose: false" ><?php echo $new_folder['title']?></a></li>
			<li><a href="<?php echo $edit_folder['link']?>" class="lightview" rel="iframe" title="<?php echo $edit_folder['title']?> ::  :: width: 900, height: 680, keyboard: false, overlayClose: false" ><?php echo $edit_folder['title']?></a></li>
			<li><a href="<?php echo $all_folders['link']?>" class="lightview" rel="iframe" title="<?php echo $all_folders['title']?> ::  :: width: 900, height: 680, keyboard: false, overlayClose: false" ><?php echo $all_folders['title']?></a></li>
			<li><a href="<?php echo $new_module['link']?>" class="lightview" rel="iframe" title="<?php echo $new_module['title']?> ::  :: width: 900, height: 680, keyboard: false, overlayClose: false" ><?php echo $new_module['title']?></a></li>
			<li><a href="<?php echo $all_nodes['link']?>" class="lightview" rel="iframe" title="<?php echo $all_nodes['title']?> ::  :: width: 900, height: 680, keyboard: false, overlayClose: false" ><?php echo $all_nodes['title']?></a></li>
		</ul>
		</li>
		<li><a>Настройки</a>
			<ul>
				<li><a href="<?php echo $users['link']?>" class="lightview" rel="iframe" title="<?php echo $users['title']?> ::  :: width: 900, height: 680, keyboard: false, overlayClose: false" ><?php echo $users['title']?></a></li>
				<li><a href="<?php echo $settings['link']?>" class="lightview" rel="iframe" title="<?php echo $settings['title']?> ::  :: width: 900, height: 680, keyboard: false, overlayClose: false" ><?php echo $settings['title']?></a></li>
				<li><a href="<?php echo $sysinfo['link']?>" class="lightview" rel="iframe" title="<?php echo $sysinfo['title']?> ::  :: width: 900, height: 680, keyboard: false, overlayClose: false" ><?php echo $sysinfo['title']?></a></li>
				<li><a href="<?php echo $blocks['link']?>" class="lightview" rel="iframe" title="<?php echo $blocks['title']?> ::  :: width: 900, height: 680, keyboard: false, overlayClose: false" ><?php echo $blocks['title']?></a></li>
				<!--<li><a href="#aba"><i>Модули</i></a></li>-->
				<li><a href="<?php echo HTTP_ROOT?>admin/module/" class="lightview" rel="iframe" title="Модули ::  :: width: 900, height: 680, keyboard: false, overlayClose: false" >Модули</a></li>
				<!--<li><a href="#<?php echo HTTP_ROOT?>admin/components/" class="lightview" rel="iframe" title="Компоненты ::  :: width: 900, height: 680, keyboard: false, overlayClose: false" >Компоненты</a></li>-->
				<li><a href="<?php echo $site['link']?>" class="lightview" rel="iframe" title="<?php echo $site['title']?> ::  :: width: 900, height: 680, keyboard: false, overlayClose: false" ><?php echo $site['title']?></a></li>
			</ul>
		</li>
	</ul>
	</li>
</ul>
</div>

<div id="cmf-toolbar-mode">
	
<?php
	if ($this->Cookie->cmf_frontend_mode === 'edit') {
?>
		<form action="" method="post">
		<div><input type="hidden" name="frontend_mode" value="view" /></div>
		<div><input type="image" src="<?php echo HTTP_SYS_RESOURCES?>admin/toolbar/images/view.gif" title="Режим просмотра" /></div>
		</form>
		<img src="<?php echo HTTP_SYS_RESOURCES?>admin/toolbar/images/edit.gif" title="Режим редактирования" alt="" />
<?php
	} else {
?>
		<img src="<?php echo HTTP_SYS_RESOURCES?>admin/toolbar/images/view.gif" title="Режим просмотра" alt="" />
		<form action="" method="post">
		<div><input type="hidden" name="frontend_mode" value="edit" /></div>
		<div><input type="image" src="<?php echo HTTP_SYS_RESOURCES?>admin/toolbar/images/edit.gif" title="Режим редактирования" /></div>
		</form>
<?php
	}
?>
	
	<!--<a href="#"><img src="<?php echo HTTP_SYS_RESOURCES?>admin/toolbar/images/settings.gif" title="Режим разработки"/></a>-->
</div>


</div>
<?php
/*


<div class="cmf-toolbar-buttons">
<a href="#">Добавить новость</a>
<a href="#">Типичное действие 1</a>
<a href="#">Типичное действие 2</a>
<!--<a href="#">SEO</a>-->
</div>


<!--<div class="pusher"></div> -->
*/
?>