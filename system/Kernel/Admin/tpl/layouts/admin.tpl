<!--<h1>Панель управления</h1>-->

<?php
if (!cf_is_get('popup')) {
	echo '<div id="breadcrumbs">';
	$this->container('breadcrumbs');
	echo '</div>';
}
?>

<?php $this->container('content')?>
