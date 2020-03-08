/**
 * @file
 * Aureon Energey - Top Navigation Menu bar
 *
 * @type {{attach: Drupal.behaviors.menu_top.attach}}
 */

(function ($) {
  'use strict';

  Drupal.menuTopC = function () {
    var $navMenu = $('#js-navigation-menu');
    var $menuToggle = $('#js-mobile-menu');
    var $moreLinks = $('.js-navigation-more');
    var mode = 'large';

    function resizeWindow() {
      var windowWidth = $(window).width();
      if (windowWidth < 960) {
        if (mode == 'large') {
          mode = 'small';
        }
      }
      else {
        if (mode == 'small') {
          mode = 'large';
          $('#js-navigation-menu').show();
        }
      }

      if ($moreLinks.length > 0) {
        var moreLeftSideToPageLeftSide = $moreLinks.offset().left;
        var moreLeftSideToPageRightSide = windowWidth - moreLeftSideToPageLeftSide;

        if (moreLeftSideToPageRightSide < 330) {
          $(".js-navigation-more .submenu .submenu").removeClass("fly-out-right");
          $(".js-navigation-more .submenu .submenu").addClass("fly-out-left");
        }

        if (moreLeftSideToPageRightSide > 330) {
          $(".js-navigation-more .submenu .submenu").removeClass("fly-out-left");
          $(".js-navigation-more .submenu .submenu").addClass("fly-out-right");
        }
      }
    }

    var init = function init() {
      resizeWindow();

      $(window).resize(function () {
        resizeWindow();
      });

      $navMenu.removeClass("show");
      $menuToggle.unbind();

/*    $moreLinks.hover(function() {
        $(this).addClass('menu-open').find('.submenu').slideDown();
      }, function() {
        $(this).removeClass('menu-open').find('.submenu').slideUp();
      }); */

/*
      $moreLinks.hover(function() {
        if ($(this).hasClass('menu-open')) {
          $(this).removeClass('menu-open').find('.submenu').slideUp();
        } else {
          $moreLinks.each(function() {
            if ($(this).hasClass('menu-open')) {
              $(this).removeClass('menu-open').find('.submenu').slideUp();
            }
          });
          $(this).addClass('menu-open').find('.submenu').slideDown();
        }
      });
*/
      $moreLinks.click(function() {
        if ($(this).hasClass('menu-open')) {
          $(this).removeClass('menu-open').find('.submenu').slideUp();
        } else {
          $moreLinks.each(function() {
            if ($(this).hasClass('menu-open')) {
              $(this).removeClass('menu-open').find('.submenu').slideUp();
            }
          });
          $(this).addClass('menu-open').find('.submenu').slideDown();
        }
      });

      $menuToggle.on("click", function (e) {
        e.preventDefault();
        if ($navMenu.hasClass('nav-open')) {
          $navMenu.slideUp().removeClass('nav-open');
        } else {
          $navMenu.slideDown().addClass('nav-open');
        }
      });
    };

    return {
      init: init
    };
  }; // function Drupal.menuTopC - function wrapper to make variables local.


  Drupal.behaviors.menuTop = {
    attach: function (context, settings) {
      $(context).find('#site-header').once('menuTopAttached').each(function () {
        if (!Drupal.menuTop) {
          Drupal.menuTop = Drupal.menuTopC();
        }
        Drupal.menuTop.init(this);
      });

      $(context).find('#dyslexia-button').once('dyslexia-attached').each(function () {
        var dyslexia = localStorage.getItem('dyslexia_font');
        if (dyslexia && dyslexia != 'undefined' && dyslexia == 'TRUE') {
          $('body').addClass('dyslexia');
          $(this).prop('checked', true);
        }

        $(this).click(function(ev) {
          if ($(this).is(":checked")) {
            $('body').addClass('dyslexia');
            localStorage.setItem('dyslexia_font', 'TRUE');
          } else {
            $('body').removeClass('dyslexia');
            localStorage.setItem('dyslexia_font', 'FALSE');
          }
        });
      });
    }
  };

}(jQuery));

