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
    e.preventDefault();
    if (e.target.tagName === 'A' && e.target.closest('li').classList.contains('nav-target')) {
      const href = e.target.getAttribute('href');
      if (href.split('#').length > 1) {
        window.sessionStorage.setItem('scrollTo', href.split('#')[1]);
        window.location.href = href.split('#')[0];
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
      const el = document.querySelector('.navbar-nav');
      const el2 = document.querySelector('.footer--onecol');
      el?.addEventListener('click', processClick);
      el2?.addEventListener('click', processClick);
    },
  };
})(Drupal, drupalSettings);