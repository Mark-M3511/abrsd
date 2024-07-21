(function (Drupal, settings) {
    // Define the behaviors object if it's not already defined
    const { behaviors } = Drupal;
    behaviors.abrsdUserInfo = {
        attach: function (context, settings) {
            const comment = context.querySelectorAll('[data-source-id]');
            // Mouseover event listener
            comment?.forEach(function (el) {
                el.addEventListener('mouseover', function (e) {
                    const target = el.querySelector('a');
                    if (!target?.hasAttribute('showing-popover')) {
                        if (target) {
                            const userId = target.closest('[data-source-id]')?.getAttribute('data-source-id');
                            if (userId) {
                                const { apiUser, apiToken } = settings.abrsd_user_registration;
                                behaviors.abrsdUserInfo.getUserDataFromAPI(apiUser, apiToken, userId, target);
                            }
                        }
                        target.setAttribute('showing-popover', '');
                    }
                });
            });
            // Mouseout event listener
            comment?.forEach(function (el) {
                el.addEventListener('mouseout', function (e) {
                    const target = el.querySelector('a');
                    if (target?.hasAttribute('showing-popover')) {
                        const popover = bootstrap.Popover.getOrCreateInstance(target);
                        popover?.hide();
                        target.removeAttribute('showing-popover');
                    }
                });
            });
            // comment?.addEventListener('click', function (e) {
            //     const target = e.target.closest('[data-source-id]');
            //     if (target) {
            //         e.preventDefault();
            //     }
            // });
        },
        getUserDataFromAPI: function (apiUser, apiToken, userId, thisEl) {
            // Define the user ID and API endpoint URL
            const apiUrl = `/jsonapi/user/user/${userId}`;
            // Basic authentication credentials
            const authHeader = 'Basic ' + btoa(`${apiUser}:${apiToken}`);
            // Fetch user data from the Drupal JSON API
            fetch(apiUrl, {
                method: 'GET',
                headers: {
                    'Authorization': authHeader,
                    'Accept': 'application/vnd.api+json'
                }
            }).then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok ' + response.statusText);
                }
                return response.json();
            }).then(responseData => {
                const { field_display_name, created, field_about_me } = responseData.data.attributes;
                // Get only the first 255 characters of the bio
                const bio = (field_about_me && field_about_me.length > 255) ? field_about_me.substring(0, 255) + '...' : 'Bio not available';
                const message = `<strong>Member since:</strong> ${behaviors.abrsdUserInfo.formatDate(created)}` +
                    `\n\n<strong>About Me:</strong> ${bio}`;
                console.log(message);
                const popover = bootstrap.Popover.getOrCreateInstance(thisEl, {
                    animate: true,
                    placement: 'top',
                    html: true,
                    content: message,
                    title: field_display_name,
                });
                popover.show();
            }).catch(error => {
                console.error('There was a problem with the fetch operation:', error);
            });
        },
        formatDate: function formatDate(dateString) {
            const date = new Date(dateString);
            const options = { year: 'numeric', month: 'long', day: 'numeric' };
            return date.toLocaleDateString('en-US', options);
        }
    };

})(Drupal, drupalSettings);