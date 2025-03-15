<?php
	include("include/head.php");
	include("include/navbar.php");
	
	$pages = get_all_pages();
?>
<div class="container main page-list">
<h1 class="title"><?=ucfirst($page['title'])?></h1>

<table class="table page-results">
	<thead>
		<tr>
			<th class="border-bottom-0 border-top-0 head-title" scope="col">Title</th>
			<th class="border-bottom-0 border-top-0 head-category" scope="col">Category</th>
			<th class="border-bottom-0 border-top-0 head-date text-right" scope="col">Date</th>
		</tr>
	</thead>
	<tbody>
<?php 	$count = 0; ?>
<?php foreach ($pages as $page): ?>
<?php 
if (str_starts_with($page['slug'], 'blog/')): ?>
		<?php
		if(!isset($page['category']))
		{
			$page['category'] = 'general';
		}  	
		if($page['slug']=="blog/") { continue;}
		$count++; ?>
		<tr>
		<td class="td-title">
		<a class="title" href="<?php echo rtrim($site['base'], '/')."/".ltrim($page['slug'], '/'); ?>">
		<?php echo ucfirst($page['title']); ?>
		</a>
		</td>
	<!-- Category -->
		<td class="td-category">
		<a class="category" href="<?=$site['base']."/category/".strtolower($page['category'])?>"><?=ucfirst($page['category'])?></a>
		</td>

	<!-- Creation date -->
		<td class="text-right td-date">
		<?php 
			if(isset($page['date']))
			{ echo date($site['date_format'], $page['date']);
			}
		 ?>
		</td>
	</tr>
<?php endif ?>
<?php endforeach ?>
<tr><td colspan="3" class="text-muted">Total posts: <?=$count?></td></tr>
</tbody>
</table>

</div>
<?php
	include("include/footer.php");
	include("include/end.php");