import '/themes/custom/abrsd/js/cookieconsent.umd.js';

/**
 * All config. options available here:
 * https://cookieconsent.orestbida.com/reference/configuration-reference.html
 * The  code is a simple example of how to configure the Cookie Consent module.
 * The configuration is done in a separate JavaScript file that is included in the theme.
 * The configuration file is included in the theme’s  .libraries.yml  file:
 * web/themes/custom/abrsd/abrsd.libraries.yml
 */
const abrsdCC = window._abrsd_.cookieConsent || {};

CookieConsent.run({
    guiOptions: {
        consentModal: {
            layout: "box wide",
            position: "bottom left",
            equalWeightButtons: true,
            flipButtons: false
        },
        preferencesModal: {
            layout: "box",
            position: "right",
            equalWeightButtons: true,
            flipButtons: false
        }
    },
    categories: {
        necessary: {
            enabled: true,  // this category is enabled by default
            readOnly: true  // this category cannot be disabled
        },
        analytics: {
            enabled: true, // this service is enabled by default
            services: {
                ga: {
                    label: 'Google Analytics',
                    onAccept: () => {
                        console.log('Enable GA');
                    },
                    onReject: () => {
                        // disable ga
                        console.log('Disable GA');
                    }
                }
            }
        }
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
                    showPreferencesBtn: abrsdCC.show_preferences_button,
                    footer: "<a href=\"#link\">Privacy Policy</a>\n<a href=\"#link\">Terms and Conditions</a>"
                },
                preferencesModal: {
                    title: abrsdCC.pref_window_title,
                    acceptAllBtn: abrsdCC.pref_accept_button,
                    acceptNecessaryBtn: abrsdCC.pref_necessary_button,
                    savePreferencesBtn: abrsdCC.pref_save_current_button,
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
    onConsent: ({cookie}) => {
        console.log('GA change');
        console.log(cookie);
        console.log(CookieConsent.acceptedCategory('analytics'));
        console.log(CookieConsent.acceptedService('ga', 'analytics'));
        // Enable or disable GA based on the user's choice
        const ga = !!CookieConsent.acceptedService('ga', 'analytics');
        window[`ga-disable-${abrsdCC.ga_id}`] = !ga;
        console.log('On Window Object: ' + window[`ga-disable-${abrsdCC.ga_id}`])
    }
});
