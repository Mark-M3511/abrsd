// Set up a Drupal JS file to handle form submission loader
/**
 * @file
 * Form Submission Loader.
 */
((Drupal, drupalSettings) => {
    'use strict';
    /**
     * @type {Drupal}
     */
    const { behaviors } = Drupal;
    /**
     * Process form submission loader
     * @param Event e - The form submission event parameter
     * @returns {void}
     */
    behaviors.formSubmissionLoader = {
        attach(context, settings) {
            const btn = document.querySelector('.webform-button--submit');
            btn?.addEventListener('click', () => {
                const loader = document.querySelector('.loader');
                // Replace d-none with d-block class
                loader?.classList.replace('d-none', 'd-inline-block');
            });
        }
    };
})(Drupal, drupalSettings);
