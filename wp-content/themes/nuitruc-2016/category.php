<?php get_header(); ?>

	<div class="main">
		<div id="mainvisual">
			<?php echo do_shortcode("[crellyslider alias=home-page-slider] "); ?>
		</div>
		<?php
			if ( function_exists('yoast_breadcrumb') )
			{
				yoast_breadcrumb('<div id="breadcrumbs"><div class="content">','</div></div>');
			}
		?>
		<!--/content-->
		<div class="content">
			<!--contentLeft-->
			<div class="contentLeft">
					<?php get_template_part('loop'); ?>
					<?php get_template_part('pagination'); ?>
			</div>
			<!--/contentLeft-->
			<!--sidebar-->
			<div class="sidebar">
				<?php get_sidebar(); ?>
			</div>
			<!--/sidebar-->
			<div class="clearfix"></div>

		</div>
		<!--/content-->

		<!--/main-->
	</div>

<?php get_footer(); ?>
