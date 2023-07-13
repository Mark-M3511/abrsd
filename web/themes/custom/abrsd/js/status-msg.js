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
            const statusMsg = document.querySelector('#submissionMsg');
            if (statusMsg) {
                // Get the home button from the modal.
                const btnHome = document.querySelector('[data-bs-route="home"]');
                btnHome?.addEventListener('click', function (event) {
                    window.location.href = drupalSettings.path.baseUrl;
                });
                // Display the modal after 500ms.
                setTimeout(function () {
                    const btnStatusMsg = document.querySelector('[data-bs-target="#submissionMsg"]');
                    if (statusMsg) {
                        // Display the modal using the Bootstrap JavaScript API.
                        btnStatusMsg.click();
                    }
                }, 500);
            }
        }
    };

})(Drupal, drupalSettings);