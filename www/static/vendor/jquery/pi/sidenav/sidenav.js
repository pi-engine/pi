/**
 * Example
 * <ul class="pi-sidenav">
    <li class="active">
      <a href="www.eefocus.com"></a>
    <li>
      <a href="#"></a>
      <ul>
        ...
      </ul>
   </ul>
 */
$(function() {
  var sidenav = $('.pi-sidenav');
  sidenav.find('li').each(function() {
    var $this = $(this);

    if ($this.find('ul').length) {
      $this.find('>a').append('<span class="caret"></span>');
    }
  });

  sidenav.on('click', 'a', function(e) {
    var $this = $(this);
    var href = $this.attr('href');
    
    if (href == '#' || !href) {
      e.preventDefault();
      var parent = $this.parent();
      parent.find('>ul').slideToggle(300);
    }
  });

  sidenav.find('.active:last').addClass('active-last');
});