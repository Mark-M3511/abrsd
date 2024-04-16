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
      const navbarMain = document.querySelector('#navbar-main');
      const navBarTop = document.querySelector('#navbar-top');

      const top = navbarMain?.clientHeight;
      // Set the top position of the sticky title with !important
      stickyTitle.style.top = `${top}px`;

      // Create an intersection observer
      const observer = new IntersectionObserver(function (entries) {
        // If the element is intersecting (visible in the viewport)
        if (entries[0].isIntersecting === true) {
          // console.log('Element is in the viewport');
          stickyTitle.classList.remove('reveal');
        } else {
          // console.log('Element has left the viewport');
          stickyTitle.classList.add('reveal');
        }
      }, { threshold: [0.5] });

      // Start observing the element
      observer.observe(title);
    }
  };

})(Drupal, drupalSettings);