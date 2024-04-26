(function (Drupal, settings) {
    Drupal.behaviors.abrsdUserRegistration = {
        attach: function (context, settings) {
            // Create a new MutationObserver instance.
            const observer = new MutationObserver(function (mutations) {
                mutations.forEach(function (mutation) {
                    if (mutation.type === 'childList') {
                        // Check for the presence of a input element with the name attribute = "files[profile_picture]"
                        if (document.querySelector('input[name="files[profile_picture]"]')) {
                            // Console log it
                            // console.log('The profile picture input element is present.');
                            // Hide the element with id attribute == #profile-picture-display
                            document.querySelector('#profile-picture-display').style.display = 'none';
                        }
                        // console.log('A child node has been added or removed.');
                    } else if (mutation.type === 'attributes') {
                        // console.log('The ' + mutation.attributeName + ' attribute was modified.');
                    }
                });
            });

            // Start observing the document with the configured parameters.
            observer.observe(document, { attributes: true, childList: true, subtree: true });

            // For example, to log a message to the console:
            console.log('abrsd_user_registration module JavaScript loaded.');
        }
    };
})(Drupal, drupalSettings);