/**
 * @file
 * Comments utilities - Modify comment behaviour for user profiles
 */
(function (Drupal, drupalSettings) {

    'use strict';

    const { behaviors } = Drupal;

    behaviors.abrsd = {
        attach: function (context, settings) {
            const elem = context.querySelector('.field--name-field-blog-comment') || document.querySelector('.field--name-field-blog-comment');
            // Add a click event listener for the element
            elem?.addEventListener('click', function (event) {
                // Check if the target is an image
                if (event.target.matches('img.image-style-thumbnail')) {
                    // Don't follow the link
                    event.preventDefault();
                }
            });
        }
    };

})(Drupal, drupalSettings);