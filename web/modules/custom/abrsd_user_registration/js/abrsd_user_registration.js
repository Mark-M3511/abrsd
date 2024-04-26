(function (Drupal, settings) {
    Drupal.behaviors.abrsdUserRegistration = {
        attach: function (context, settings) {
            // Create a new MutationObserver instance.
            const observer = new MutationObserver(function (mutations) {
                const elUploader = document.querySelector('input[name="files[profile_picture]"]');
                mutations.forEach(function (mutation) {
                    if (mutation.type === 'childList') {
                        if (elUploader) {
                            // Hide the element with id attribute == #profile-picture-display
                            const elDisplay = document.querySelector('#profile-picture-display');
                            if (elDisplay) {
                                elDisplay.style.display = 'none';
                            }
                        }
                    } else if (mutation.type === 'attributes') {
                        // console.log('The ' + mutation.attributeName + ' attribute was modified.');
                    }
                });
            });

            // Start observing the document with the configured parameters.
            observer.observe(document, { attributes: true, childList: true, subtree: true });

            // For example, to log a message to the console:
            // console.log('abrsd_user_registration module JavaScript loaded.');
        }
    };
})(Drupal, drupalSettings);