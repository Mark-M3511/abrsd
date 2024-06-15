(function (Drupal, settings) {
    Drupal.behaviors.abrsdUserRegistration = {
        attach: function (context, settings) {
            document.addEventListener('DOMContentLoaded', function () {
                // Ensure the code runs only once per element
                document.querySelectorAll('.open-modal:not(.loadNodeInModal-processed)').forEach(function (element) {
                    element.classList.add('loadNodeInModal-processed'); // Mark element as processed
                    element.addEventListener('click', function (e) {
                        e.preventDefault();
                        const nodeUrl = this.getAttribute('href');
                        // Fetch the content from the URL
                        fetch(nodeUrl)
                            .then(response => response.text())
                            .then(html => {
                                // Find the modal body and set its content
                                // const modalBody = document.querySelector('#yourModal .modal-body');
                                // if (modalBody) {
                                //     modalBody.innerHTML = html;
                                //     // Show the modal, assuming Bootstrap's modal is initialized in JavaScript
                                //     var modal = new bootstrap.Modal(document.getElementById('yourModal'));
                                //     modal.show();
                                // }
                                const newElement = document.createElement('div');
                                // Assign the fetched HTML to the new element
                                newElement.innerHTML = html;
                                console.log(newElement.querySelector('.field--name-body').innerHTML);
                            })
                            .catch(error => console.error('Error loading the URL: ', error));
                    });
                });
            });
        }
    };
})(Drupal, drupalSettings);