<!-- sidebar -->
<aside class="sidebar" role="complementary">
	<?php if(is_front_page()){ ?>
	<?php get_template_part('skillshare'); ?>
	<?php }else{ ?>
	<?php get_template_part('recentposts'); ?>
	<?php }; ?>
	<?php if(!is_front_page()): ?>
		<script async src="//pagead2.googlesyndication.com/pagead/js/adsbygoogle.js"></script>
		<!-- Single post - 1 (trungtamtiengnhat.edu.vn) -->
		<ins class="adsbygoogle"
			 style="display:block"
			 data-ad-client="ca-pub-2759348951124535"
			 data-ad-slot="8610571400"
			 data-ad-format="auto"></ins>
		<script>
			(adsbygoogle = window.adsbygoogle || []).push({});
		</script>
	<?php endif; ?>
	<!--thu vien ho tro & thong tin nhat ban -->
		<div id="jvca-japan-new" class="module-item">
			<h2 class="block-title"><i class="fa fa-newspaper-o icon_title" aria-hidden="true"></i>THÔNG TIN NHẬT BẢN</h2>
			<section class="newsList">

			  <?php $japan_news_query = new WP_Query(array(
				'category_name' => 'thong-tin-nhat-ban',
				'showposts' => 3
			  )); ?>
			<?php if ( $japan_news_query->have_posts() ) : ?>

			  <?php while($japan_news_query->have_posts()) : $japan_news_query->the_post(); ?>
				<article>
				  <figure>
					<?php if(has_post_thumbnail()): ?>
					  <a href="<?php the_permalink(); ?>" title="<?php the_title_attribute(); ?>">
						<?php echo get_the_post_thumbnail(get_the_ID(),'nanosmall'); ?>
					  </a>
					<?php else: ?>
					  <img class="attachment-post-thumbnail" src="<?php  echo get_template_directory_uri();?>/images/no-thumb_80x80.png" alt="no image">
					<?php endif;?>
				  </figure>
				  <h4 class="post-title"><a href="<?php the_permalink(); ?>" title="<?php the_title_attribute(); ?>"><?php the_title(); ?></a></h4>
				  <div class="entry-content">
					<p>
					  <?php
					  if(has_excerpt()){
						echo the_excerpt();
					  }else{
						echo mb_substr(the_content(),0,170,'UTF-8').'...';
					  }
					  ?>
					</p>
				  </div>
				</article>
			  <?php endwhile; ?>

			  <?php
				else:
				  echo 'sorry, no post not found';
				endif;
			  ?>
			  <?php wp_reset_postdata(); ?>
			</section>
		</div>
        <div class="clearfix"></div>
        <div id="module-wrap">
          <div id="jvca-tool" class="module-item">
            <h2 class="block-title"><i class="fa fa-university icon_title" aria-hidden="true"></i>THƯ VIỆN HỖ TRỢ</h2>
            <ul class="tool-list">
              <?php $tools_query = new WP_Query(array(
                'category_name' => 'thu-vien-ho-tro',
                'showposts' => 6
              )); ?>
              <?php if($tools_query->have_posts()): ?>
                <?php while($tools_query->have_posts()) : $tools_query->the_post(); ?>
					<li>
						<i class="fa fa-mobile icon_left" aria-hidden="true"></i>
						<a href="<?php the_permalink(); ?>" title="<?php the_title_attribute(); ?>"><?php the_title(); ?></a>
					</li>
                <?php endwhile; ?>
              <?php
                else:
                  echo 'sorry, no post not found !';
                endif; ?>
                <?php wp_reset_postdata(); ?>
            </ul>
          </div>
		</div>

    <!--thu vien ho tro & thong tin nhat ban -->
	<div class="sidebar-widget">
		<?php if(!function_exists('dynamic_sidebar') || !dynamic_sidebar('widget-area-1')) ?>
	</div>
        
	<div class="sidebar-widget">
		<?php if(!function_exists('dynamic_sidebar') || !dynamic_sidebar('widget-area-2')) ?>
	</div>
</aside>
<!-- /sidebar -->
