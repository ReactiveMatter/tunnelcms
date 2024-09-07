<!DOCTYPE HTML>
<html>
<head>
	<meta http-equiv="Content-Type" content="text/html;charset=UTF-8">
	<meta name="viewport" content="width=device-width,initial-scale=1">
	<title>Tunnel CMS - The simplest markdown CMS</title>
	<link rel="stylesheet" type="text/css" href="<?=$site['base']?>/assets/style.css">
</head>
<body>
	<div class="container">
	<h1 class="title"><?=ucfirst($page['title'])?></h1>
	<?=$page['content']?>
	</div>
</body>
</html>