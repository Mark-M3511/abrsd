/**
 * @file
 * Global utilities.
 *
 */
(function (Drupal, drupalSettings) {

  'use strict';
  /**
   * @type {Drupal}
   */
  const { behaviors } = Drupal;
  // Attach the behavior.
  behaviors.sticky_title = {
    attach: function (context, settings) {

      // Get the element
      const title = document.querySelector('.field--name-title');
      const stickyTitle = document.querySelector('.sticky-title');

      // Create an intersection observer
      const observer = new IntersectionObserver(function (entries) {
        // If the element is intersecting (visible in the viewport)
        if (entries[0].isIntersecting === true) {
          console.log('Element is in the viewport');
          if (!stickyTitle.classList.contains('d-none')) {
            stickyTitle.classList.add('d-none');
          }
        }
        else {
          console.log('Element has left the viewport');
          if (stickyTitle.classList.contains('d-none')) {
            stickyTitle.classList.remove('d-none');
          }
        }
      }, { threshold: [0] });

      // Start observing the element
      observer.observe(title);
    }
  };

})(Drupal, drupalSettings);