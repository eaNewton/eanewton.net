//JS for Label Effects
jQuery(document).ready(function($){

  $(window).load(function() {
    $(".col-3 input").val("");

    $(".input-effect input").focusout(function() {
      if($(this).val() !== "") {
        $(this).addClass("has-content");
      } else {
        $(this).removeClass("has-content");
      }
    })
  });

    $('.mobile-menu-trigger').click(function() {
      $('.mobile-nav-container').toggleClass('expand')
      $('.mobile-menu-trigger').toggleClass('btn-none')
    })

});
