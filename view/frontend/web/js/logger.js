define([], function () {
    return function (...args) {
        const debug = YIREO_GOOGLETAGMANAGER2_DEBUG || false;
        if (false === debug) {
            return;
        }

        var css = 'color:white; background-color:green; padding:1px;'
        console.log('%cYireo_GoogleTagManager2', css, ...args);
    };
});
