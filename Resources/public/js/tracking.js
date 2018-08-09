define(['jquery'], function($){
    var tracking = {};

    /**
     * Push data to GTM
     * @param pushData
     */
    tracking.push = function (pushData) {
        if (typeof dataLayer !== 'undefined' && $.isArray(dataLayer)) {
            dataLayer.push(pushData);
        }
    };

    /**
     * Push events to GTM with parameters
     * @param {String} event
     * @param {String} eventCategory
     * @param {String} eventAction
     * @param {String} eventLabel
     * @param {Object|undefined} additionalVariables
     */
    tracking.sendEvent = function(event, eventCategory, eventAction, eventLabel, additionalVariables)
    {
        var pushData = (typeof additionalVariables === "object") ? additionalVariables : {}

        pushData.event = event;
        pushData.eventCategory = eventCategory;
        pushData.eventAction = eventAction;
        pushData.eventLabel = eventLabel;

        tracking.push(pushData);
    };

    return tracking;
});