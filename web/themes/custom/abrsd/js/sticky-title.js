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

      // On window scroll, add sticky class to title if it is not in view.
      const title = document.querySelector('.header-sticky');
      window.addEventListener('scroll', function () {
        // if (window.scrollY > 1) {
        //   title?.classList.add('sticky-top');
        //   title?.classList.remove('d-none');
        // } else {
        //   title?.classList.remove('sticky-top');
        //   title?.classList.add('d-none');
        // }
      });
    }
  };

})(Drupal, drupalSettings);