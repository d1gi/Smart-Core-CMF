<div id="breadcrumbs">
	<a href="<?php echo $this->homepage['uri']?>" title="<?php echo $this->homepage['descr']?>"><?php echo $this->homepage['title']?></a>
</div>

<div class="login-message"><?php echo $this->welcome_message?></div>

<?php
$Form = new Helper_Form($this->login_form);
echo $Form;
