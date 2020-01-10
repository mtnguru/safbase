/**
 * @file
 * Juilliard - Left Nav Behaviors
 *
 * @type {{attach: Drupal.behaviors.leftNav.attach}}
 */

(function ($) {
  'use strict';

  // Wrap code in a function to make variables local.
  Drupal.leftNavC = function () {
    var namespace = 'leftNav';
    var leftNavIsOpen = 'leftnav-is-open';
    var leftNavActive = 'leftnav-active';

    var $leftNavOpen;
    var $leftNavMenu;
    var $leftNavItems;
    var $toolbar;
    var $topbar;
    var $sidebar;
    var $sidebarContainer;
    var $sections;
    var $footer;

    var topTrigger;
    var topSpacing;
    var bottomSpacing;
    var maxScrollTime = 1000;

    var isMobile;

    // Open the Navigation menu - only done in mobile.
    function openNav() {
      $leftNavOpen.addClass(leftNavIsOpen);
      $leftNavMenu.addClass(leftNavIsOpen);
      $leftNavMenu.find('li:eq(0) a').focus();
    }

    // Close the navigation menu
    function closeNav() {
      $leftNavOpen.removeClass(leftNavIsOpen);
      $leftNavMenu.removeClass(leftNavIsOpen);
    }

    // Initialize the Navigation behaviors
    function init(context) {
      $toolbar = $('#toolbar-bar');
      $topbar = $('#site-header');
      $footer = $('.field--type-comment');
      $sidebarContainer = $(context).find('#content-sidebar-container');
      $sidebar = $(context).find('.content-sidebar');
      $leftNavOpen = $(context).find('.leftnav-open');
      $leftNavMenu = $(context).find('.leftnav-menu');
      $leftNavItems = $leftNavMenu.find('li');
      $sections = $(context).find('.leftnav-section');

      // Desktop mediaquery event listener - Open nav menu (always open on desktop).
      $(window).on('desktop.' + namespace, function (e) {
        openNav(e);
      });

      // Mobile mediaquery event listener - Initially close the nav menu.
      $(window).on('mobile.' + namespace, function (e) {
        closeNav(e);
      });

      // Nav menu open button event listener - User selects "Jump to a Section" button.
      $leftNavOpen.on('click', function (e) {
        e.preventDefault();
        if ($leftNavMenu.hasClass(leftNavIsOpen)) {
          closeNav(e);
        }
        else {
          openNav(e);
        }
      });

      // Search for a nav menu item with a href of: '#' + id
      function selectMenuItem(id) {
        $leftNavMenu.find('li').removeClass(leftNavActive);
        for (var i = 0; i < $leftNavItems.length; i++) {
          var $item = $($leftNavItems[i]);
          var href = $item.find('a').attr('href');
          if (href === '#' + id) {
            $item.addClass(leftNavActive);
            break;
          }
        }
      }

      // Set the height of the sidebar container = to height of sidebar container contents.
      // Holds space when sidebar contents become fixed.
      function setSidebarContainerDims() {
        $sidebarContainer.height($sidebar.outerHeight());
      }

      // Listen for window resize events.
      window.addEventListener('resize', function () {
        setSidebarContainerDims();
      });

      // Mobile mode.
      $(window).on('mobile.' + namespace, function () {
        isMobile = true;
        topSpacing = $topbar.height() + $leftNavOpen.outerHeight() + 10;
        topTrigger = $topbar.height();
        setCurrentLocation();
      });

      // Desktop mode.
      $(window).on('desktop.' + namespace, function (context) {
        isMobile = false;
        topTrigger = $topbar.height() + 75;
        topSpacing = $topbar.height() + 75;
        bottomSpacing = 50;
        setCurrentLocation();
      });

      // Listen for scroll events.
      $(window).scroll(function () {
        setCurrentLocation();
      });

      // From current location fix/unfix sidebar nav, indicate current section.
      function setCurrentLocation() {
        var scrollTop = $(window).scrollTop();

        // Fix the sidebar to the top when scrolling.
        if (scrollTop >= $sidebarContainer.offset().top - topTrigger) {
          $sidebar.addClass('content-sidebar-fixed');
        }
        else {
          $sidebar.removeClass('content-sidebar-fixed');
        }

        // Look for when the bottom of the .content-container is within bottomSpacing of the bottom of the nav sidebar.
        if (scrollTop + $sidebar[0].offsetTop + $sidebar[0].offsetHeight + bottomSpacing >= $footer[0].offsetTop) {
          $sidebar.addClass('content-sidebar-bottom');
        }
        else {
          $sidebar.removeClass('content-sidebar-bottom');
        }

        // Find menu item currently displayed in viewport.
        var s;
        var sb;
        for (s = 0; s < $sections.length; s++) {
          var section = $sections[s];
          sb = $sections.length - 1;
          if (scrollTop < section.offsetTop - topSpacing - 10) {
            sb = (s === 0) ? 0 : s - 1;
            break;
          }
        }
        // Set text in Open button to currently displayed menu item.
        if (s !== 0) {
          var i = (sb > $leftNavItems.length - 1) ? $leftNavItems.length - 1 : sb;
          $leftNavOpen.text($leftNavItems[i].innerText);
        }
        // Set class on menu item for currently displayed section.
        selectMenuItem($sections[sb].id);
      }

      function scrollToSection(target) {
        var newTop;
        if (isMobile) {
          var offset = 10 + $leftNavOpen.outerHeight(true) + $topbar.outerHeight(true);
          if ($toolbar && $('body').hasClass('toolbar-fixed')) {
            offset += $toolbar.height();
          }
          newTop = $(target.hash).offset().top - offset;
        }
        else {
          newTop = $(target.hash).offset().top - 165;
        }
        var distance = Math.abs(newTop - $(window).scrollTop());
        var scrollTime = (distance < maxScrollTime) ? distance : maxScrollTime;

        $('html, body').animate({scrollTop: newTop + 'px'}, {duration: scrollTime, easing: 'swing'});
      }

      // Event - User clicks on nav item - scroll to selected section and close nav menu.
      $leftNavMenu.find('a').click(function (e) {
        e.preventDefault();
        scrollToSection(e.target);

        if (isMobile) {
          closeNav();
        }
      }).on('keydown', function (e) { // User presses ENTER button
        if (e.keyCode === 13) {
          e.preventDefault();
          scrollToSection(e.target);
          $(e.target.hash).find('.back-to-menu-anchor').focus();

          if (isMobile) {
            closeNav();
          }
        }
      });

      setSidebarContainerDims();

      // Initially select the first nav menu item and its related content section.
      var $firstMenuItem = $leftNavMenu.find('li').first();
      $firstMenuItem.addClass(leftNavActive);
      $sections.first().addClass(leftNavActive);

      // focus first menu item on "back to menu" link (assessibility/desktop)
      $('.back-to-menu-anchor').on('keydown', function (e) {
        if (e.keyCode === 13) {
          e.preventDefault();
          $firstMenuItem.find('a').focus();
        }
      });
    }

    return {
      init: init
    };
  }; // function Drupal.leftNavC - function wrapper to make variables local.

  Drupal.behaviors.leftNav = {
    attach: function (context, settings) {
      $(context).find('.left-nav, .application-requirements').once('leftNav').each(function () {
        if (!Drupal.leftNav) {
          Drupal.leftNav = Drupal.leftNavC();
        }
        Drupal.leftNav.init(this);
      });
    }
  };

}(jQuery));

