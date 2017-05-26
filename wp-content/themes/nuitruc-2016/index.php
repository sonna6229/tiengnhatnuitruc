<?php get_header(); ?>

	<?php if (  is_front_page() ) :
		get_template_part('template','index');
	endif; ?>

<?php //get_sidebar(); ?>

<?php get_footer(); ?>
