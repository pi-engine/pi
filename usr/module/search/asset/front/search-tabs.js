
(function($) {
  var form = $('#js-search-form');
  var moduleUrl = form.data('module-url');
  var serviceUrl = form.data('service-url');

  form.on('click', '.js-search-global', function(e) {
    e.preventDefault();
    form.attr({
      'action': '',
      'target': ''
    });
    form[0].submit();
  }).on('click', '.js-search-module', function(e) {
    var name = $(this).data('name');
    e.preventDefault();
    form.attr({
      'action': moduleUrl.replace('_NAME', name),
      'target': ''
    });
    form[0].submit();
  }).on('click', '.js-search-service', function(e) {
    var name = $(this).data('name');
    e.preventDefault();
    form.attr({
      'action': serviceUrl.replace('_NAME', name),
      'target': '_blank'
    });
    form[0].submit();
  });

})(jQuery)