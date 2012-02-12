<!DOCTYPE html>
<html manifest="cache.manifest">
<head>
<title>Vägkorset</title> 
<meta charset="utf-8">
<link href='http://fonts.googleapis.com/css?family=Redressed' rel='stylesheet' type='text/css'>
<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.7.1/jquery.min.js"></script>
<script type="text/javascript">
$(document).ready(function(){
	$('#index-bg-door').click(function(){
		window.location="main.php";
	});
	$('#index-bg-door').bind('mouseenter',function(){
		$('#index-bg-door').addClass('index-bg-open');
	}).bind('mouseleave',function(){
		$('#index-bg-door').removeClass('index-bg-open');
	});
	if (navigator.userAgent.toLowerCase().indexOf('iphone')!=-1 || navigator.userAgent.toLowerCase().indexOf('ipad')!=-1){
		$('#index-bg-door').addClass('index-bg-open');
	}
});
</script>
<style type="text/css">
#index-bg {
	margin: 0 auto;
	height: 885px;
	width: 1280px;
	background: url(img/door.png) center no-repeat;
	background-position: 0 0;
	position: relative;
}
.index-bg-open {
	background: url(img/door.png) center no-repeat;
	background-position: 175px -885px;
}
#index-bg-door {
	position: absolute;
	margin-top:10px;
	left: 466px;
	width: 507px;
	height: 667px;
	cursor: pointer;
}
.door-title {
	color: #444;
	top: 5px;
	overflow: visible;
	margin-left: -110px;
	width: 112%;
	text-align: center;
	position: absolute;
	font-size: 17px;
	font-family: Redressed, cursive;
}
body {
	overflow: hidden;
	background: url(img/bg_florals.jpg) #000; /* Åkersten pillat */
	padding:0;
	margin:0;
}
</style>
<script type="text/javascript">

  var _gaq = _gaq || [];
  _gaq.push(['_setAccount', 'UA-26991280-1']);
  _gaq.push(['_trackPageview']);

  (function() {
    var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
    ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
    var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
  })();

</script>
</head>
<body>
	<div id="index-bg"><div id="index-bg-door"><div class="door-title">Vandrarhemmet för den som är trött och sliten längs färden</div></div></div>
</body>
</html>
