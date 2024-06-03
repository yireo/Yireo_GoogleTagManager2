define(["googleTagManagerLogger"], function (logger) {
  return function (eventData, message) {
    window.Tagging_GTM_PAST_EVENTS = window.Tagging_GTM_PAST_EVENTS || [];

    const metaData = Object.assign({}, eventData.meta);

    const cleanEventData = Object.assign({}, eventData);
    if (cleanEventData.meta) {
      delete cleanEventData.meta;
    }

    if (cleanEventData.length === 0) {
      return;
    }

    if (
      metaData &&
      metaData.allowed_pages &&
      metaData.allowed_pages.length > 0 &&
      false ===
        metaData.allowed_pages.some((page) =>
          window.location.pathname.includes(page)
        )
    ) {
      logger(
        "Warning: Skipping event, not in allowed pages",
        window.location.pathname,
        eventData
      );
      return;
    }

    // Prevent the same event from being triggered twice, when containing the same data
    const eventHash = btoa(encodeURIComponent(JSON.stringify(cleanEventData)));
    if (window.Tagging_GTM_PAST_EVENTS.includes(eventHash)) {
      logger("Warning: Event already triggered", eventData);
      return;
    }

    if (!message) {
      message = "push (unknown) [unknown]";
    }

    logger(message, eventData);
    window.dataLayer = window.dataLayer || [];
    window.dataLayer.push({ ecommerce: null });

    if (window.taggingHelpers) {
      cleanEventData.marketing = window.taggingHelpers.getMarketingObject();
      cleanEventData.device = window.taggingHelpers.getDeviceInfo();
    }

    if (cleanEventData.marketing) {
      const expires = new Date();
      expires.setTime(expires.getTime() + 7 * 24 * 60 * 60 * 1000);
      document.cookie = `trytagging_user_data=${btoa(
        JSON.stringify(cleanEventData.marketing)
      )};expires=${expires.toUTCString()};path=/`;
    }

    if (
      cleanEventData.event === "trytagging_begin_checkout" ||
      cleanEventData.event === "trytagging_view_cart" ||
      cleanEventData.event === "trytagging_add_to_cart"
    ) {
      // Post marketingObject to server
      const xhr = new XMLHttpRequest();
      xhr.open("POST", "/rest/V1/trytagging/save", true);
      xhr.setRequestHeader("Content-Type", "application/json");
      xhr.send(
        JSON.stringify({
          jsonData: cleanEventData.marketing || {},
        })
      );
    }

    window.dataLayer.push(cleanEventData);
    window.Tagging_GTM_PAST_EVENTS.push(eventHash);
  };
});
