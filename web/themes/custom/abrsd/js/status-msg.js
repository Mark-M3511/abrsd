/**
 * @file
 * Global utilities.
 *
 */

((Drupal, drupalSettings) => {
    'use strict';
    /**
     * @type {Drupal}
     */
    const { behaviors } = Drupal;
    // Attach the behavior.
    behaviors.statusMsg = {
        attach: function (context, drupalSettings) {
            const statusMsg = document.querySelector('#submissionComplete');
            if (statusMsg) {
                // Get the home button from the modal.
                const btnHome = statusMsg.querySelector('[data-bs-route="home"]');
                btnHome?.addEventListener('click', function (event) {
                    window.location.href = drupalSettings.path.baseUrl;
                });
                // Display the modal after 500ms.
                setTimeout(function () {
                    if (statusMsg) {
                        const submitMsg = document.querySelector('#submissionComplete');
                        const modal = bootstrap.Modal.getOrCreateInstance(submitMsg);
                        modal.show();
                    }
                }, 500);
            }
        }
    };

})(Drupal, drupalSettings);