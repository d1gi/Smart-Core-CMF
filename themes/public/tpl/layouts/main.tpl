
<div id="wrapper">
	<div id="masthead">
		<div class="content">
			<div id="sc-lang"><a href="#">ru</a> | <a href="#">en</a></div>
			
			<!--<a id="sc-logo" href="#">Smart Core CMF</a>-->
			<?php $this->block('primary-menu')?>
			
			<ul id="nav-primary">
				<li><a href="#">О системе</a></li>
				<!--<li class="current"><span>Скачать</span></li>-->
				<li><a href="#">Скачать</a></li>
				<li><a href="#">Дополнения</a></li>
				<li><a href="#">Документация</a></li>
				<li><a href="#">Сообщество</a></li>
			</ul>
						
		</div> <!-- .content -->
	</div><!-- #masthead -->

	
	<div id="banner">
		<div class="content">
			<h1>Smart Core CMF</h1>
			<div id="dl-top">
				<a href="http://sourceforge.net/projects/smart-core-cmf/files/SmartCore-2011-01-15-alpha.zip/download" target="_blank">Скачать<br /></a>
				<span>v.2011-01-15-alpha</span>
			</div>
			<!--<h1>Скачать</h1>-->
		</div><!-- .content -->
	</div><!-- #banner -->

	<div class="content subpage">
		<h2>Content Managment Framework/System</h2>
		<p>Современная система для создания и управления интернет проектами с открытым исходным кодом, работающая под управлением PHP 5.2+ и MySQL 5.0+.</p>
		
		<hr />
		<h3>Получить</h3>
		<p>Файлы для скачивания доступны по адресу <a href="http://sourceforge.net/projects/smart-core-cmf/" target="_blank">http://sourceforge.net/projects/smart-core-cmf/</a></p>
		<p>Система активно развивается и для ознакамления с исходным кодом, настоятельно рекомендуется сделать clone с репозитория git:</p>
		<code>
		git clone git://smart-core-cmf.git.sourceforge.net/gitroot/smart-core-cmf/smart-core-cmf 
		</code>
		<br /><br /><br />
		<p>Основная разработка ведется в ветке develop.</p>
		
		<hr />
		<h3>Документация</h3>
		<p>Wiki - <a href="/wiki/">http://smart-core.org/wiki/</a></p>
		
		<hr />
		<h3>Сообщество</h3>
		<p>Приглашаются все желающие принять участие в развитии этого молодого и перспективного проекта ;)</p>
		<p>Все вопросы, можно задать на форуме - <a href="/forum/">http://smart-core.org/forum/</a></p>
		
		<br /><br />
	</div>
	
<div id="bottom-signup">
	<div class="content">
	
	</div>
</div>	
	
<div id="push"></div>
</div> <!-- wrapper -->

<div id="footer">
	<div id="footer-wrapper">

	<div class="content">

			<div class="col-3">
				<h3>О системе</h3>
			<ul>
				<li><a href="#">Особенности</a></li>
				<li><a href="#">Системные требования</a></li>
				<li><a href="#">Решения</a></li>
				<li><a href="#">Разработчики</a></li>
				<li><a href="#">Поддержка проекта</a></li>
				<li><a href="#">Лицензия</a></li>
			</ul>
			</div>
			<div class="col-3">
				<h3>Сообщество</h3>
			<ul>
				<li><a href="#">Блог</a></li>
				<li><a href="#">Форум</a></li>
				<li><a href="#">Трекер</a></li>
				<li><a href="#">Сайты работающие на<br />Smart Core CMF</a></li>
				<li><a href="#">Часто задаваемые вопросы</a></li>
				<li><a href="#">Логотип</a></li>
			</ul>
			</div>
			<div class="col-3">
				<h3>Документация</h3>
				<ul>
				<li><a href="#">Концепция и Архитектура</a></li>
				<li><a href="#">Инсталляция</a></li>
				<li><a href="#">Быстрый старт</a></li>
				<li><a href="#">Термины и Определения</a></li>
				<li><a href="#">Права доступа</a></li>
				<li><a href="#">Руководство пользователя</a></li>
				<li><a href="#">Руководство разработчика</a></li>
				</ul>
			</div>
			<div class="col-3 contact-us last ">
				<h3>Контакты</h3>
				<ul>
				<li>Email</li>
				</ul>
				<!--
				<form>
					<label for="name">Имя</label>
					<input name="name" type="text">
					<label for="message">Сообщение</label>
					<textarea name="message" cols="" rows=""></textarea>
					<input type="button" value="Написать нам!"> 
				</form>
				-->
			</div>
			
		</div>
			
	  <div id="copyright" class="center clear">
		<p>&copy; 2009-2011 Smart Core</p>
	  </div>

		</div>

</div><!-- #footer -->


<script type="text/javascript">

  var _gaq = _gaq || [];
  _gaq.push(['_setAccount', 'UA-20750359-1']);
  _gaq.push(['_setDomainName', '.smart-core.org']);
  _gaq.push(['_trackPageview']);

  (function() {
	var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
	ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
	var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
  })();

</script>

<!--	
	
	<div id="head2">
	<?php $this->block('head')?>
	</div>
	 
	<div id="menu">
	<?php $this->block('vertical-menu')?>
	</div>
	 
	<div id="content">
	<?php $this->block('content')?>
	</div>
	 
	<div id="footer">
	<?php $this->block('footer')?>
	</div>

</div> 
-->
