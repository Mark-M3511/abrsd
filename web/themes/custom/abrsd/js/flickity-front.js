/**
 * @file
 * Global utilities.
 *
 */
(function (Drupal, drupalSettings) {

    'use strict';

    const { behaviors } = Drupal;

    behaviors.abrsd = {
        attach: function (context, settings) {
            const elem = document.querySelector('.blog-carousel');
            if (elem) {
                const flkty = new Flickity(elem, {
                    // options
                    cellAlign: 'left',
                    contain: true,
                    wrapAround: true,
                    autoPlay: false,
                    prevNextButtons: true,
                    pageDots: true,
                });
            }
        }
    };

})(Drupal, drupalSettings);