define(['jquery'], function($){
    var tracking = {};
    tracking.sendEvent = function(event, eventCategory, eventAction, eventLabel)
    {
        if (typeof dataLayer !== 'undefined' && $.isArray(dataLayer)) {
            dataLayer.push({
                'event': event,
                'eventCategory': eventCategory,
                'eventAction': eventAction,
                'eventLabel': eventLabel
            });
        }
    };
    return tracking;
});