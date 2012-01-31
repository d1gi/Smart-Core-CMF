<?php 
/*
<meta name="author" content="digi" /> 

<!--[if lt IE 8]><link rel="stylesheet" href="/themes/styles/screen-fix-ie.css" type="text/css" /><![endif]-->

<!--	
	<script src="/SmartCore/scripts/jquery/1.4.2/jquery-1.4.2.min.js"></script>
	<script>
		$(function(){report()});
		function report() {
			$('#test').html(
				$('#content').width() + 'x' + $('#content').height()
			);
			$('#content').prepend('<b> TEXT </b>');
		}
	</script>
-->

<!--<body onresize="report();">-->
*/
?>
<!-- Layout start (старт макета) -->
<div id="nofooter">
	<div class="vhcenter1"><div class="vhcenter2"><div class="vhcenter3">
	<div class="main-frame">
		
<div id="auth-light">
<?php $this->block('auth-light')?>
</div>

<div id="logo"><h1>Smart Core CMF</h1>платформа управления сайтами</div><!-- <div id="lang-switch">Рус | Eng</div> -->
		
<div id="v-menu">
<?php //$this->block('v_menu')?>
<?php //echo $this->View->Block->v_menu?>
<?php $this->Blocks->v_menu->render()?>
</div>

<div id="breadcrumbs">
<?php $this->block('breadcrumbs')?>
</div>

<div id="content">
<?php
	$this->view('content');
	/*
	if ($this->view('content') === false) {
		$this->block('content');
	};
	*/
?>		
</div>

	</div> <!--// main-frame-->
	</div></div></div>
	<div id="footer-pusher"></div>
</div>


<div id="footer">
<!-- контент футера -->
<?php $this->block('footer')?>
</div>