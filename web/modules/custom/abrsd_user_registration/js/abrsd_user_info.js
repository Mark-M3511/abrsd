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
                                behaviors.abrsdUserInfo.getUserDataFromAPI(
                                    settings.abrsd_user_registration,
                                    userId,
                                    target
                                );
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
            // Click event listener
            const commentMeta = context.querySelectorAll('.comment__meta');
            commentMeta?.forEach(function (el) {
                el.addEventListener('click', function (e) {
                    e.preventDefault();
                });
            });
        },
        getUserDataFromAPI: function (apiParams, userId, thisEl) {
            const { apiUser, apiToken, apiBaseUrl } = apiParams;
            // Define the user ID and API endpoint URL
            const apiUrl = `${apiBaseUrl}/${userId}`;
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
                    template: `
        <div class="popover user-info-popover" role="tooltip">
            <div class="popover-arrow"></div>
            <h3 class="popover-header"></h3>
            <div class="popover-body"></div>
        </div>
    `
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