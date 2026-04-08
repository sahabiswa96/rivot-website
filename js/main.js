(function ($) {
    "use strict";
      
    /* meanmenu */
    $('.main-menu nav').meanmenu({
         meanMenuContainer: '.mobile-menu',
         meanScreenWidth: "767"
     });
    
    $(window).on('scroll',function() {

  
        $("#header-sticky").addClass("scroll-header");
  
      });
    

    
    
    /* testimonial-active */
    $('.testimonial-active').owlCarousel({
        loop:true,
        nav:false,
        dots:true,
        autoplay:true,
        animateOut: 'slideOutUp',
        animateIn: 'slideInUp',
        touchDrag : false,
        mouseDrag: false,
        responsive:{
            0:{
                items:1
            },
            767:{
                items:1
            },
            1000:{
                items:1
            }
        }
    })
    
    
    
    // scrollToTop
    $.scrollUp({
      scrollName: 'scrollUp', // Element ID
      topDistance: '300', // Distance from top before showing element (px)
      topSpeed: 300, // Speed back to top (ms)
      animation: 'fade', // Fade, slide, none
      animationInSpeed: 200, // Animation in speed (ms)
      animationOutSpeed: 200, // Animation out speed (ms)
      scrollText: 'UP', // Text for element
      activeOverlay: false, // Set CSS color to display scrollUp active point, e.g '#00FFFF'
    });
    
    // WOW active
    new WOW().init();
    
    
    })(jQuery);