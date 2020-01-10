/**
 * @file
 * Ethereal Matters - Top Tabs
 *
 * @type {{attach: Drupal.behaviors.tabs.attach}}
 */

(function ($) {
  'use strict';

  Drupal.behaviors.azTabs = {
    attach: function (context, settings) {
      $(context).find('.az-tabs').once('az-attached').each(function () {
        var $tabs = $(this).find('.tab');
        $($tabs[0]).addClass('active');

        $tabs.each(function() {
          $(this).click(function (ev) {
            $tabs.removeClass('active');
            $(this).addClass('active');
          });
        });
      });
    }
  };

}(jQuery));

