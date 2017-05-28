(function ($, root, undefined) {

	$(function () {

		'use strict';

		// DOM ready, take it away
		$('.menu li').each(function(index,element){

	    if($(this).hasClass('active') ){
	      $(this).find('.lineHover').show();
	    }else{
	      $(this).hover(function(){
	        $(this).find('.lineHover').show();
	      },function(){
	        $(this).find('.lineHover').hide();
	      });
	    };
	  });

	  $('.navbar-toggle').on('click',function(){
	      $('.navTop .menu').slideToggle('500');
	  });

		$('a[href*=#]:not([href=#])').click(function() {
	    if (location.pathname.replace(/^\//,'') == this.pathname.replace(/^\//,'') && location.hostname == this.hostname) {
	      var target = $(this.hash);
	      target = target.length ? target : $('[name=' + this.hash.slice(1) +']');
	      if (target.length) {
	        $('html,body').animate({
	          scrollTop: target.offset().top
	        }, 1000);
	        return false;
	      }
	    }
	  });

		// facebook share
		$('.btnFbShare').on('click',function(){
			FB.ui({
					method: 'stream.share'
			});
			return false;
		});

		//right menu
		var rightNav = $('#right-nav');

		if(rightNav.length > 0){

			var rightNavHeight = rightNav.height(),
			rightTop = rightNav.offset().top;

			$(window).scroll(function() { //when scroll
				var st = $(window).scrollTop();

				if ((rightTop + st + rightNavHeight) < $('#module-wrap').offset().top ) {
					rightNav.stop().animate({'top': rightTop + st}, "slow");
				}
			});
		}

		//course tabs menu
		$('#tabs').find('ul li').first().addClass('active');

		$('#tabs').find('ul li').find('a').on('click', function(e){
			e.preventDefault();
			var _slug = $(this).attr('href').replace('#','');
			$(this).parent().siblings().removeClass('active');
			$(this).parent().addClass('active');

			//call ajax
			$.ajax({
				type: 'POST',
				url: get_course.ajax_url,
				data: {slug:_slug,action: 'get_course_action'},
				dataType: 'JSON',
				success: function(respone){
                    var html = '';
                    respone.map(function(e){
                        html += '<article>';
                        html += '<figure>';
                        html += '<a href='+ e.permalink +' title='+ e.title +'>';
                        html += e.thumbnail;
                        html += '</a>';
                        html += '</figure>';
                        html += '<h3 class="post-title">';
                        html += '<a href='+ e.permalink +' title='+ e.title + '>';
                        html += e.title;
                        html += '</a>';
                        html +='</h3>';
                        html += '<div class="entry-content">';
                        html += e.excerpt;
                        html += '</div>';
                        html += '</article>';
                    });
                    $('.course_list').html('').append(html).hide().fadeIn('10000');
				},
				error: function(respone){
                    $('.course_list').html('').append(respone.responseText).hide().fadeIn('10000');
				}
			});
		});
	});


})(jQuery, this);
