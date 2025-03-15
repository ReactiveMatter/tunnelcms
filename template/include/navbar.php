<nav class="navbar">
	<div class="container">		
	    <a class="muted-link" href="<?=$site['base']?>/"/>Tunnel CMS</a>	
		<button class="navbar-toggler collapsed ml-auto" type="button" data-toggle="collapse" data-target="#navbarResponsive" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
		<img class="menu-toggle" src="<?=$site['base']?>/assets/menu.svg">
		</button>
		<div class="collapse navbar-collapse" id="navbarResponsive">
		<ul class="navbar-nav">
		<li>
		<a class="muted-link" href="<?=$site['base']?>/blog/">Blog</a>	
		<li>
		<li>
		<a class="muted-link" href="<?=$site['base']?>/about/">About</a>
		</li>
		<li>
		<a class="muted-link theme-toggle" onclick="toggleTheme()"><span class="target-theme" style="text-transform:capitalize">dark</span> theme</a>
		</li>
		</ul>
		</div>

	</div>
</nav>
