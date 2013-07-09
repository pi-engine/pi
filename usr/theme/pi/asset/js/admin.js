$(function() {
  /* For scroll shadow */
  var a = $('.pi-content-inner'),
     b = $('.module-menu-shadow').css('transition', 'opacity .2s ease-in-out');
  a.scroll(function(){
     if (a.scrollTop() > 5) {
         b.css('opacity', .4);
     } else {
         b.css('opacity', 0);
     }
  })
  /* For debug info */
  $("#pi-logger-output").insertAfter($("#pi-footer"));
});