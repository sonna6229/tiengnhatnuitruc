<?php
/* Template Name: sub thanks template */
?>
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
			<h2>Bạn đã đăng ký nhận thông tin tuyển sinh từ Trung Tâm Tiếng Nhật Núi Trúc . Hãy click <a href="http://trungtamtiengnhat.edu.vn">vào đây</a> để quay về trang chủ .</h2>
      <br>
      <h3>申し込みいただきまして、ありがとうございました 。ホームページに戻るように<a href="http://trungtamtiengnhat.edu.vn">ホーム</a> をクリックしてください。</h3>
      <br>
      <img src="<?php echo get_template_directory_uri(); ?>/images/confimed_thanks.png" width="500" height="313" alt="Cảm ơn bạn đã đăng ký nhận tin từ trung tâm tiếng nhật núi trúc">
		</div>
	  <!--/end content left-->
		<div class="sidebar">
			<?php get_sidebar(); ?>
		</div>
		<div class="clearfix"></div>
	</div>
</div>

<?php get_footer(); ?>
