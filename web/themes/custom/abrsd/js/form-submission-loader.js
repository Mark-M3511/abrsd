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
            const regForm = document.querySelector('.webform-submission-user-registration-form');
            btn?.addEventListener('click', () => {
                const loader = document.querySelector('.loader');
                // Replace d-none with d-block class
                loader?.classList.replace('d-none', 'd-inline-block');
            });
            if (regForm) {
                const formMessage = document.querySelector('.form-message');
                //  Get the serverTime value from the drupalSettings object
                const serverTime = drupalSettings.abrsd_user_registration.serverTime;
                const browserTime = new Date();
                const options = {
                    year: 'numeric',
                    month: 'long',
                    day: 'numeric',
                    hour: '2-digit',
                    minute: '2-digit'
                };
                const formattedDate = browserTime.toLocaleString('en-US', options);
                // Set the text of the span element
                formMessage.textContent = `Registration form created on: ${formattedDate}`;
            }
        }
    };
})(Drupal, drupalSettings);
