/**
 * @file
 * Global utilities.
 *
 */
(function (Drupal, drupalSettings) {

    'use strict';

    Drupal.behaviors.bootstrap_sass = {
        attach: function (context, settings) {

            // Custom code here
            const elem = document.querySelector('.blog-carousel');
            const flkty = new Flickity( elem, {
                // options
                cellAlign: 'left',
                contain: true,
                wrapAround: true,
                autoPlay: 2500,
                prevNextButtons: false,
                pageDots: true,
            });
        }
    };

})(Drupal, drupalSettings);