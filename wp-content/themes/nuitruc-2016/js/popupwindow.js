jQuery( document ).ready(function(){
  //open popup trigger
  jQuery('.keyopen').toggle(
    function(){
      jQuery(this).parent('#popupwindow').animate({'left':'0px'} , 'slow');
      jQuery('.btnClose').show('slow');
    },function(){
      jQuery(this).parent('#popupwindow').animate({'left':'-270px'} , 'slow');
      jQuery('.btnClose').hide('slow');
    });
  //close popup trigger
  jQuery('.btnClose').on('click',function(){
    jQuery(this).parent('#popupwindow').animate({'left':'-270px'} , 'slow');
    jQuery('.btnClose').hide('slow');
  });

  if ( jQuery(window).width() < 768) {
    jQuery('.btnClose').hide();
    //open popup trigger
    jQuery('.keyopen').toggle(
      function(){
        jQuery(this).parent('#popupwindow').animate({'bottom':'0px'} , 'slow');
      },function(){
        jQuery(this).parent('#popupwindow').animate({'bottom':'-329px'} , 'slow');
      });
  }

});
