/**
 * Implement div with max-height which hides content - more button slides down remaining content.
 */
(function ($) {
  'use strict';

  Drupal.behaviors.moreSlider = {
    attach: function(context, settings) {
      var $slider = $('.az-more-wrapper', context);
      $('.az-more-wrapper', context).once('azActivated').each(function() {
        var $moreWrapper = $(this);
        var $moreSlider = $moreWrapper.find('.az-more-slider');
        var $moreSliderButton = $moreSlider.siblings('.az-more-slider-button');
        var finalHeight;
        var fadeHeight = $moreSlider.height();
        var maxDelay = 1000;   // Maximum time to open/close slider
        var fullHeight;

        function initSlider() {
          // Content is smaller than space and doesn't need hiding, hide overlay, hide more button
          fullHeight = $moreSlider.get(0).scrollHeight;
          if (fullHeight < fadeHeight + 80) {
            $moreSlider.css('maxHeight', fullHeight + 'px');
            $moreSlider.removeClass('az-fade-out');
            $moreSliderButton.hide();
            $moreSlider.find('.az-more-slider-overlay').hide();
          } else {
            $moreSlider.addClass('az-fade-out');
            $moreSliderButton.show();
            $moreSlider.find('.az-more-slider-overlay').show();
          }
        }

        // User clicks 'more/less' link
        $moreSliderButton.click(function (evt) {
          evt.preventDefault();
          if ($moreSlider.hasClass('az-open')) {
            // Close the slider

            finalHeight = fadeHeight;
            // Show fade overlay
            $moreSlider.removeClass('az-open').find('.az-more-slider-overlay').show(100);
            // Change the more/less link label
            $moreSliderButton.html('&mdash; more &mdash;');

            // If the top of moreSlider is above the screen scroll screen down to put top of item at top of screen
            var scrollTop = $(window).scrollTop();
            var itemTop = $moreSlider.offset().top - fadeHeight;
            if (itemTop - scrollTop < 0) {
              var delay = fullHeight - fadeHeight;
              $('html, body').animate({ scrollTop: itemTop + 'px'}, (delay > maxDelay) ? maxDelay : delay, 'swing');
            }
            $moreWrapper.addClass('az-open');
          } else {
            // Open the slider

            finalHeight = $moreSlider.get(0).scrollHeight;
            // Hide fade overlay
            $moreSlider.addClass('az-open').find('.az-more-slider-overlay').hide(100);
            // Change the more/less link label
            $moreSliderButton.html('&mdash; less &mdash;');
            $moreWrapper.addClass('az-open');
          }

          var delay = fullHeight - fadeHeight;
          $moreSlider.animate({maxHeight: finalHeight}, {duration: (delay > maxDelay) ? maxDelay : delay, easing: 'swing'});
        });

        window.addEventListener('resize', function () {
          initSlider();
        });

        initSlider();
      });
    }
  };

}(jQuery));

