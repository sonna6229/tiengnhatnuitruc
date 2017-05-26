<?php get_header(); ?>

<div class="main">
	<?php
		if ( function_exists('yoast_breadcrumb') )
		{
			yoast_breadcrumb('<div id="breadcrumbs"><div class="content">','</div></div>');
		}
	?>
	<div class="content">
    <div class="contentLeft">
			<!-- section -->
			<section class="post_details">

			<?php if (have_posts()): while (have_posts()) : the_post(); ?>

			  <!-- article -->
			  <article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>

			    <!-- post title -->
			    <h1 class="block-title">
			      <?php the_title(); ?>
			    </h1>
			    <!-- /post title -->

			    <!-- post details -->
					<div class="social_info">
				    <span class="date">
							<span class="hours"><?php the_time('g:i'); ?></span>
							<span class="MST"><?php the_time('m/d/Y'); ?></span>
						</span>
						<ul class="list">
							<li>
								<a href="javascript:void(0);" class="btnFbShare">
									<img src="<?php echo get_template_directory_uri(); ?>/img/ico_fb_post_detail.png" alt="Trung tâm tiếng nhật núi trúc trên facebook">
								</a>
							</li>
							<li>
	<a href="https://plus.google.com/share?url={<?php the_permalink(); ?>}" onclick="javascript:window.open(this.href,
'', 'menubar=no,toolbar=no,resizable=yes,scrollbars=yes,height=600,width=600');return false;">
									<img src="<?php echo get_template_directory_uri(); ?>/img/ico_gg_post_detail.png" alt="Share on Google+">
								</a>
							</li>
							<li>
								<a href="mailto:nguyenanhson1987@gmail.com?Subject=Trung tâm tiếng nhật Núi Trúc có thể giúp gì được cho bạn !">
									<img src="<?php echo get_template_directory_uri(); ?>/img/ico_date_emal_detail.png" alt="Liên hệ với trung tâm tiếng nhật núi trúc qua email">
								</a>
							</li>
							<li>
								<a href="javascript:void(0);">
									<img src="<?php echo get_template_directory_uri(); ?>/img/ico_print_post_detail.png" alt="print post">
								</a>
							</li>
						</ul>
						<div class="clearfix"></div>
					</div>
			    <!-- /post details -->
					<div class="desc">
						<?php the_excerpt(); ?>
					</div>
			    <?php the_content(); // Dynamic Content ?>

			  </article>
			  <!-- /article -->
			<?php endwhile; ?>

			<?php else: ?>

			  <!-- article -->
			  <article>

			    <h1><?php _e( 'Sorry, nothing to display.', 'html5blank' ); ?></h1>

			  </article>
			  <!-- /article -->

			<?php endif; ?>

			</section>
			<!-- /section -->
             <script async src="//pagead2.googlesyndication.com/pagead/js/adsbygoogle.js"></script>
				<!-- Single post - 2 (trungtamtiengnhat.edu.vn) -->
				<ins class="adsbygoogle"
					 style="display:block"
					 data-ad-client="ca-pub-2759348951124535"
					 data-ad-slot="5532680609"
					 data-ad-format="auto"></ins>
				<script>
				(adsbygoogle = window.adsbygoogle || []).push({});
			</script>

		    <div class="fb-comments" data-href="<?php the_permalink() ?>" data-width="100%" data-numposts="5"></div>

			<?php
				$tags = wp_get_post_tags($post->ID);
				if($tags){
					$tag_ids = array();
					foreach ($tags as $item) $tag_ids[] = $item->term_id;
					$args = array(
						'tag__in' => $tag_ids,
						'post__not_in' => array($post->ID),
						'posts_per_page' => 10
					);
					$postsByTag = new WP_Query($args);
				}
			?>
			<!-- /relatedposts -->
			<div class="relatedposts">
				<h2 class="block-title"><i class="fa fa-newspaper-o icon_title" aria-hidden="true"></i>TIN LIÊN QUAN</h2>
				<ul class="items">
				<?php
					while ($postsByTag->have_posts()) : $postsByTag->the_post();

				?>
 					<li>
						<a href="<?php the_permalink(); ?>" title="<?php the_title_attribute(); ?>">
							<?php the_title(); ?> <span class="datetime">(<?php the_time('d/m/Y g:i'); ?>)</span>
						</a>
					</li>
				<?php endwhile; ?>
				<?php wp_reset_query(); ?>
				</ul>
			</div>
		</div>
	  <!--/end content left-->
		<div class="sidebar">
			<?php get_sidebar(); ?>
		</div>
		<div class="clearfix"></div>
	</div>
</div>

<?php get_footer(); ?>