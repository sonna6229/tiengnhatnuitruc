<!--recentposts-->
<?php
$args = array(
          'numberposts' => 5,
          'post_type' => 'post',
          'order'=> 'DESC',
          'orderby' => 'post_date',
          'post_status' => 'publish',
	      'suppress_filters' => true
        );
$recentposts = get_posts( $args );
?>
<div class="recentposts">
  <h2 class="block-title"><i class="fa fa-newspaper-o icon_title" aria-hidden="true"></i>TIN MỚI NHẤT</h2>
  <section class="newsList">
  <?php if ( !empty($recentposts) ) : ?>

    <?php
      foreach ($recentposts as $post): setup_postdata( $post );
    ?>
      <article>
        <figure>
          <?php if(has_post_thumbnail()): ?>
            <?php echo get_the_post_thumbnail(get_the_ID(),'nanosmall' ); ?>
          <?php else: ?>
            <img class="attachment-post-thumbnail" src="<?php  echo get_template_directory_uri();?>/images/no-thumb_80x80.png" alt="no image">
          <?php endif;?>
        </figure>
        <div class="post_date"><?php the_time('d/m/Y g:i'); ?></div>
        <h4 class="post-title"><a href="<?php the_permalink(); ?>" title="<?php the_title_attribute(); ?>"><?php the_title(); ?></a></h4>
      </article>
    <?php endforeach; ?>

    <?php
      else:
        echo 'sorry, no post not found';
      endif;
    ?>
    <?php wp_reset_postdata(); ?>
  </section>
</div>
<!--recentposts-->
