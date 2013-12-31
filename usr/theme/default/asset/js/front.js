(function($) {
  //Fix navbar multiple level
  $('.pi-navbar').find('>li').each(function() {
    var $this = $(this);

    if ($this.find('ul').length) {
       var link = $this.find('>a');
       var ul = $this.find('>ul');

       $this.addClass('dropdown');
       link.addClass('dropdown-toggle')
           .attr('data-toggle', 'dropdown')
           .append('<span class="caret"></span>');
       ul.addClass('dropdown-menu');
    }
  });
})(jQuery)