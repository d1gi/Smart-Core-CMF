<script src="/js/lightbox/jquery.lightbox.js" type="text/javascript"></script>
<link type="text/css" href="/js/lightbox/jquery.lightbox.css" rel="stylesheet" />

<script type="text/javascript">
jQuery(function(){
  jQuery.Lightbox.construct({
	show_linkback: false,
	show_helper_text: false,
	show_info: true,
	show_extended_info: true,
	download_link: false,
	keys: {
		close: 'z',
		prev: 'q',
		next: 'e'
	},
	opacity: 0.7,
	text: {
		image: 'Фото',
		of: 'из',
		close: 'Закрыть',
		download: 'Загрузить'
	}
  });
});
</script>

<?php

echo "<table width=\"100%\">
	<tr>
		<td><h2>" . $data['album']['title'] . "</h2></td>
		<td align=\"right\">&nbsp;<a href=\"?\">все альбомы</a></td>
	</tr>
</table>
";

echo "<p>"  . $data['album']['images_count'] . " фотографий</p>";

foreach ($data['images'] as $key => $value) {
	echo "
	<a class=\"lightview\" rel=\"gallery[mygallery]\" href=\"$value[original_link]\" name=\"image_$key\" title=\"$value[descr]\">
		<div class=\"gallery_image\" id=\"news_item_id_$key\">
		<img src=\"$value[thumbnail_link]\" alt=\"\" />
		</div>
	</a>\n";
}
