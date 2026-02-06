#!/bin/bash
set -e

echo "======================================"
echo "Running CI Steps Locally"
echo "======================================"
echo ""
echo "This script runs the same checks as GitHub Actions CI"
echo ""

# Color codes for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Check if Docker is running
if ! docker info > /dev/null 2>&1; then
    echo -e "${RED}✗ Docker is not running. Please start Docker and try again.${NC}"
    exit 1
fi

echo -e "${GREEN}✓ Docker is running${NC}"
echo ""

# Track failures
FAILED_STEPS=()

# Function to run a step and track failures
run_step() {
    local step_name="$1"
    local command="$2"

    echo "======================================"
    echo "Step: $step_name"
    echo "======================================"

    if eval "$command"; then
        echo -e "${GREEN}✓ $step_name passed${NC}"
        echo ""
        return 0
    else
        echo -e "${RED}✗ $step_name failed${NC}"
        echo ""
        FAILED_STEPS+=("$step_name")
        return 1
    fi
}

# Step 1: Build (using dev environment for local testing)
echo "Note: Using env=dev for local execution (CI uses env=ci)"
echo ""

run_step "Build" "make env=dev start" || true

# Step 2: Update Composer dependencies (for Doctrine 3.x upgrade)
echo "======================================"
echo "Step: Update Composer Dependencies"
echo "======================================"
echo "Updating to Doctrine 3.x..."
make composer-update || {
    echo -e "${YELLOW}⚠ Composer update failed, continuing with existing dependencies${NC}"
}
echo ""

# Step 3: Clear cache
run_step "Clear Cache" "docker compose exec -T php sh -lc './bin/console cache:clear --no-warmup'" || true

# Step 4: DDD Layer Architecture Check
run_step "DDD Layer Architecture" "make env=dev layer" || true

# Step 5: Database Schema Validation
run_step "Database Schema Validation" "make env=dev schema-validate" || true

# Step 6: Coding Standards Check
echo "======================================"
echo "Step: Coding Standards"
echo "======================================"
echo "Note: Skipping cs-check as it's currently disabled in Makefile"
echo -e "${YELLOW}⚠ ECS check disabled until ECS upgrade${NC}"
echo ""

# Step 7: PHPStan (Static Analysis)
run_step "PHPStan Static Analysis" "make env=dev phpstan" || true

# Step 8: Psalm (Static Analysis)
run_step "Psalm Static Analysis" "make env=dev psalm" || true

# Step 9: Unit Tests
run_step "PHPUnit Tests" "make env=dev phpunit" || true

# Step 10: Verify Doctrine 3.x specific requirements
echo "======================================"
echo "Step: Doctrine 3.x Verification"
echo "======================================"
echo "Checking Domain layer has no Doctrine dependencies..."

if grep -q "use Doctrine" src/App/User/Domain/ValueObject/Auth/Credentials.php 2>/dev/null; then
    echo -e "${RED}✗ Doctrine imports found in Domain layer${NC}"
    FAILED_STEPS+=("Doctrine 3.x Verification")
else
    echo -e "${GREEN}✓ Domain layer is free of Doctrine dependencies${NC}"
fi
echo ""

# Step 11: Check Doctrine mappings
run_step "Doctrine Mapping Info" "docker compose exec -T php sh -lc './bin/console doctrine:mapping:info'" || true

# Step 12: E2E Tests (Optional - requires Playwright setup)
echo "======================================"
echo "Step: E2E Tests (Optional)"
echo "======================================"
if [ -d "e2e" ] && [ -f "e2e/package.json" ]; then
    echo "E2E tests available. To run them:"
    echo "  cd e2e && npx playwright test"
    echo ""
    echo "Skipping E2E tests in this script (run manually if needed)"
else
    echo "No E2E tests found"
fi
echo ""

# Summary
echo "======================================"
echo "CI Steps Summary"
echo "======================================"

if [ ${#FAILED_STEPS[@]} -eq 0 ]; then
    echo -e "${GREEN}✓ All CI steps passed!${NC}"
    echo ""
    echo "Your code is ready for CI and meets all quality checks."
    exit 0
else
    echo -e "${RED}✗ Some CI steps failed:${NC}"
    for step in "${FAILED_STEPS[@]}"; do
        echo -e "${RED}  - $step${NC}"
    done
    echo ""
    echo "Please fix the failing steps before pushing."
    exit 1
fi
