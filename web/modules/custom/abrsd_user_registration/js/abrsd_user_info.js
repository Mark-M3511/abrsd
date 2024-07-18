(function (Drupal, settings) {
    const { behaviors } = Drupal;
    behaviors.abrsdUserInfo = {
        attach: function (context, settings) {
            console.log('abrsdUserInfo');
            // #comment-13 > div.comment__meta.d-none.d-lg-block.col-sm-2 > div > article > div > a > img
            // const img = document.querySelector('.field--name-user-picture img');
            const comment = document.querySelector('.field--name-field-blog-comment');
            comment?.addEventListener('click', function (e) {
                e.preventDefault();
                const target = e.target.classList.contains('image-style-thumbnail')
                    || e.target.classList.contains('user-initials')
                    || e.target.classList.contains('user-initial');
                // If the click target is a profile image or user name, get the user data
                if (target) {
                    const userId = e.target.getAttribute('data-source-id')
                        || e.target.closest('[data-source-id]')?.getAttribute('data-source-id');
                    if (userId) {
                        behaviors.abrsdUserInfo.getUserDataFromAPI(userId);
                    } else {
                        console.error('User profile not found');
                    }
                }
            });
        },
        getUserDataFromAPI: function (userId) {
            // Define the user ID and API endpoint URL
            // const userId = 'e87fd47d-ccb8-4237-b2bd-8bf36be678e0'; // Replace with the actual user ID
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
                    const message = `Name: ${field_display_name}\nMember since: ${behaviors.abrsdUserInfo.formatDate(created)}` +
                        `\nAbout Me: ${field_about_me ?? 'N/A'}`;
                    console.log(message);
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