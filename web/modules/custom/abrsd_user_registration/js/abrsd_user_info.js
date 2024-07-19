(function (Drupal, settings) {
    const { behaviors } = Drupal;
    behaviors.abrsdUserInfo = {
        attach: function (context, settings) {
            console.log('abrsdUserInfo');
            const comment = context.querySelector('.field--name-field-blog-comment');
            comment?.addEventListener('click', function (e) {
                e.preventDefault();
                const target = e.target.closest('[data-source-id]');
                if (target) {
                    const userId = target.getAttribute('data-source-id');
                    if (userId) {
                        behaviors.abrsdUserInfo.getUserDataFromAPI(userId, target);
                    } else {
                        console.error('User profile not found');
                    }
                }
            });

        },
        getUserDataFromAPI: function (userId, thisEl) {
            // Define the user ID and API endpoint URL
            const apiUrl = `/jsonapi/user/user/${userId}`;

            // Basic authentication credentials
            const username = '3f1d9b2e-4e2f-4a2b-9e8e-60a3a7e1b3c6';
            const password = 'u7Wg7k2qsmsqRwX7rKNlDgtevmKO3iO4wAYdQOFz4929756KJBATe3p95bP0xWwZ';
            const authHeader = 'Basic ' + btoa(`${username}:${password}`);

            // Fetch user data from the Drupal JSON API
            fetch(apiUrl, {
                method: 'GET',
                headers: {
                    'Authorization': authHeader,
                    'Accept': 'application/vnd.api+json'
                }
            })
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Network response was not ok ' + response.statusText);
                    }
                    return response.json();
                })
                .then(responseData => {
                    // console.log('User data:', data);
                    const { field_display_name, created, field_about_me } = responseData.data.attributes;
                    const message = `<strong>Member since:</strong> ${behaviors.abrsdUserInfo.formatDate(created)}` +
                        `\n\n<strong>About Me:</strong> ${field_about_me ?? 'Bio not available'}`;
                    const popover = bootstrap.Popover.getOrCreateInstance(thisEl, {
                        trigger: 'hover focus',
                        placement: 'top',
                        html: true,
                        content: message,
                        title: field_display_name,
                    });
                    popover.show();
                    // console.log(message);
                })
                .catch(error => {
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