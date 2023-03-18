/**
 * @file
 * Global utilities.
 *
 */
// (function($, Drupal) {

//   'use strict';

//   Drupal.behaviors.abrsd = {
//     attach: function(context, settings) {
//         document.addEventListener('DOMContentLoaded', function() {
//   };

// })(jQuery, Drupal);

((Drupal, drupalSettings) => {
  const { behaviors } = Drupal;
  const processClick = (e) => {
    if (e.target.tagName === 'A' && e.target.closest('li').classList.contains('nav-target')) {
      const href = e.target.getAttribute('href');
      if (href.split('#').length > 1) {
        e.preventDefault();
        window.sessionStorage.setItem('scrollTo', href.split('#')[1]);
        const url = new URL(window.location.href);
        // If the current page is the same as the link, scroll to the anchor,
        // else, redirect to the link.
        if(url.pathname === href.split('#')[0]) {
          const target = document.querySelector('#' + href.split('#')[1]);
          target?.scrollIntoView({ behavior: 'auto', block: 'center' });
        } else {
          window.location.href = href.split('#')[0];
        }
      }
    }
  }
  // Drupal.behaviors.abrsd = {
  behaviors.abrsd = {
    attach: function (context, settings) {
      /**
       * Scroll to anchor
       */
      const scrollTo = window.sessionStorage.getItem('scrollTo');
      if (scrollTo) {
        window.sessionStorage.removeItem('scrollTo');

        const target = document.querySelector('#' + scrollTo);
        target?.scrollIntoView({ behavior: 'auto', block: 'center' });
      }

      /**
       * Scroll to anchor on click
       * @type {HTMLElement}
       */
      const headerNav = document.querySelector('.navbar-nav');
      const footerNav = document.querySelector('.footer--onecol');
      headerNav?.addEventListener('click', processClick);
      footerNav?.addEventListener('click', processClick);
    },
  };
})(Drupal, drupalSettings);