import '/themes/custom/abrsd/js/cookieconsent.umd.js';

/**
 * All config. options available here:
 * https://cookieconsent.orestbida.com/reference/configuration-reference.html
 */
const abrsdCC = window._abrsd_.cookieConsent || {};

CookieConsent.run({
    categories: {
        necessary: {
            enabled: true,  // this category is enabled by default
            readOnly: true  // this category cannot be disabled
        },
        analytics: {}
    },
    language: {
        default: 'en',
        translations: {
            en: {
                consentModal: {
                    title: abrsdCC.consent_title,
                    description: decodeEntities(abrsdCC.consent_description),
                    acceptAllBtn: abrsdCC.accept_all_button,
                    acceptNecessaryBtn: abrsdCC.accept_necessary_button,
                    showPreferencesBtn: abrsdCC.show_preferences_button
                },
                preferencesModal: {
                    title:  abrsdCC.pref_window_title,
                    acceptAllBtn:abrsdCC.pref_accept_button,
                    acceptNecessaryBtn:abrsdCC.pref_necessary_button,
                    savePreferencesBtn:abrsdCC.pref_save_current_button,
                    closeIconLabel: 'Close modal',
                    sections: [
                        {
                            title: 'Somebody said ... cookies?',
                            description: 'I want one!'
                        },
                        {
                            title: 'Strictly Necessary cookies',
                            description: 'These cookies are essential for the proper functioning of the website and cannot be disabled.',

                            //this field will generate a toggle linked to the 'necessary' category
                            linkedCategory: 'necessary'
                        },
                        {
                            title: 'Performance and Analytics',
                            description: 'These cookies collect information about how you use our website. All of the data is anonymized and cannot be used to identify you.',
                            linkedCategory: 'analytics'
                        },
                        {
                            title: 'More information',
                            description: 'For any queries in relation to my policy on cookies and your choices, please <a href="#contact-page">contact us</a>'
                        }
                    ]
                }
            }
        }
    },
});

function decodeEntities(encodedString) {
    // Create a temporary DOM element
    const tempElement = document.createElement('div');

    debugger;

    // Set the innerHTML of the temp element to the encoded string
    tempElement.innerHTML = encodedString;

    // Use textContent to extract the decoded content (since innerHTML might parse it back)
    // return tempElement.textContent || tempElement.innerText || "";
    return tempElement.innerHTML;
}