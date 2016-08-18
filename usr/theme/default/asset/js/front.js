(function ($) {
    //Fix navbar multiple level
    var navbar = $('.pi-navbar-nav');
    var hasBrand = navbar.parents('.navbar').find('.navbar-brand').length;
    navbar.find('>li').each(function () {
        var $this = $(this);
        var caretStr = '<span class="pi-navbar-caret"></span>';
        caretStr += '<span class="pi-navbar-caret pi-navbar-caret-outer"></span>';
        if ($this.find('li').length) {
            $this.append(caretStr);
        }
    });
    navbar.find('ul').addClass('dropdown-menu');

    if (!hasBrand) {
        navbar.css('marginLeft', '-15px');
    }
})(jQuery)