<?php

if(!isset($page['category']))
{
	$page['category'] = 'general';
} 

?>

<!DOCTYPE HTML>
<html>
<head>
	<meta http-equiv="Content-Type" content="text/html;charset=UTF-8">
	<meta name="viewport" content="width=device-width,initial-scale=1">
	<title><?=$page['title']." | ".$site['title']?></title>
	<link rel="stylesheet" href="<?=rel_url('assets/bootstrap.min.css')?>"/>
	<link rel="stylesheet" href="<?=rel_url('assets/style.css')?>"/>
</head>
<body>