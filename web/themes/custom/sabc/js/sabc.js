(function ($) {

  Drupal.behaviors.expandNavOnHover = {
    // Make Primary Nav expand on hover
    attach: function (context, settings) {
      $(".navbar-nav .dropdown").hover(
      function(){
        $(this).addClass("show");
        $("ul.dropdown-menu", this).addClass("show");
      },function(){
        $(this).removeClass("show");
        $("ul.dropdown-menu", this).removeClass("show");
      });
    }
  }

  Drupal.behaviors.preventPDFInBrowser = {
    attach: function (context, settings) {
      // Force user to download fillable PDF's and not open in browser
      $(".prevent-left-click").click(function(e){
        e.preventDefault();
        $("#preventLeftClickModal").modal('show');
      });
    }
  }

// reintroduce collapsible fieldsets - this was removed in Drupal 8 in favor of <details> blocks
// use this if you don't want to switch to <details>

//   // initialize
//   jQuery('.fieldset-wrapper').collapse('show');
//   jQuery('.accordion legend').addClass('open');

//   // click toggles
//   jQuery('.accordion legend').on('click', function() {
//     var local = jQuery(this);

//     if(local.hasClass('open')) {
//       console.log('clicked to close');
//       jQuery(this).removeClass('open');
//       jQuery(this).parent().children('.fieldset-wrapper').collapse('hide');
//     } else {
//       console.log('clicked to open');
//       jQuery(this).addClass('open');
//       jQuery(this).parent().children('.fieldset-wrapper').collapse('show');
  
//     }
//   });
 
})(jQuery);