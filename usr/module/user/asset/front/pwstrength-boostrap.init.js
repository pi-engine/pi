function translateThisThing(key) {
    var translations = {
        "wordLength": wordLength,
        "wordNotEmail": wordNotEmail,
        "wordSimilarToUsername": wordSimilarToUsername,
        "wordTwoCharacterClasses": wordTwoCharacterClasses,
        "wordRepetitions": wordRepetitions,
        "wordSequences": wordSequences,
        "errorList": errorList,
        "veryWeak": veryWeak,
        "weak": weak,
        "normal": normal,
        "medium": medium,
        "strong": strong,
        "veryStrong": veryStrong
    };

    return translations[key];
};

function pwstrengthInit() {
    if ($('#password-change [name="credential"], [name="register"] #credential, [name="credential-new"]').not('.pwstrengthEnabled').not('#credential-verify').length > 0) {
        $('#password-change [name="credential"], [name="register"] #credential, [name="credential-new"]').not('.pwstrengthEnabled').not('#credential-verify').addClass('pwstrengthEnabled').pwstrength({
            common: {
                minChar: minChar
            },
            rules: {
                scores: {
                    wordNotEmail: -100,
                    wordLength: -50,
                    wordSimilarToUsername: -100,
                    wordSequences: -50,
                    wordTwoCharacterClasses: 2,
                    wordRepetitions: -25,
                    wordLowercase: -60,
                    wordUppercase: 20,
                    wordOneNumber: 20,
                    wordThreeNumbers: 30,
                    wordOneSpecialChar: 3,
                    wordTwoSpecialChar: 5,
                    wordUpperLowerCombo: 2,
                    wordLetterNumberCombo: 2,
                    wordLetterNumberCharCombo: 2
                }
            },
            i18n: {
                t: function (key) {
                    var result = translateThisThing(key); // Do your magic here

                    return result === key ? '' : result; // This assumes you return the
                    // key if no translation was found, adapt as necessary
                }
            }
        });
    }
}
$(window).on('load', function () {
    pwstrengthInit();
});
