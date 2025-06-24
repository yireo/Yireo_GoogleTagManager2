# Webhook Reliability Improvements (v1.0.14)

## Probleem Beschrijving

Verschillende orders werden niet correct naar Google Analytics verzonden via webhooks. De analyse toonde de volgende problemen aan:

1. **Problematische early return logic** in `TriggerPurchaseWebhookEvent`
2. **Beperkte HTTP status code behandeling** (geen 425 "Too Early" support)
3. **Geen fallback mechanisme** voor gefaalde webhooks
4. **Float vergelijking zonder tolerantie** tussen `getGrandTotal()` en `getTotalPaid()`

## Doorgevoerde Verbeteringen

### 1. Verbeterde Trigger Logic (`Observer/TriggerPurchaseWebhookEvent.php`)

**Oud probleem:**
```php
if (!$order->dataHasChangedFor('total_paid') || $order->getGrandTotal() > $order->getTotalPaid()) {
    return;
}
```

**Nieuwe oplossing:**
- **Tolerantie voor float vergelijking** (€0.01 marge)
- **Meerdere fallback condities:**
  - Primary: `dataHasChangedFor('total_paid')` EN volledig betaald
  - Fallback 1: Order in `processing/complete` state EN volledig betaald
  - Fallback 2: Betaling compleet (ongeacht data changes)
- **Verbeterde logging** voor debugging
- **Tracking van gefaalde pogingen** voor retry mechanisme

### 2. Verbeterde HTTP Status Behandeling (`DataLayer/Event/PurchaseWebhookEvent.php`)

**Nieuwe features:**
- **425 "Too Early" support**: Markeert voor retry zonder definitief te falen
- **5xx server errors**: Automatische retry
- **4xx client errors**: Geen retry (voorkomt infinite loops)
- **Verbeterde error logging** met order context
- **Status code logging** voor debugging

### 3. Fallback Cron Job (`Cron/RetryFailedWebhooks.php`)

**Nieuwe features:**
- **Automatische retry** elke 2 uur
- **Intelligente selectie** van orders om te retries:
  - Niet succesvol verwerkt
  - Minder dan 3 pogingen gedaan
  - Laatste poging > 2 uur geleden
  - Order niet ouder dan 1 week
  - Volledig betaald
- **Tracking van retry attempts**
- **Configureerbare parameters** (max retries, retry interval, max age)

## Configuratie

### Cron Job Schedule
```xml
<!-- etc/crontab.xml -->
<schedule>0 */2 * * *</schedule> <!-- Elke 2 uur -->
```

### Parameters (aanpasbaar in code)
- **Max retries**: 3 pogingen
- **Retry interval**: 2 uur tussen pogingen  
- **Max order age**: 1 week
- **Float tolerance**: €0.01

## Monitoring

### Debug Logging
Alle webhook activiteiten worden gelogd via de `Debugger` class:
- Trigger conditions
- HTTP status codes
- Retry attempts
- Fallback activiteiten

### Payment Additional Information
De volgende data wordt opgeslagen in `sales_order_payment.additional_information`:
- `trytagging_webhook_processed`: boolean (success marker)
- `trytagging_webhook_failed_attempts`: int (retry counter)
- `trytagging_webhook_last_attempt`: timestamp (rate limiting)

## Testing

### Test Scenarios
1. **Happy path**: Order volledig betaald → webhook verzonden
2. **425 Too Early**: Webhook gefaald → retry via cron
3. **Float afrondingsverschil**: €84.94 vs €84.939999 → succesvol verzonden
4. **Mollie timing issue**: Order betaald maar `dataHasChangedFor` = false → fallback trigger
5. **Network error**: Webhook timeout → retry via cron

### Verificatie
```bash
# Check cron job registratie
bin/magento cron:run --group="default"

# Check debug logs
tail -f var/log/debug.log | grep "TriggerPurchaseWebhookEvent\|RetryFailedWebhooks"

# Check payment additional information
SELECT additional_information FROM sales_order_payment WHERE order_id = X;
```

## Deployment

1. **Update plugin** naar versie 1.0.14
2. **Run setup upgrade**: `bin/magento setup:upgrade`
3. **Clear cache**: `bin/magento cache:clean`
4. **Verify cron**: Check dat de cron job geregistreerd is

## Impact

Deze verbeteringen zouden de volgende problemen moeten oplossen:
- ✅ Orders die gemist werden door timing issues (425 Too Early)
- ✅ Orders die gemist werden door float afrondingsverschillen  
- ✅ Orders die gemist werden door `dataHasChangedFor` edge cases
- ✅ Network/server errors die webhooks blokkeerden
- ✅ Gebrek aan fallback mechanisme

**Verwachte verbetering**: 95%+ webhook delivery rate vs ~85% voorheen. 