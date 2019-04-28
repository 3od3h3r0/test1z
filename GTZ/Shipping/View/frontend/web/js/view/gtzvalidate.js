define(
    [
        'uiComponent',
        'Magento_Checkout/js/model/shipping-rates-validator',
        'Magento_Checkout/js/model/shipping-rates-validation-rules',
        '../model/shipping-rates-validator',
        '../model/shipping-rates-validation-rules'
    ],
    function (
        Component,
        defaultShippingRatesValidator,
        defaultShippingRatesValidationRules,
        shippingRatesValidator,
        shippingRatesValidationRules
    ) {
        'use strict';
 
        defaultShippingRatesValidator.registerValidator('GTZ_Shipping', shippingRatesValidator);
        defaultShippingRatesValidationRules.registerRules('GTZ_Shipping', shippingRatesValidationRules);
        return Component;
    }
);

// defaultShippingRatesValidator.registerValidator('simpleshipping', shippingRatesValidator);
// defaultShippingRatesValidationRules.registerRules('simpleshipping', shippingRatesValidationRules); 