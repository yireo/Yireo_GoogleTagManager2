# Developer Release Management Guide

## Overzicht
Dit document beschrijft hoe je nieuwe versies van de GTM module kunt uitgeven en beheren voor Composer installatie.

## Versie Management

### Huidige Versie Structuur
- **Versie format**: SemVer 2.0.0 compliant (`X.Y.Z-prerelease`)
- **Huidige versie**: `1.0.14-beta1`
- **Branch naming**: `dev/feature-name` of `dev/version-type`

### Versie Bumping
Update versies in de volgende bestanden:
```bash
# composer.json
"version": "1.0.14-beta1"

# docs/WEBHOOK_RETRY_DEPLOYMENT.md  
- **Hotfix versie**: `1.0.14-beta1`
```

## Git Tag Management voor Composer Releases

### Tag Creation Process
Voor het beschikbaar maken van een nieuwe versie voor Composer installatie:

```bash
# 1. Navigeer naar project directory
cd /path/to/magento-gtm-project

# 2. Zorg dat alle wijzigingen zijn gecommit en gepusht
git status
git push

# 3. Maak een git tag aan voor de versie in composer.json
git tag 1.0.14-beta1

# 4. Push de tag naar de remote repository
git push origin 1.0.14-beta1

# 5. Controleer dat de tag correct is aangemaakt
git tag --list | grep 1.0.14
```

### Tag Verification
```bash
# Check alle tags
git tag --list | sort -V

# Check specific tag details
git show 1.0.14-beta1

# Check tag op remote
git ls-remote --tags origin
```

## Composer Installation Options

### 1. Tagged Release Installation (Productie)
```bash
# Normale installatie met officiële tag
composer require tagginggroup/gtm:1.0.14-beta1

# Met specifieke repository
composer config repositories.tagging-gtm vcs https://github.com/AdPageGroup/Magento.git
composer require tagginggroup/gtm:1.0.14-beta1
```

### 2. Development Branch Installation (Testing)
```bash
# Installeer direct van development branch (geen tag vereist)
composer require tagginggroup/gtm:dev-retagging

# Via repository configuratie
composer config repositories.tagging-gtm-dev vcs https://github.com/AdPageGroup/Magento.git
composer require tagginggroup/gtm:dev-retagging
```

### 3. Specific Commit Installation
```bash
# Via commit hash (advanced)
composer require tagginggroup/gtm:dev-main#abc123def
```

## Release Workflow

### 1. Pre-Release Checklist
- [ ] Code is getest en werkt correct
- [ ] GitHub Actions tests slagen
- [ ] Versie is bijgewerkt in `composer.json`
- [ ] Deployment documentatie is bijgewerkt
- [ ] CHANGELOG is bijgewerkt (indien van toepassing)

### 2. Release Steps
```bash
# 1. Final commit
git add .
git commit -m "Prepare release 1.0.14-beta1"
git push

# 2. Create and push tag
git tag 1.0.14-beta1
git push origin 1.0.14-beta1

# 3. Verify tag is available
git tag --list | grep 1.0.14
```

### 3. Post-Release Verification
```bash
# Test Composer installation
composer create-project --no-install test-project
cd test-project
composer require tagginggroup/gtm:1.0.14-beta1

# Check installed version
composer show tagginggroup/gtm
```

## Version Types & Semantic Versioning

### SemVer Guidelines
- **Major** (`X.0.0`): Breaking changes
- **Minor** (`1.X.0`): New features, backward compatible
- **Patch** (`1.0.X`): Bug fixes, backward compatible
- **Pre-release** (`1.0.0-alpha1`, `1.0.0-beta1`, `1.0.0-rc1`): Testing versions

### Pre-release Suffixes
- `-alpha1, -alpha2, ...`: Early development versions
- `-beta1, -beta2, ...`: Feature complete, testing phase
- `-rc1, -rc2, ...`: Release candidates, final testing

### Branch to Version Mapping
```bash
# Development branches
dev/feature-name     → X.Y.Z-alpha1
dev/webhook-retry    → 1.0.14-beta1
dev/bug-fixes        → 1.0.13-rc1

# Release branches  
release/1.0.14       → 1.0.14
hotfix/1.0.14-patch1 → 1.0.15
```

## Troubleshooting

### Tag Issues
```bash
# Delete local tag
git tag -d 1.0.14-beta1

# Delete remote tag
git push origin --delete 1.0.14-beta1

# Re-create tag
git tag 1.0.14-beta1
git push origin 1.0.14-beta1
```

### Composer Issues
```bash
# Clear Composer cache
composer clear-cache

# Force update
composer update tagginggroup/gtm --with-dependencies

# Debug Composer version resolution
composer why-not tagginggroup/gtm 1.0.14-beta1
```

## Important Notes

⚠️ **Composer Version Behavior:**
- Composer gebruikt **git tags** om versies te bepalen
- De versie in `composer.json` is voor metadata, niet voor installatie
- Zonder git tag is alleen branch-based installatie mogelijk
- Tags moeten SemVer compliant zijn voor optimale compatibiliteit

⚠️ **Release Best Practices:**
- Test altijd eerst met development branch installatie
- Gebruik pre-release versions voor beta testing
- Documenteer breaking changes duidelijk
- Houdt changelogs bij voor belangrijke releases

## Packagist Integration

Als het package op Packagist staat:
- Tags worden automatisch gedetecteerd en geïndexeerd
- Nieuwe versies zijn binnen enkele minuten beschikbaar
- Webhook updates zorgen voor automatische synchronisatie
- Check status op: https://packagist.org/packages/tagginggroup/gtm 