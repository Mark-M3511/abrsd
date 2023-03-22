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
  const processClick = (e) => {
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
          const target = document.querySelector('#' + href.split('#')[1]);

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
        const target = document.querySelector('#' + scrollTo);
        const { mobile, tablet } = mediaQueries();
        const blockPos = (mobile || tablet) ? 'start' : 'center';

        window.sessionStorage.removeItem('scrollTo');
        target?.scrollIntoView({ behavior: 'auto', block: blockPos });
      }

      const headerNav = document.querySelector('.navbar-nav');
      const footerNav = document.querySelector('.footer--onecol');
      /**
       * Set up click event listeners for header and footer nav links
       */
      headerNav?.addEventListener('click', processClick);
      footerNav?.addEventListener('click', processClick);
    },
  };
})(Drupal, drupalSettings);