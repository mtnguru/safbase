/**
 * @file
 * Open and close hierarchical menus
 *
 * @type {{attach: Drupal.behaviors.menu.attach}}
 */

(function ($) {
  'use strict';

  Drupal.behaviors.menu = {
    attach: function (context, settings) {
      var expandClass = 'menu-item--expanded';

      $(context).find('#book-pages').once('menuAttached').each(function () {
        $('#book-pages .menu-item--children', context).click(function (event) {
          if (event.target.tagName === 'LI') {
            event.stopPropagation();

            if ($(this).hasClass(expandClass)) {
              $(this).removeClass(expandClass);
            }
            else {
              $(this).siblings().removeClass(expandClass)
              $(this).addClass(expandClass);
            }
          }
        });

        var hide = localStorage.getItem('atomizer_hide_unpublished');
        if (hide && hide != 'undefined' && hide == 'TRUE') {
          $('#book-pages').removeClass('hide-unpublished');
        }

        $('#book-show-all').click(function() {
          if ($('#book-pages').hasClass('hide-unpublished')) {
            $('#book-pages').removeClass('hide-unpublished');
            localStorage.setItem('atomizer_hide_unpublished', 'TRUE');
          } else {
            $('#book-pages').addClass('hide-unpublished');
            localStorage.setItem('atomizer_hide_unpublished', 'FALSE');
          }
        });
      });
    }
  };

}(jQuery));

