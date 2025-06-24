# Deployment Instructies voor Webhook Retry Hotfix

## Versie Informatie
- **Hotfix versie**: `1.0.14-beta1`
- **Specifiek voor**: Webhook reliability problemen
- **Veilig voor productie**: Ja (alleen verbeteringen, geen breaking changes)

## Installatie via Composer

### Stap 1: Update naar Hotfix Versie
```bash
# Navigate naar Magento root directory
cd /path/to/magento

# Update naar de specifieke hotfix versie
composer require tagginggroup/gtm:1.0.14-beta1

# Of als je een specifieke repository hebt:
composer config repositories.tagging-gtm-hotfix vcs https://github.com/[repository-url]
composer require tagginggroup/gtm:1.0.14-beta1

# Alternatief: Installeer development branch (no tag required)
composer require tagginggroup/gtm:dev-retagging
```

### Stap 2: Magento Setup
```bash
# Setup upgrade om nieuwe cron job te registreren
bin/magento setup:upgrade

# Clear cache
bin/magento cache:clean
bin/magento cache:flush

# Optioneel: compile als je di:compile gebruikt
bin/magento setup:di:compile
```

### Stap 3: Verificatie
```bash
# Check of de nieuwe cron job geregistreerd is
bin/magento cron:run --group="default"

# Check dat de module correct geladen is
bin/magento module:status | grep Tagging_GTM

# Test de cron job handmatig
bin/magento cron:run --group="default" --bootstrap="MAGE_DIRS[base][path]=/path/to/magento"
```

## Pre-deployment Testing (Aanbevolen)

### Staging Environment Test
```bash
# Test webhook retry functionaliteit
# 1. Plaats een order die volledig betaald wordt
# 2. Check debug logs voor webhook activity:
tail -f var/log/debug.log | grep "TriggerPurchaseWebhookEvent"

# 3. Test cron job:
bin/magento cron:run --group="default"
tail -f var/log/debug.log | grep "RetryFailedWebhooks"
```

### Database Check
```sql
-- Check payment additional information voor webhook tracking
SELECT 
    order_id, 
    JSON_EXTRACT(additional_information, '$.trytagging_webhook_processed') as processed,
    JSON_EXTRACT(additional_information, '$.trytagging_webhook_failed_attempts') as failed_attempts
FROM sales_order_payment 
WHERE created_at >= DATE_SUB(NOW(), INTERVAL 1 DAY)
ORDER BY created_at DESC;
```

## Rollback Plan

Als er problemen zijn, kun je terug naar de vorige versie:

```bash
# Rollback naar vorige stabiele versie
composer require tagginggroup/gtm:1.0.13

# Setup downgrade
bin/magento setup:upgrade
bin/magento cache:clean
```

## Monitoring na Deployment

### Debug Logging inschakelen
1. Zorg dat GTM debug mode aan staat in admin
2. Monitor deze log bestanden:
   - `var/log/debug.log` (voor debug berichten)
   - `var/log/system.log` (voor errors/warnings)

### Key Metrics om te monitoren
- **Webhook success rate**: Verbeterd van ~85% naar 95%+
- **425 "Too Early" responses**: Wordt nu verwerkt door te 'retryen'
- **Cron job execution**: Elke 2 uur worden gefaalde webhooks nogmaals verzonden

### Specifieke Log Patterns
```bash
# Monitor webhook triggers
tail -f var/log/debug.log | grep "TriggerPurchaseWebhookEvent::execute"

# Monitor webhook retries  
tail -f var/log/debug.log | grep "RetryFailedWebhooks"

# Monitor HTTP status codes
tail -f var/log/debug.log | grep "HTTP status code"

# Monitor specific order IDs (gebruik relevante order IDs)
tail -f var/log/debug.log | grep "ORDER_ID_PATTERN"
```

## Success Criteria

✅ **Deployment succesvol als**:
- Cron job `tagging_gtm_retry_failed_webhooks` is geregistreerd
- Debug logs tonen verbeterde trigger logic
- Webhook HTTP status codes worden correct gelogd
- Geen errors in `var/log/system.log`

## Contact voor Support

Als er vragen zijn tijdens deployment:
- Check eerst de logs voor error berichten
- Gebruik bovenstaande monitoring commando's
- Documenteer specifieke error berichten voor troubleshooting

## Toekomstige Updates

Deze hotfix wordt later geïntegreerd in de main release (1.0.14). Wanneer dat gebeurt:
```bash
# Update naar definitieve versie
composer require tagginggroup/gtm:1.0.14
bin/magento setup:upgrade
bin/magento cache:clean
```
