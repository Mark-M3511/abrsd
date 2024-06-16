(function (Drupal, settings) {
    Drupal.behaviors.abrsdUserDocs = {
        attach: function (context, settings) {
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
                            const newElement = document.createElement('div');
                            // Assign the fetched HTML to the new element
                            newElement.innerHTML = html;
                            const docContent = newElement.querySelector('.field--name-body').innerHTML
                            const docTitle = newElement.querySelector('.field--name-body h2').innerText;
                            // Find the modal body and set its content
                            const modalBody = document.querySelector('#docsModal .modal-body');
                            const modalTitle = document.querySelector('#docsModal .modal-title');
                            if (modalBody && docContent) {
                                modalTitle.innerHTML = `You are required to read and acknowledge the ${docTitle}`;
                                modalBody.innerHTML = docContent;
                                // Show the modal, assuming Bootstrap's modal is initialized in JavaScript
                                const modal = new bootstrap.Modal(document.querySelector('#docsModal'));
                                modal.show();
                            }
                            console.log(newElement.querySelector('.field--name-body').innerHTML);
                        })
                        .catch(error => console.error('Error loading the URL: ', error));
                });
            });

        }
    };
})(Drupal, drupalSettings);