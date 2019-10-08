angular.module("template/pagination/pagination.html", []).run(["$templateCache", function($templateCache) {
  $templateCache.put("template/pagination/pagination.html",
      "<ul class=\"pagination justify-content-center\">\n" +
      "  <li ng-repeat=\"page in pages\" class=\"page-item\" ng-class=\"{active: page.active, disabled: page.disabled}\"><a class=\"page-link\" ng-click=\"selectPage(page.number)\">{{page.text}}</a></li>\n" +
      " </ul>\n" +
      "");
}]);