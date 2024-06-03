define([], function () {
    return function (...args) {
        const debug = window.Tagging_GTM_DEBUG || false;
        if (false === debug) {
            return;
        }

        var color = 'gray';
        if (args[0].toLowerCase().startsWith('push')) {
            color = 'green';
        }

        if (args[0].toLowerCase().startsWith('warning')) {
            color = 'orange';
        }

        var css = 'color:white; background-color:' + color + '; padding:1px;'
        console.log('%cTagging_GTM', css, ...args);
    };
});
