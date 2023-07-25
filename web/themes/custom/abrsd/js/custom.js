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
   * @param Event e - The click event paramter
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
   * @param Event e - The click event paramter
   * @returns {void}
   */
  const processNewsItemClick = (e) => {
    if (e.target.classList.contains('badge') || e.target.classList.contains('card-body')) {
      e.preventDefault();
      const href = e.target.dataset.href ? e.target.dataset.href : e.target.closest('div.news-topic-card').dataset.href;
      // Navigate to the link
      window.location.href = '/' + href;
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
   * @param Event e - The submit event paramter
   * @returns {void}
   */
  const processContactFormSubmitBtnClick = (e) => {
    const submissionStart = document.querySelector('#submissionStart');
    const modal = bootstrap.Modal.getOrCreateInstance(submissionStart);
    e.preventDefault();
    modal.show();
  }

  const processModalShow = (e) => {
    const contactForm = document.querySelector('.webform-submission-contact-add-form');
    const modal = bootstrap.Modal.getInstance(e.target);
    const btnSubmit = document.querySelector('#edit-actions-submit');
    // Just pause for 2 seconds
    setTimeout(() => {
      // Show time in console
      // console.log('3 seconds passed');
      // Disable the submit button
      btnSubmit.setAttribute('disabled', 'disabled');
      // Hide the modal
      modal.hide();
    }, 3000);
  }

  const processModalHide = (e) => {
    const contactForm = document.querySelector('.webform-submission-contact-add-form');
    const modal = bootstrap.Modal.getInstance(e.target);
    const title = document.querySelector('.webform-title');
    // Set title to 'Submitting...'
    title.innerHTML = 'Sending emails...just a minute...';
    // Submit the form
    contactForm.submit();
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

      const headerNav = (context.querySelector('.navbar-nav') || document.querySelector('.navbar-nav'));
      const footerNav = (context.querySelector('.footer--onecol') || document.querySelector('.footer--onecol'));
      const listGroup = (context.querySelector('.news-list') || document.querySelector('.news-list'));
      const contactForm = (context.querySelector('.webform-submission-contact-add-form') || document.querySelector('.webform-submission-contact-add-form'));
      const formSubmitBtn = (context.querySelector('#edit-actions-submit') || document.querySelector('#edit-actions-submit'));
      const modal = (context.querySelector('#submissionStart') || document.querySelector('#submissionStart'));
      /**
       * Set up click event listeners for header and footer nav links
       */
      headerNav?.addEventListener('click', processNavClick);
      footerNav?.addEventListener('click', processNavClick);
      listGroup?.addEventListener('click', processNewsItemClick);
      formSubmitBtn?.addEventListener('click', processContactFormSubmitBtnClick);
      modal?.addEventListener('shown.bs.modal', processModalShow);
      modal?.addEventListener('hidden.bs.modal', processModalHide);
    },
  };
})(Drupal, drupalSettings);