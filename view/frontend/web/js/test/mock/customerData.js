define([], function() {
    return {
        'get': function(sectionId) {
            return require('./mock/customerData/' + sectionId);
        }
    };
});