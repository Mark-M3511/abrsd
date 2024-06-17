/**
 * @file
 * A JavaScript file for the abrsd_user_docs module.
 * This file is responsible for loading the content of a node in a modal.
 * The modal is displayed when a link with the class 'open-modal
 * is clicked.
 */
(function (Drupal, settings) {
    const { behaviors } = Drupal;
    behaviors.abrsdUserDocs = {
        scrollThreshold: 5,
        docsModal: document.querySelector('#docsModal'),
        attach: function (context, settings) {
            // Ensure the code runs only once per element
            behaviors.abrsdUserDocs.init();
            // Disable the modal buttons
            behaviors.abrsdUserDocs.disableModalButtons();
            // Add event listener for when the modal is closed
            behaviors.abrsdUserDocs.addModalCloseListener();
            // Add event listener for when the content div is scrolled
            behaviors.abrsdUserDocs.addScrollListener();
            // Add a click event to the modal form and check which button dimissed the modal
            behaviors.abrsdUserDocs.addModalClickEvent();
        },
        init: function () {
            document.querySelectorAll('.open-modal:not(.loadNodeInModal-processed)').forEach(function (el) {
                el.classList.add('loadNodeInModal-processed'); // Mark element as processed
                el.addEventListener('click', function (e) {
                    e.preventDefault();
                    const nodeUrl = this.getAttribute('href');
                    behaviors.abrsdUserDocs.showLoadingMessage(true, nodeUrl);
                    // Fetch the content from the URL
                    fetch(nodeUrl)
                        .then(response => {
                            if (!response.ok) {
                                throw new Error('There was an error loading the URL: ' + response.statusText);
                            }
                            return response.text();
                        })
                        .then(html => {
                            const newElement = document.createElement('div');
                            // Assign the fetched HTML to the new element
                            newElement.innerHTML = html;
                            const docContent = newElement.querySelector('.field--name-body').innerHTML
                            const docTitle = newElement.querySelector('.field--name-body h2').innerText;
                            // Find the modal body and set its content
                            const modal = document.querySelector('#docsModal');
                            const modalBody = modal.querySelector('.modal-body');
                            const modalTitle = modal.querySelector('.modal-title');

                            if (modalBody && docContent) {
                                modalTitle.innerHTML = `You are required to read and acknowledge the ${docTitle}`;
                                modalBody.innerHTML = docContent;
                                // Add a data attribute with the path of the node
                                modalBody.setAttribute('data-node-url', nodeUrl);
                                // Show the modal, assuming Bootstrap's modal is initialized in JavaScript
                                const modal = new bootstrap.Modal(document.querySelector('#docsModal'),
                                    {
                                        keyboard: false,
                                        backdrop: 'static'
                                    });
                                modal.show();
                            }
                        })
                        .catch(error => console.error('Error loading the URL: ', error));
                });
            });
        },
        showLoadingMessage: function (hide, nodeUrl) {
            let span = null;
            if (nodeUrl.includes('code-conduct')) {
                span = document.querySelector('#edit-code-of-conduct--description .loading-msg');
            } else {
                span = document.querySelector('#edit-terms-of-use--description .loading-msg');
            }
            hide ? span.classList.remove('d-none') : span.classList.add('d-none');
        },
        disableModalButtons: function () {
            // Get the modal element
            const docsModal = behaviors.abrsdUserDocs.docsModal;
            // Add event listener for when the modal is shown
            docsModal.addEventListener('show.bs.modal', function () {
                let btn = docsModal.querySelector('.btn-accept');
                btn.setAttribute('disabled', 'disabled');
                btn = docsModal.querySelector('.btn-reject');
                btn.setAttribute('disabled', 'disabled');
            });
        },
        addModalCloseListener: function () {
            // Get the modal element
            const docsModal = behaviors.abrsdUserDocs.docsModal;
            // Add event listener for when the modal is closed
            docsModal.addEventListener('hidden.bs.modal', function (event) {
                // Code to execute after the modal is closed
                console.log('Modal has been closed');
                // Get the data-node-url attribute of the modal-body element
                const nodeUrl = docsModal.querySelector('.modal-body').getAttribute('data-node-url');
                // Get the input element with the same attribute value
                const inputElement = document.querySelector(`input[data-node-url="${nodeUrl}"]`);
                // Get the user decision from local storage
                const decision = localStorage.getItem('userDecision');
                // Check if the input element exists
                if (inputElement) {
                    // Set the input element as checked
                    inputElement.checked = (decision === 'accept');
                    // Enable the element by negating and assigning the value
                    // of the checked property
                    inputElement.disabled = !inputElement.checked;
                    // Dispatch a change event
                    const event = new Event('change', {
                        'bubbles': true,
                        'cancelable': true,
                    });
                    inputElement.dispatchEvent(event);
                }
                // Clear the local storage
                localStorage.removeItem('userDecision');
                behaviors.abrsdUserDocs.showLoadingMessage(false, nodeUrl);
            });
        },
        addScrollListener: function () {
            // Get the modal element
            const docsModal = behaviors.abrsdUserDocs.docsModal;
            // Add event listener for when the content div is scrolled
            docsModal.querySelector('.modal-body').addEventListener('scroll', function () {
                const container = this;
                const scrollableHeight = container.scrollHeight;
                const containerHeight = container.clientHeight;
                const currentScrollPosition = container.scrollTop;

                // Check if the user has scrolled to the bottom
                if (currentScrollPosition + containerHeight >= scrollableHeight - behaviors.abrsdUserDocs.scrollThreshold) { // 5 is a small threshold
                    docsModal.querySelector('.btn-accept').disabled = false;
                    docsModal.querySelector('.btn-reject').disabled = false;
                }
            });
        },
        addModalClickEvent: function () {
            // Get the modal element
            const docsModal = behaviors.abrsdUserDocs.docsModal;
            // Add a click event to the modal form and check which button dimissed the modal
            docsModal.querySelector('.modal-footer').addEventListener('click', function (e) {
                let decision = null;
                if (e.target.classList.contains('btn-accept')) {
                    decision = 'accept';
                } else {
                    decision = 'reject';
                }
                // Save the response to local storage
                localStorage.setItem('userDecision', decision);
                console.log(decision);
            });
        }
    };
})(Drupal, drupalSettings);