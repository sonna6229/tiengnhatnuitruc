<?php
/* Template Name: Contact Template */
get_header();
?>
<div class="main">
<?php
	if ( function_exists('yoast_breadcrumb') )
	{
		yoast_breadcrumb('<div id="breadcrumbs"><div class="content">','</div></div>');
	}
?>
	<div class="content contact">
	    <div class="contentLeft">
			<h2>Trung tâm tiếng nhật núi trúc</h2>
			<p>Điện thoại: (04) 38 460 341</p>
			<p>Fax: (04) 38 460 341</p>
			<p>Địa chỉ: số 15, ngõ Núi Trúc, Kim Mã , Ba Đình , Hà Nội.</p>
			<div class="form-contact">
				<h2 class="block-title"><i class="fa fa-address-card icon_title" aria-hidden="true"></i>Liên hệ với chúng tôi</h2>
				<img src="http://trungtamtiengnhat.edu.vn/wp-content/uploads/2016/06/trung_tam_tieng_nhat_nui_truc_google_map-700x250.png" alt="Trung tâm tiếng nhật núi trúc google map" class="aligncenter size-large wp-image-1822" />
			</div>
		</div>
	  	<!--/end content left-->
		<div class="sidebar">
			<?php get_sidebar(); ?>
		</div>
		<div class="clearfix"></div>
	</div>
</div>
<?php
get_footer();
?>