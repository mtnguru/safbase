/**
 * @file
 * Juilliard - Initialize Formstone
 *
 * @type {{attach: Drupal.behaviors.initMediaQuery.attach}}
 */

(function ($) {
  'use strict';

  var init = function () {
    $.mediaquery({
      minWidth: [320, 580, 960, 1280],
      maxWidth: [1280, 960, 580, 320]
    });

    $.mediaquery('bind', 'mq-desktop', '(min-width: 960px)', {
      enter: function () {
        _.defer(function () {
          window.dispatchEvent(new CustomEvent('desktop'));
        });
      },
      leave: function () {
        _.defer(function () {
          window.dispatchEvent(new CustomEvent('mobile'));
        });
      }
    });
  };

  Drupal.behaviors.initMediaQuery = {
    attach: function (context) {
      $(context).find('body').once('initMediaQuery').each(function () {
        init();
      });
    }
  };
}(jQuery));
