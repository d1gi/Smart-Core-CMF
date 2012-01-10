
<script type="text/javascript" src="/scripts/nivo-slider/2.6/jquery.nivo.slider.pack.js"></script>
<style type="text/css"> @import "/scripts/nivo-slider/2.6/nivo-slider.css"; </style>
<style type="text/css"> 
#slider {
	position:relative;
	width:800px; /* Change this to your images width */
	height:300px; /* Change this to your images height */
	background:url(images/nivo/loading.gif) no-repeat 50% 50%;
	border-radius:6px;
	-moz-border-radius:6px;
	-webkit-border-radius:6px;
}
#slider img {
	position:absolute;
	top:0px;
	left:0px;
	display:none;
}
#slider a {
	border:0;
	display:block;
}	
	
.nivo-controlNav {
	position:absolute;
	left:720px;
	bottom:10px;
}
.nivo-controlNav a {
	display:block;
	width:22px;
	height:22px;
	background:url(images/nivo/bullets.png) no-repeat;
	text-indent:-9999px;
	border:0;
	margin-right:3px;
	float:left;
}
.nivo-controlNav a.active {
	background-position:0 -22px;
}

.nivo-directionNav a {
	display:block;
	width:30px;
	height:30px;
	background:url(images/nivo/arrows.png) no-repeat;
	text-indent:-9999px;
	border:0;
}
a.nivo-nextNav {
	background-position:-30px 0;
	right:15px;
}
a.nivo-prevNav {
	left:15px;
}

.nivo-caption {
	text-shadow:none;
	font-family: Helvetica, Arial, sans-serif;
}
.nivo-caption a { 
	color:#efe9d1;
	text-decoration:underline;
}
</style>

<script type="text/javascript">
jQuery(document).ready(function(){
	
	/* jQuery('#slider').nivoSlider(); */
	
	jQuery('#slider').nivoSlider({
		effect:'fade', // Specify sets like: 'fold,fade,sliceDown'
		slices:15, // For slice animations
		boxCols: 8, // For box animations
		boxRows: 4, // For box animations
		animSpeed:500, // Slide transition speed
		pauseTime:<?php echo $data['time_interval'];?>, // How long each slide will show
		startSlide:0, // Set starting Slide (0 index)
		directionNav:true, // Next & Prev navigation
		directionNavHide:true, // Only show on hover
		controlNav:true, // 1,2,3... navigation
		controlNavThumbs:false, // Use thumbnails for Control Nav
		controlNavThumbsFromRel:false, // Use image rel for thumbs
		controlNavThumbsSearch: '.jpg', // Replace this with...
		controlNavThumbsReplace: '_thumb.jpg', // ...this in thumb Image src
		keyboardNav:true, // Use left & right arrows
		pauseOnHover:false, // Stop animation while hovering
		manualAdvance:false, // Force manual transitions
		captionOpacity:0, // Universal caption opacity
		prevText: 'Предыдущий слайд', // Prev directionNav text
		nextText: 'Следующий слайд', // Next directionNav text
		beforeChange: function(){}, // Triggers before a slide transition
		afterChange: function(){}, // Triggers after a slide transition
		slideshowEnd: function(){}, // Triggers after all slides have been shown
		lastSlide: function(){}, // Triggers when last slide is shown
		afterLoad: function(){} // Triggers when slider has loaded
	});

	jQuery(".jbgallery").jbgallery({
		style     : "zoom",
		menu 	: "numbers"
   });
   
});
</script>

	
<div id="slider">
<?php
	foreach ($data['slides'] as $key => $value) {
		$tmp = $key + 1;
		echo "<img src=\"$value[img]\" alt=\"\" title=\"#slidercaption" . $tmp . "\" />\n";
	}
?>
</div>
