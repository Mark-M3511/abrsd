(function (Drupal, settings) {
    Drupal.behaviors.abrsdUserInfo = {
        attach: function (context, settings) {
            console.log('abrsdUserInfo');
            // #comment-13 > div.comment__meta.d-none.d-lg-block.col-sm-2 > div > article > div > a > img
            const img = document.querySelector('.field--name-user-picture img');
            img?.addEventListener('click', function (e) {
                e.preventDefault();
                getUserDataFromAPI();
            });
        }
    };

    function getUserDataFromAPI() {
        // Define the user ID and API endpoint URL
        const userId = 'e87fd47d-ccb8-4237-b2bd-8bf36be678e0'; // Replace with the actual user ID
        const apiUrl = `/jsonapi/user/user/${userId}`;

        // Basic authentication credentials
        const username = 'bsaka@arsenal.com';
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
            .then(data => {
                // console.log('User data:', data);
                const user = data.data.attributes;
                const message = `Name: ${user.name}\nEmail: ${user.mail}\nCreated: ${user.created}`+
                    `\nAbout Me: ${user.field_about_me}`;
                // Destructure the attributes property to get the user data
                // const { name, mail, created } = data.data.attributes;
                console.log(message);
            })
            .catch(error => {
                console.error('There was a problem with the fetch operation:', error);
            });
    }
})(Drupal, drupalSettings);