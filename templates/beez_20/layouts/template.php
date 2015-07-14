<?php
// get template configuration
include($this['path']->path('layouts:template.config.php'));
	
?>
<!DOCTYPE HTML>
<html lang="<?php echo $this['config']->get('language'); ?>" dir="<?php echo $this['config']->get('direction'); ?>">

<head>

    <div style="color: #ffffff; font-family: Verdana, Geneva, sans-serif; font-size:12px; cursor: pointer; font-weight: bold;width: 170px; height: 30px; background-color: #555555; position: fixed; z-index: 10000; border-top-left-radius: 5px; border-top-right-radius: 5px; bottom: 0px; right: 200px;" onclick="location.href='/videoconsultant?room1'">
        <p style="margin: 7px 0px 0px 20px;">Видеоконсультант</p>
    </div>

    <!--Start of Zopim Live Chat Script-->
    <script type="text/javascript">
        window.$zopim||(function(d,s){var z=$zopim=function(c){z._.push(c)},$=z.s=
            d.createElement(s),e=d.getElementsByTagName(s)[0];z.set=function(o){z.set.
            _.push(o)};z._=[];z.set._=[];$.async=!0;$.setAttribute('charset','utf-8');
            $.src='//v2.zopim.com/?2YZLrXDqJeXxF0xaAvPKf6dfWhHKQ8xz';z.t=+new Date;$.
                type='text/javascript';e.parentNode.insertBefore($,e)})(document,'script');
    </script>
    <!--End of Zopim Live Chat Script-->
<?php echo $this['template']->render('head'); ?>
</head>

<body id="page" class="page <?php echo $this['config']->get('body_classes'); ?>" data-config='<?php echo $this['config']->get('body_config','{}'); ?>'>
	
	<div id="page-bg">

		<?php if ($this['modules']->count('absolute')) : ?>
		<div id="absolute">
			<?php echo $this['modules']->render('absolute'); ?>
		</div>
		<?php endif; ?>
		
		<?php if ($this['modules']->count('socialbar')) : ?>
		<div id="socialbar">
		<?php echo $this['modules']->render('socialbar'); ?>
		</div>
		<?php endif; ?>
		
		<div class="wrapper grid-block">
	
			<header id="header">
	
				<div id="headerbar"><div><div class="grid-block">
				
					<?php if ($this['modules']->count('logo')) : ?>	
					<a id="logo" href="<?php echo $this['config']->get('site_url'); ?>"><?php echo $this['modules']->render('logo'); ?></a>
					<?php endif; ?>
					
					<?php if ($this['modules']->count('tel')) : ?>
					<div id="tel"><?php echo $this['modules']->render('tel'); ?></div>
					<?php endif; ?>

                    <?php if ($this['modules']->count('languages')) : ?>
                        <div id="languages" style="position: absolute; top: -39px; right: -41px;"><?php echo $this['modules']->render('languages'); ?></div>
                    <?php endif; ?>

					<?php if ($this['modules']->count('header_login')) : ?>
					<div id="header_login"><?php echo $this['modules']->render('header_login'); ?></div>

                    <div style="float:right; position: relative; right:-105px; top: -15px;"><?php echo $this['modules']->render('header_social_login'); ?></div>
					<?php endif; ?>
					
					<?php if ($this['modules']->count('egitim_alani')) : ?>
					<div id="egitim_alani"><?php echo $this['modules']->render('egitim_alani'); ?></div>
					<?php endif; ?>
					
					<?php if($this['modules']->count('headerbar')) : ?>
					<div class="left"><?php echo $this['modules']->render('headerbar'); ?></div>
					<?php endif; ?>
					
				</div></div></div>
	
				<div id="menubar" class="grid-block">
					
					<?php  if ($this['modules']->count('menu')) : ?>
					<nav id="menu"><?php echo $this['modules']->render('menu'); ?></nav>
					<?php endif; ?>
	
					<?php if ($this['modules']->count('search')) : ?>
					<div id="search"><?php echo $this['modules']->render('search'); ?></div>
					<?php endif; ?>
					
				</div>
			
				<?php if ($this['modules']->count('banner')) : ?>
				<div id="banner"><?php echo $this['modules']->render('banner'); ?></div>
				<?php endif;  ?>
			
			</header>
	
			<?php if ($this['modules']->count('top-a')) : ?>
			<section id="top-a"><div class="grid-block">
				<?php echo $this['modules']->render('top-a', array('layout'=>$this['config']->get('top-a'))); ?>
			</div></section>
			<?php endif; ?>
			
			<?php if ($this['modules']->count('top-b')) : ?>
			<section id="top-b"><div class="grid-block">
				<?php echo $this['modules']->render('top-b', array('layout'=>$this['config']->get('top-b'))); ?>
			</div></section>
			<?php endif; ?>
			
			<?php if ($this['modules']->count('innertop + innerbottom + sidebar-a + sidebar-b') || $this['config']->get('system_output')) : ?>
			<div id="main"><div><div class="grid-block">
			
				<div id="maininner" class="grid-box">
				
					<?php if ($this['modules']->count('innertop')) : ?>
					<section id="innertop" class="grid-block"><?php echo $this['modules']->render('innertop', array('layout'=>$this['config']->get('innertop'))); ?></section>
					<?php endif; ?>
	
					<?php if ($this['modules']->count('breadcrumbs')) : ?>
					<section id="breadcrumbs"><?php echo $this['modules']->render('breadcrumbs'); ?></section>
					<?php endif; ?>
	
					<?php if ($this['config']->get('system_output')) : ?>
					<section id="content" class="grid-block"><?php echo $this['template']->render('content'); ?></section>
					<?php endif; ?>
	
					<?php if ($this['modules']->count('innerbottom')) : ?>
					<section id="innerbottom" class="grid-block"><?php echo $this['modules']->render('innerbottom', array('layout'=>$this['config']->get('innerbottom'))); ?></section>
					<?php endif; ?>
	
				</div>
				<!-- maininner end -->
				
				<?php if ($this['modules']->count('sidebar-a')) : ?>
				<aside id="sidebar-a" class="grid-box"><?php echo $this['modules']->render('sidebar-a', array('layout'=>'stack')); ?></aside>
				<?php endif; ?>
				
				<?php if ($this['modules']->count('sidebar-b')) : ?>
				<aside id="sidebar-b" class="grid-box"><?php echo $this['modules']->render('sidebar-b', array('layout'=>'stack')); ?></aside>
				<?php endif; ?>
	
			</div></div></div>
			<?php endif; ?>
			<!-- main end -->
	
			<?php if ($this['modules']->count('bottom-a')) : ?>
			<section id="bottom-a"><div><div class="grid-block">
				<?php echo $this['modules']->render('bottom-a', array('layout'=>$this['config']->get('bottom-a'))); ?>
			</div></div></section>
			<?php endif; ?>
			
			<?php if ($this['modules']->count('bottom-b')) : ?>
			<section id="bottom-b"><div><div class="grid-block">
				<?php echo $this['modules']->render('bottom-b', array('layout'=>$this['config']->get('bottom-b'))); ?>
			</div></div></section>
			<?php endif; ?>
			

		
	
		</div>
			<?php if ($this['modules']->count('footer + debug') || $this['config']->get('warp_branding')) : ?>
			<footer id="footer" class="grid-block">
	
				<?php if ($this['config']->get('totop_scroller')) : ?>
				<a id="totop-scroller" href="#page"></a>
				<?php endif; ?>
	
				<?php
					echo $this['modules']->render('footer');
					$this->output('warp_branding');
					echo $this['modules']->render('debug');
				?>

			</footer>
	<?php endif; ?>
		
		<?php echo $this->render('footer'); ?>
		
	</div>
	
</body>
</html>