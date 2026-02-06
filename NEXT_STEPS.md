# Quick Start - Doctrine 3.x Upgrade Next Steps

## ⚠️ Prerequisites
- Docker must be running
- Run `make start-deps` to ensure dependencies are up

## 🚀 Single Command Validation

```bash
./validate-doctrine-upgrade.sh
```

This will run all validation steps and report success or failure.

## 📋 Manual Step-by-Step (Alternative)

If you prefer to run steps individually:

### 1. Update Dependencies
```bash
make composer-update
```

### 2. Clear Cache
```bash
docker compose exec -T php sh -lc './bin/console cache:clear --no-warmup'
```

### 3. Verify Mappings
```bash
docker compose exec -T php sh -lc './bin/console doctrine:mapping:info'
```
Expected: 2 mappings found (Credentials, UserView)

### 4. Validate Schema
```bash
docker compose exec -T php sh -lc './bin/console doctrine:schema:validate'
```
Expected: Mapping OK, Database OK

### 5. Check Schema Changes
```bash
docker compose exec -T php sh -lc './bin/console doctrine:schema:update --dump-sql'
```
Expected: Empty output (no changes needed)

### 6. Run Tests
```bash
make phpunit
```
Expected: 63 tests pass

### 7. Run E2E Tests
```bash
cd e2e && npx playwright test
```
Expected: 15 tests pass

## ✅ Success Indicators

- ✓ Composer installs Doctrine ORM 3.2.x
- ✓ Doctrine DBAL upgrades to 4.x
- ✓ All mappings found and valid
- ✓ Schema validation passes
- ✓ No schema changes required
- ✓ All 63 unit tests pass
- ✓ All 15 E2E tests pass
- ✓ Zero Doctrine imports in `Credentials.php`

## 🔴 If Tests Fail

1. Capture the error output
2. Check `doctrine:schema:validate` output
3. Run `doctrine:schema:update --dump-sql` to see any schema differences
4. Review the logs for specific errors

## 📄 Full Documentation

- **Summary:** `DOCTRINE_3_UPGRADE_SUMMARY.md` - Complete overview of changes
- **Original Plan:** Review the plan in the conversation history

## 🎯 Primary Goal

Achieve **zero Doctrine ORM dependencies in Domain layer** while upgrading to Doctrine 3.x.

Verify with:
```bash
grep "use Doctrine" src/App/User/Domain/ValueObject/Auth/Credentials.php
# Should return: no output (exit code 1)
```

---

Good luck! 🚀
