/**
 * @file az_nav_menu.js
 * Controller that loads content into blocks.
 */
(function ($) {
  'use strict';

  if (!Drupal.az) {
    Drupal.az = {};
  }

  Drupal.az.contentC = function () {

    var $container;
    var $tabs;
    var $pages;

    function getSet(id) {
      return drupalSettings.azcontent[id];

    }

    function initBlocks ($cont, context) {
      var id = $cont[0].id.replace('az-page-', '');
      var set = getSet(id);
      if (!set.load || !set.load == 'immediate') {
        getContent(getSet(id));
      }
    }

    function initTabs ($cont, context) {
      $container = $cont;
      $tabs = $container.find('.az-tab');
      $pages = $container.find('.az-page');

      var id = $tabs[0].id;
      $('#az-tab-' + id).addClass('active');  // Set the first tab active.
      $('#az-page-' + id).addClass('active');  // Set the first tab active.
      var set = getSet(id);
      getContent(set);
      $('#' + set['id']).addClass('active');

      $tabs.click(function () {        // Set event handlers on tabs
        var id = this.id.replace('az-tab-', '');
        var set = getSet(id);

        $tabs.removeClass('active');
        $(this).addClass('active');
        $pages.removeClass('active');
        $container.find('#az-page-' + id).addClass('active');

        if (!set['loaded']) {
          getContent(set);
        }
      });

//    var set = getSet(this.id.replace('tab-', ''));
//    getContent(set);

      // Initialize content
      $('.content-content', context).once('az-attached').each(function() {
        var set = drupalSettings.azcontent[this.id.replace('az-tab-', '')];

        // Load the block-content - no tab to trigger it.
//      if ($(this).hasClass('block-content')) {
//        getContent(set);
//      }
      });

    }

    var doAjax = function doAjax(url, data, successCallback, errorCallback) {
      $.ajax({
        url: url,
        type: 'POST',
        data: JSON.stringify(data),
        contentType: "application/json; charset=utf-8",
        processData: false,
        success: function (response) {
          if (Array.isArray(response) && response.length > 0) {
            if (response[0].data && response[0].data.message) {
              alert(response[0].data.message);
            }
            if (successCallback) successCallback(response);
          } else {
            (errorCallback) ? errorCallback(response) : successCallback(response);
          }
          return false;
        },
        error: function (response) {
          alert('az_nav_menu::doAjax: ' + response.responseText);
          (errorCallback) ? errorCallback(response) : successCallback(response);
        }
      });
    };

    var contentLoaded = function (response) {
      for (var i = 0; i < response.length; i++) {
        if (response[i].command == 'GetContentCommand') {
          var set = response[i].set;
          var $contentContainer = $('#az-page-' + set['id'] + ' .page-content'); // destination container

          switch (set.type) {
            case 'entity-table':
            case 'entity-stream':

              // Remove the old more button
              $contentContainer.find('.more-button').remove();
              // Append the new stream html
              $contentContainer.append(response[i].content);
              // Find the new more button
              var $moreButton = $contentContainer.find('.more-button');

              // If we're at the end then remove the more button
              if (set.pageNum * set.pageNumItems + set.numRows >= set.totalRows) {
                $moreButton.remove();
              }
              else {
                // Set click event handler on more button.
                $moreButton.click(function () {
                  if (set.type == 'entity-stream') {
                    set.pageNum++;   // Increment the page number.
                  }
                  getContent(set);
                });
              }
              break;
            case 'entity-render':
              // Append the new stream html
              $contentContainer.append(response[i].content);
              break;
          }
        }
      }
    };

    var getContent = function (set) {
      set['loaded'] = true;
      doAjax('/content/getContent', set, contentLoaded);
    };

    return {
      initBlocks: initBlocks,
      initTabs: initTabs
    };
  };

  Drupal.behaviors.az_nav_menu = {
    // Attach functions are executed by Drupal upon page load or ajax loads.
    attach: function (context, settings) {
      if (!Drupal.az.content) {  // Ensures we only run this once
        Drupal.az.content = Drupal.az.contentC();
      }
      $('.az-tabs').once('az-attached').each(function () {
        Drupal.az.content.initTabs($(this), context);
      });
      $('.content-block').once('az-attached').each(function () {
        Drupal.az.content.initBlocks($(this), context);
      });
    }
  };

}(jQuery));

