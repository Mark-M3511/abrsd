(function (Drupal, settings) {
    Drupal.behaviors.abrsdUserRegistration = {
        attach: function (context, settings) {
            // Create a new MutationObserver instance.
            const observer = new MutationObserver(mutations => {
                const uploader = document.querySelector('input[name="files[profile_picture]"]');
                mutations.forEach(mutation => {
                    if (mutation.type === 'childList') {
                        if (uploader) {
                            // Hide the element with id attribute == #profile-picture-display
                            const profilePic = document.querySelector('#profile-picture-display');
                            if (profilePic) {
                                profilePic.style.display = 'none';
                            }
                        }
                    }
                });
            });
            // Start observing the document with the configured parameters.
            observer.observe(document, { attributes: true, childList: true, subtree: true });
        }
    };
})(Drupal, drupalSettings);