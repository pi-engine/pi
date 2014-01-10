(function($) {
  //For angularjs nav
  var fragReg = /#!?(.*)$/;
  $('#pi-nav-top').find('.nav-pills li').each(function() {
    var $this = $(this);
    var match = $this.find('a').attr('href').match(fragReg);
    if (!match) return false;
    $this.removeClass('active').attr('ng-class', 'navClass("' + match[1] + '")');
  });
  
  //For debug
  $(function() {
    var debug = $('#pi-logger-output');
    debug.length &&  debug.insertAfter($('.pi-module-content'));
  });
})(jQuery)
