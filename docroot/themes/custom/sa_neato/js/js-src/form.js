/**
 * @file
 * Atomizer - Form Focus
 *
 * @type {{attach: Drupal.behaviors.az_form.attach}}
 */

(function ($) {
  'use strict';

  function setPopulated($field) {
    if ($.trim($field.val()).length) {
      $field.addClass('populated').parent().addClass('populated');
    }
    else {
      $field.removeClass('populated').parent().removeClass('populated');
    }
  }

  var init = function ($field) {

    setPopulated($field);

    $field.on('blur', function () {
      setPopulated($(this));
    });

  };

  Drupal.behaviors.az_form = {
    attach: function (context) {
//    $(context).find('input[type=date], input[type=email], input[type=password], input[type=tel], input[type=text], textarea').once('AzForm').each(function () {
      var $fart = $(context).find('input');
      $(context).find('input').once('AzForm').each(function () {
        init($(this));
      });
    }
  };
}(jQuery));
