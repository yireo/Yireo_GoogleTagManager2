export {DataLayer} from './data-layer';
export type {DataLayerEntry} from './data-layer';
export {DataLayerEvent, DataLayerEventAssumption, matchesPartial} from './data-layer-event';
export type {EventMatcher} from './data-layer-event';
export {
    test,
    expect,
    configureGtm,
    configureHyvaGtm,
    reloadCustomerSections,
    addProductToCart,
    getProductUrlFromCart,
    GTM_ID,
    HYVA_THEME,
    defaultConfig,
} from './gtm';
export type {ConfigurePayload} from './gtm';
