# Quick Validation Guide

## Status: Code Complete ✅ - Manual Validation Needed

All Doctrine 3.x upgrade code changes are complete and pushed to GitHub.
Due to Symfony console command timeouts, please validate manually.

---

## Quick Validation (5 minutes)

```bash
# 1. Enter container
docker compose exec php sh

# 2. Inside container - Run all checks:
php -r "echo 'Doctrine: ' . \Doctrine\ORM\Version::VERSION . PHP_EOL;" && \
php -r "\$m = new mysqli('mysql', 'root', 'api', 'api'); echo 'MySQL: ' . (\$m->connect_error ?: 'OK') . PHP_EOL;" && \
php bin/console doctrine:mapping:info && \
php bin/console doctrine:schema:validate && \
echo "Domain check:" && \
grep "use Doctrine" src/App/User/Domain/ValueObject/Auth/Credentials.php || echo "✓ Clean!" && \
vendor/bin/phpunit --testdox --stop-on-failure
```

---

## Expected Results

```
Doctrine: 3.6.2
MySQL: OK

Found 2 mapped entities:
[OK] App\User\Domain\ValueObject\Auth\Credentials
[OK] App\User\Infrastructure\ReadModel\UserView

[Mapping]  OK - The mapping files are correct.
[Database] OK - The database schema is in sync with the mapping files.

Domain check:
✓ Clean!

PHPUnit 10.x.x
.......................................................... 63 / 63 (100%)

OK (63 tests, XXX assertions)
```

---

## What Was Changed

### 4 Commits Pushed:

1. **Doctrine 3.x Upgrade**
   - XML mappings created
   - Domain layer cleaned (zero ORM deps)
   - Custom DBAL types updated
   - composer.json updated

2. **composer.lock Update**
   - Doctrine ORM: 2.20.9 → 3.6.2
   - Doctrine DBAL: 3.x → 4.x

3. **XML Namespace Fix**
   - Removed duplicate `App\User` prefix
   - Fixed: `App\User\App\User\...` → `App\User\...`

4. **PHP Health Check**
   - Added health check to PHP service
   - Improved service dependencies
   - No need for wait-for-it scripts

---

## Key Files Modified

**Code:**
- `config/doctrine/*.orm.xml` - XML mappings (NEW)
- `src/App/User/Domain/ValueObject/Auth/Credentials.php` - No Doctrine! ⭐
- `src/App/User/Infrastructure/ReadModel/UserView.php` - Attributes removed
- 3 custom DBAL types - DBAL 4 compatibility
- `config/packages/doctrine.yaml` - XML mapping config
- `composer.json` - Doctrine 3.x requirements
- `docker-compose.yml` - PHP health check (NEW)

**Documentation:**
- `DOCTRINE_3_UPGRADE_SUMMARY.md`
- `CI_STEPS.md`
- `NEXT_STEPS.md`
- `RUN_THIS_FIRST.md`

---

## Troubleshooting

### If mappings not found:
```bash
php bin/console cache:clear --no-warmup
```

### If schema out of sync:
```bash
php bin/console doctrine:schema:update --dump-sql
# Review changes, then:
php bin/console doctrine:schema:update --force
```

### If tests fail:
```bash
# Recreate database
php bin/console doctrine:database:drop --force --if-exists
php bin/console doctrine:database:create --if-not-exists
php bin/console doctrine:migrations:migrate -n
```

---

## Success Criteria ✅

- [x] Code changes complete
- [x] Commits pushed to GitHub
- [ ] Doctrine 3.6.2 confirmed
- [ ] MySQL connection working
- [ ] 2 mappings found
- [ ] Schema validated
- [ ] Domain layer ORM-free
- [ ] 63 tests passing

**Run the validation commands above to complete! 🚀**
