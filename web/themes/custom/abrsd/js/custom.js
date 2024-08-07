/**
 * @file
 * Global utilities.
 *
 */

((Drupal, drupalSettings) => {
  'use strict';
  /**
   * @type {Drupal}
   */
  const { behaviors } = Drupal;
  /**
   * Process click event on header and footer nav links
   * @param Event e - The click event parameter
   * @returns {void}
   */
  const processNavClick = (e) => {
    if (e.target.tagName === 'A' && e.target.closest('li').classList.contains('nav-target')) {
      const href = e.target.getAttribute('href');
      // Check if the browser supports matchMedia
      const { mobile, tablet } = mediaQueries();

      if (href.split('#').length > 1) {
        e.preventDefault();
        window.sessionStorage.setItem('scrollTo', href.split('#')[1]);
        const url = new URL(window.location.href);
        // If the current page is the same as the link, scroll to the anchor,
        // else, redirect to the link.
        if (url.pathname === href.split('#')[0]) {
          const target = document.querySelector(`#${href.split('#')[1]}`);

          if (mobile || tablet) {
            // window.location.href = href.split('#')[0] + '#main-content';
            window.location.href = href.split('#')[0];
          } else {
            target?.scrollIntoView({ behavior: 'auto', block: 'center' });
          }
        } else {
          window.location.href = href.split('#')[0];
        }
      }
    }
  }

  /**
   * Process click event on list group links
   * @param Event e - The click event parameter
   * @returns {void}
   */
  const processNewsItemClick = (e) => {
    const { classList, dataset } = e.target;
    const href = dataset.href || e.target.closest('div.news-topic-card').dataset.href;
    // If the click target is a badge or card body, redirect to the link.
    if (classList.contains('badge') || classList.contains('card-body')) {
      e.preventDefault();
      // Remove the leading slash from the href if it exists
      window.location.href = '/' + href.replace(/^\//, '');
    }
  }

  /**
   * Check if the browser supports matchMedia
   */
  const mediaQueries = () => {
    const matchMediaQuery = (window.matchMedia || window.msMatchMedia);

    return {
      mobile: matchMediaQuery('(max-width: 47.99875rem)').matches,
      tablet: matchMediaQuery('(min-width: 48rem) and (max-width: 63.99875rem)').matches,
    };
  }

  /**
   * Handle contact form submission
   * @param Event e - The submit event parameter
   * @returns {void}
   */
  const processContactFormSubmit = (e) => {
    const submissionStart = document.querySelector('#submissionStart');
    const modal = bootstrap.Modal.getOrCreateInstance(submissionStart);
    // Implement an 'shown.bs.modal' event listener
    submissionStart.addEventListener('shown.bs.modal', (el) => {
      // Get the paragraph element with the class modal-title
      const modalTitle = el.target.querySelector('p.modal-title');
      // Set the text content of the modal-title element to 'Sending yur message...'
      modalTitle.textContent = 'Sending your message...';
    });
    modal?.show();
  }

  /**
   * Attach behaviors to the document.
   * Note: This is normally written as Drupal.behaviors.abrsd = {
   */
  behaviors.abrsd = {
    attach: function (context, settings) {
      /**
       * Scroll to anchor
       */
      const scrollTo = window.sessionStorage.getItem('scrollTo');

      if (scrollTo) {
        const target = (context.querySelector(`#${scrollTo}`) || document.querySelector(`#${scrollTo}`));
        const { mobile, tablet } = mediaQueries();
        const blockPos = (mobile || tablet) ? 'start' : 'center';

        window.sessionStorage.removeItem('scrollTo');
        target?.scrollIntoView({
          behavior: 'auto',
          block: blockPos
        });
      }

      // Set the classes of the elements to be selected for list gorup elements
      const listGroupClasses = '.news-list, .forum-list';

      const headerNav = (context.querySelector('.navbar-nav') || document.querySelector('.navbar-nav'));
      const footerNav = (context.querySelector('.footer--onecol') || document.querySelector('.footer--onecol'));
      const listGroup = (context.querySelector(listGroupClasses) || document.querySelector(listGroupClasses));
      const contactForm = (context.querySelector('.webform-submission-contact-add-form') || document.querySelector('.webform-submission-contact-add-form'));
      /**
       * Set up click event listeners for header and footer nav links
       */
      headerNav?.addEventListener('click', processNavClick);
      footerNav?.addEventListener('click', processNavClick);
      listGroup?.addEventListener('click', processNewsItemClick);
      contactForm?.addEventListener('submit', processContactFormSubmit);
    }
  }
})(Drupal, drupalSettings);