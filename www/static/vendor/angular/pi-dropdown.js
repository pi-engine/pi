/**
 * @name pi.directive: dropdownToggle
 * @restrict CA
 * @example
   <div class="dropdown">
     <a class="dropdown-toggle">My Dropdown Menu</a>
     <ul class="dropdown-menu">
       <li ng-repeat="choice in dropChoices">
         <a href="javascript:void(0) ng-click="yourAction(choice)">{{choice.text}}</a>
       </li>
     </ul>
   </div>
 */
angular.module('pi')
.directive('dropdownToggle', ['$document',
  function($document) {
      var openElement;
      var closeMenu = angular.noop;
      return {
        restrict: 'CA',
        link: function(scope, element, attrs) {
          var parent = element.parent();
          element.bind('click', function (event) {
            var elementWasOpen = (element === openElement);
            event.preventDefault();
            event.stopPropagation();
            if (openElement) {
              closeMenu();
            }

            if (!elementWasOpen) {
              parent.addClass('open');
              closeMenu = function() {
                $document.unbind('click', closeMenu);
                parent.removeClass('open');
                openElement = null;
                closeMenu = angular.noop;
              };
              openElement = element;
              $document.bind('click', closeMenu);
            }
          });
        }
      }
  }
])