<!--recentposts-->
<?php
$skill_posts_query = new WP_Query( array(
    'category_name' => 'chia-se-kinh-nghiem',
    'showposts' => 4
) );
?>
<div class="recentposts">
  <h2 class="block-title"><i class="fa fa-facebook icon_title" aria-hidden="true"></i>CHIA SẺ KINH NGHIỆM</h2>
  <section class="newsList">
  <?php if ( !empty($skill_posts_query) ) : ?>

    <?php while($skill_posts_query->have_posts()) : $skill_posts_query->the_post(); ?>
      <article>
        <figure>
          <?php if(has_post_thumbnail()): ?>
            <a href="<?php the_permalink(); ?>" title="<?php the_title_attribute(); ?>"><?php echo get_the_post_thumbnail(get_the_ID(),'nanosmall' ); ?></a>
          <?php else: ?>
            <a href="<?php the_permalink(); ?>" title="<?php the_title_attribute(); ?>"><img class="attachment-post-thumbnail" src="<?php  echo get_template_directory_uri();?>/images/no-thumb_80x80.png" alt="no image"></a>
          <?php endif;?>
        </figure>
        <div class="post_date"><?php the_time('d/m/Y g:i'); ?></div>
        <h4 class="post-title"><a href="<?php the_permalink(); ?>" title="<?php the_title_attribute(); ?>"><?php the_title(); ?></a></h4>
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
<!--recentposts-->
