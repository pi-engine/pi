$(document).ready(function ($) {
    $('.owl-carousel').owlCarousel({
        //loop:true,
        //lazyLoad:true,
        margin: 3,
        nav: true,
        rtl: true,
        autoplay: true,
        dots: false,
        autoplayTimeout: 12000,
        autoplayHoverPause: true,
        navText: ['<i class="fa fa-angle-left"></i>', '<i class="fa fa-angle-right"></i>'],
        responsive: {
            0: {items: 2},
            600: {items: 2},
            1000: {items: 2}
        }
    })
});