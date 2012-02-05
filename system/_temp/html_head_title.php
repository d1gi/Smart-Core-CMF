<?php 

// Сборка строки <title> из "хлебных крошек".
$title = '';
$bc = Registry::get('Breadcrumbs')->get();
krsort($bc);
foreach ($bc as $key => $value) {
	if ($key == 0) {
		break;
	}
	$title .= $value['title'] . ' / '; // @todo сделать настройку разделителя.
}

// Если "хлебных крошек" нет, то отображаем полное имя сайта, иначе сокращенное.
$site_props = Registry::get('Site')->getProperties();

if (count($bc) > 1) {
	$title .= $site_props['short_name'];
} else {
	$title .= $site_props['full_name'];
}

$head .= "\t<title>$title</title>\n";
