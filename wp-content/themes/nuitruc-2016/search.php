<?php get_header(); ?>

	<div class="main">
		<!--/content-->
		<div class="content">
			<!--contentLeft-->
			<div class="contentLeft">
					<h1 class="search-found"><?php echo sprintf( __( '%s Kết quả tìm kiếm : ', 'html5blank' ), $wp_query->found_posts ); echo get_search_query(); ?></h1>
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
	</div>

<?php get_footer(); ?>
