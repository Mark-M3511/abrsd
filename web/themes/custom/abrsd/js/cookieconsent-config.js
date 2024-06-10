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
                    description: abrsdCC.consent_description,
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
                            title: abrsdCC.pref_intro_title,
                            description: abrsdCC.pref_intro_description
                        },
                        {
                            title: abrsdCC.pref_strict_title,
                            description: abrsdCC.pref_strict_description,
                            //this field will generate a toggle linked to the 'necessary' category
                            linkedCategory: 'necessary'
                        },
                        {
                            title: abrsdCC.pref_analytics_title,
                            description: abrsdCC.pref_analytics_description,
                            linkedCategory: 'analytics'
                        },
                        {
                            title: abrsdCC.pref_more_info_title,
                            description: abrsdCC.pref_more_info_description,
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