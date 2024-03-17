/**
 * @file
 * Global utilities.
 *
 */
(function (Drupal, drupalSettings) {

    'use strict';

    const { behaviors } = Drupal;

    const caoursel = (elem) => {
        if (!elem) {
            return;
        }
        const flkty = new Flickity(elem, {
            // options
            draggable: true,
            wrapAround: true,
            autoPlay: false,
            prevNextButtons: true,
            pageDots: true,
        });
    }

    behaviors.abrsd = {
        attach: function (context, settings) {
            const elem = document.querySelector('.blog-carousel');
            caoursel(elem);
        }
    };

})(Drupal, drupalSettings);