# CI Steps - Local Execution Guide

This document describes all CI steps and how to run them locally.

## Quick Start

**Run all CI steps:**
```bash
./run-ci-locally.sh
```

**Or run individual steps below:**

---

## CI Steps (from .github/workflows/pr.yml)

### 1. Build Environment

**CI Command:**
```bash
make env=ci start
```

**Local Command:**
```bash
make env=dev start
```

**What it does:**
- Stops existing containers
- Builds Docker images
- Installs Composer dependencies
- Starts all services (nginx, php, mysql, rmq, elasticsearch)

---

### 2. Update Dependencies (Doctrine 3.x Upgrade)

**Command:**
```bash
make composer-update
```

**What it does:**
- Updates Doctrine ORM from 2.x to 3.x
- Updates Doctrine DBAL from 3.x to 4.x
- Updates related dependencies

---

### 3. Clear Cache

**Command:**
```bash
docker compose exec -T php sh -lc './bin/console cache:clear --no-warmup'
```

**What it does:**
- Clears Symfony cache
- Ensures fresh metadata for Doctrine mappings

---

### 4. DDD Layer Architecture

**CI Command:**
```bash
make env=ci layer
```

**Local Command:**
```bash
make env=dev layer
```

**What it does:**
- Runs deptrac to validate architectural layers
- Ensures Domain layer doesn't depend on Infrastructure
- Verifies DDD boundaries are respected

**Expected Output:**
```
✓ No violations found
```

---

### 5. Database Schema Validation

**CI Command:**
```bash
make env=ci schema-validate
```

**Local Command:**
```bash
make env=dev schema-validate
```

**Underlying Command:**
```bash
./bin/console doctrine:schema:validate
```

**What it does:**
- Validates Doctrine mappings are correct
- Checks database schema matches entity definitions
- Ensures no schema drift

**Expected Output:**
```
[Mapping]  OK - The mapping files are correct.
[Database] OK - The database schema is in sync with the mapping files.
```

---

### 6. Coding Standards (Currently Disabled)

**CI Command:**
```bash
make env=ci cs-check
```

**Status:** Disabled until ECS upgrade (see Makefile comment)

---

### 7. PHPStan (Static Analysis)

**CI Command:**
```bash
make env=ci phpstan
```

**Local Command:**
```bash
make env=dev phpstan
```

**Underlying Command:**
```bash
./vendor/bin/phpstan analyse --memory-limit=512M
```

**What it does:**
- Static analysis of PHP code
- Type checking
- Detects potential bugs

**Expected Output:**
```
[OK] No errors
```

---

### 8. Psalm (Static Analysis)

**CI Command:**
```bash
make env=ci psalm
```

**Local Command:**
```bash
make env=dev psalm
```

**Underlying Command:**
```bash
./vendor/bin/psalm --show-info=false
```

**What it does:**
- Advanced static analysis
- Type inference
- Security vulnerability detection

**Expected Output:**
```
No errors found!
```

---

### 9. PHPUnit Tests

**CI Command:**
```bash
make env=ci conf="--coverage-clover build/logs/clover.xml" phpunit
```

**Local Command (without coverage):**
```bash
make env=dev phpunit
```

**Local Command (with coverage):**
```bash
make env=dev conf="--coverage-clover build/logs/clover.xml" phpunit
```

**Underlying Command:**
```bash
XDEBUG_MODE=coverage ./vendor/bin/phpunit
```

**What it does:**
- Runs all 63 unit tests
- Tests Domain logic, value objects, services
- Tests Infrastructure layer (repositories, projections)

**Expected Output:**
```
OK (63 tests, XXX assertions)
```

---

### 10. Doctrine 3.x Specific Checks

**Check Domain Layer Purity:**
```bash
grep "use Doctrine" src/App/User/Domain/ValueObject/Auth/Credentials.php
```

**Expected:** No output (exit code 1)

**Check Mappings:**
```bash
docker compose exec -T php sh -lc './bin/console doctrine:mapping:info'
```

**Expected Output:**
```
Found 2 mapped entities:
[OK] App\User\Domain\ValueObject\Auth\Credentials
[OK] App\User\Infrastructure\ReadModel\UserView
```

---

### 11. Artifact Build (Optional)

**CI Command:**
```bash
make env=ci artifact
```

**What it does:**
- Builds production Docker image
- Validates Dockerfile
- Ensures production build succeeds

---

### 12. E2E Tests (Optional)

**Command:**
```bash
cd e2e && npx playwright test
```

**What it does:**
- Runs 15 Playwright E2E tests
- Tests API endpoints
- Tests full user flows

**Expected Output:**
```
15 passed (XXs)
```

---

## Common Issues & Solutions

### Issue: Docker not running
**Error:** `Cannot connect to the Docker daemon`

**Solution:**
```bash
# Start Docker Desktop or Docker daemon
# Then retry
```

### Issue: Port conflicts
**Error:** `port is already allocated`

**Solution:**
```bash
make stop
make start
```

### Issue: Stale cache
**Error:** Metadata cache errors

**Solution:**
```bash
docker compose exec -T php sh -lc './bin/console cache:clear --no-warmup'
make db  # Recreate database
```

### Issue: Composer dependency conflicts
**Error:** Doctrine version conflicts

**Solution:**
```bash
make composer-update
# If that fails:
docker compose exec -T code sh -lc 'rm -rf vendor composer.lock && composer install'
```

### Issue: Schema validation fails
**Error:** Database schema not in sync

**Solution:**
```bash
# Check what's different
docker compose exec -T php sh -lc './bin/console doctrine:schema:update --dump-sql'

# Apply changes if needed (be careful!)
docker compose exec -T php sh -lc './bin/console doctrine:schema:update --force'

# Or recreate database
make db
```

---

## CI vs Local Differences

| Aspect | CI (env=ci) | Local (env=dev) |
|--------|-------------|-----------------|
| Docker Compose | Uses etc/ci/docker-compose.yml | Uses etc/dev/docker-compose.yml |
| PHP Extensions | Production build | Dev build with Xdebug |
| Service Wait | --wait flag for healthchecks | --wait flag for healthchecks |
| Cache | Fresh every time | Persists between runs |
| Database | Fresh every run | Persists (unless `make db`) |

---

## Quick Commands Reference

```bash
# Full CI simulation
./run-ci-locally.sh

# Individual checks
make env=dev layer          # Architecture
make env=dev schema-validate # Schema
make env=dev phpstan        # Static analysis
make env=dev psalm          # Static analysis
make env=dev phpunit        # Tests

# Doctrine checks
docker compose exec -T php sh -lc './bin/console doctrine:mapping:info'
docker compose exec -T php sh -lc './bin/console doctrine:schema:validate'

# E2E tests
cd e2e && npx playwright test

# Clean start
make start                  # Full rebuild
```

---

## Success Criteria

All steps should pass:
- ✓ Build completes
- ✓ DDD layers validated
- ✓ Schema validated
- ✓ PHPStan: 0 errors
- ✓ Psalm: 0 errors
- ✓ PHPUnit: 63/63 tests pass
- ✓ E2E: 15/15 tests pass (optional)
- ✓ No Doctrine in Domain layer
- ✓ All Doctrine mappings found

---

## Troubleshooting

If CI fails but local passes:
1. Check Docker versions match
2. Check PHP versions match (8.3)
3. Clear all caches: `make stop && make start`
4. Check `.env` vs `.env.test` configurations

If local fails but you think it should pass:
1. Compare with CI environment variables
2. Check service dependencies are running: `make start-deps`
3. Recreate database: `make db`
4. Clear all caches and rebuild: `make rebuild`

---

For more details, see:
- `.github/workflows/pr.yml` - PR workflow
- `.github/workflows/push.yml` - Push workflow
- `Makefile` - Make targets
- `DOCTRINE_3_UPGRADE_SUMMARY.md` - Doctrine upgrade details
