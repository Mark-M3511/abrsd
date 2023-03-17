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

(function (Drupal) {
  Drupal.behaviors.abrsd = {
    attach: function (context, settings) {
      // Your code here.
      const scrollTo = window.sessionStorage.getItem('scrollTo');
      if (scrollTo) {
        window.sessionStorage.removeItem('scrollTo');
        const target = document.querySelector('#' + scrollTo);
        if (target) {
          target.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }
      }

      let el = document.querySelector('.navbar-nav');
      el.addEventListener('click', function (e) {
        e.preventDefault();
        if (e.target.tagName === 'A' && e.target.closest('li').classList.contains('dropdown-item')) {
          const href = e.target.getAttribute('href');
          // console.log(href.split('#')[1]);
          window.sessionStorage.setItem('scrollTo', href.split('#')[1]);
          window.location.href = href.split('#')[0];
        }
      });

    },
  };
}(Drupal));