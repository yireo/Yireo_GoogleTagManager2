define([], function () {
    var customer = function () {
        return {
            'subscribe': function () {
            }
        };
    };

    customer.subscribe = function() {};

    return customer;
});