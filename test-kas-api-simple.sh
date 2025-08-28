#!/bin/bash

# Kas API Testing Script (Simple version without jq)
# Testing all endpoints and error scenarios

BASE_URL="http://127.0.0.1:8000/api"
echo "=== KAS API TESTING (Simple Version) ==="
echo "Base URL: $BASE_URL"
echo ""

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Function to print test results
print_test() {
    echo -e "${BLUE}=== $1 ===${NC}"
}

print_success() {
    echo -e "${GREEN}✅ $1${NC}"
}

print_error() {
    echo -e "${RED}❌ $1${NC}"
}

print_warning() {
    echo -e "${YELLOW}⚠️  $1${NC}"
}

# Test 1: Login as different roles
print_test "TEST 1: LOGIN DIFFERENT ROLES"

echo "1.1 Login as Bendahara"
BENDAHARA_RESPONSE=$(curl -s -X POST "$BASE_URL/login" \
  -H "Content-Type: application/json" \
  -d '{
    "email": "bendahara1@example.com",
    "password": "password"
  }')
echo "Response: $BENDAHARA_RESPONSE"

echo ""
echo "1.2 Login as Siswa"
SISWA_RESPONSE=$(curl -s -X POST "$BASE_URL/login" \
  -H "Content-Type: application/json" \
  -d '{
    "email": "siswa1@example.com",
    "password": "password"
  }')
echo "Response: $SISWA_RESPONSE"

echo ""
echo "1.3 Login as Koordinator"
KOORDINATOR_RESPONSE=$(curl -s -X POST "$BASE_URL/login" \
  -H "Content-Type: application/json" \
  -d '{
    "email": "koordinator1@example.com",
    "password": "password"
  }')
echo "Response: $KOORDINATOR_RESPONSE"

echo ""

# Extract tokens manually (simple approach)
BENDAHARA_TOKEN=$(echo "$BENDAHARA_RESPONSE" | grep -o '"token":"[^"]*"' | cut -d'"' -f4)
SISWA_TOKEN=$(echo "$SISWA_RESPONSE" | grep -o '"token":"[^"]*"' | cut -d'"' -f4)
KOORDINATOR_TOKEN=$(echo "$KOORDINATOR_RESPONSE" | grep -o '"token":"[^"]*"' | cut -d'"' -f4)

if [ ! -z "$BENDAHARA_TOKEN" ]; then
    print_success "Bendahara token extracted: ${BENDAHARA_TOKEN:0:20}..."
else
    print_error "Failed to extract bendahara token"
fi

# Test 2: Test unauthorized access (non-bendahara roles)
print_test "TEST 2: UNAUTHORIZED ACCESS TESTS"

echo "2.1 Siswa trying to access /members (should fail)"
RESPONSE=$(curl -s -X GET "$BASE_URL/members" \
  -H "Authorization: Bearer $SISWA_TOKEN" \
  -H "Content-Type: application/json")
echo "Response: $RESPONSE"

echo ""
echo "2.2 Koordinator trying to access /kas/summary (should fail)"
RESPONSE=$(curl -s -X GET "$BASE_URL/kas/summary" \
  -H "Authorization: Bearer $KOORDINATOR_TOKEN" \
  -H "Content-Type: application/json")
echo "Response: $RESPONSE"

echo ""
echo "2.3 No token access to /members (should fail)"
RESPONSE=$(curl -s -X GET "$BASE_URL/members" \
  -H "Content-Type: application/json")
echo "Response: $RESPONSE"

echo ""

# Test 3: Authorized access (bendahara role)
print_test "TEST 3: AUTHORIZED ACCESS (BENDAHARA)"

echo "3.1 Get Members"
MEMBERS_RESPONSE=$(curl -s -X GET "$BASE_URL/members" \
  -H "Authorization: Bearer $BENDAHARA_TOKEN" \
  -H "Content-Type: application/json")
echo "Response: $MEMBERS_RESPONSE"

echo ""
echo "3.2 Get Kas Summary"
SUMMARY_RESPONSE=$(curl -s -X GET "$BASE_URL/kas/summary" \
  -H "Authorization: Bearer $BENDAHARA_TOKEN" \
  -H "Content-Type: application/json")
echo "Response: $SUMMARY_RESPONSE"

echo ""
echo "3.3 Get Kas Records"
RECORDS_RESPONSE=$(curl -s -X GET "$BASE_URL/kas/records" \
  -H "Authorization: Bearer $BENDAHARA_TOKEN" \
  -H "Content-Type: application/json")
echo "Response: $RECORDS_RESPONSE"

echo ""

# Test 4: Store Income (valid data)
print_test "TEST 4: STORE INCOME (VALID DATA)"

echo "4.1 Store Income with sample data"
INCOME_RESPONSE=$(curl -s -X POST "$BASE_URL/kas/income" \
  -H "Authorization: Bearer $BENDAHARA_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "description": "Penerimaan kas bulan testing",
    "date": "2025-01-15",
    "payments": [
      {
        "member_id": 1,
        "amount": 25000,
        "month": 1,
        "year": 2025
      },
      {
        "member_id": 2,
        "amount": 25000,
        "month": 1,
        "year": 2025
      }
    ]
  }')
echo "Response: $INCOME_RESPONSE"

echo ""

# Test 5: Store Expense (valid data)
print_test "TEST 5: STORE EXPENSE (VALID DATA)"

echo "5.1 Store Expense"
EXPENSE_RESPONSE=$(curl -s -X POST "$BASE_URL/kas/expense" \
  -H "Authorization: Bearer $BENDAHARA_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "amount": 50000,
    "description": "Pembelian alat tulis untuk testing",
    "date": "2025-01-15"
  }')
echo "Response: $EXPENSE_RESPONSE"

echo ""

# Test 6: Validation errors
print_test "TEST 6: VALIDATION ERRORS"

echo "6.1 Store Income with missing required fields"
ERROR_RESPONSE=$(curl -s -X POST "$BASE_URL/kas/income" \
  -H "Authorization: Bearer $BENDAHARA_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "description": "Test without payments"
  }')
echo "Response: $ERROR_RESPONSE"

echo ""
echo "6.2 Store Income with invalid member_id"
ERROR_RESPONSE=$(curl -s -X POST "$BASE_URL/kas/income" \
  -H "Authorization: Bearer $BENDAHARA_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "description": "Test with invalid member",
    "date": "2025-01-15",
    "payments": [
      {
        "member_id": 99999,
        "amount": 25000,
        "month": 1,
        "year": 2025
      }
    ]
  }')
echo "Response: $ERROR_RESPONSE"

echo ""
echo "6.3 Store Expense with missing amount"
ERROR_RESPONSE=$(curl -s -X POST "$BASE_URL/kas/expense" \
  -H "Authorization: Bearer $BENDAHARA_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "description": "Test without amount",
    "date": "2025-01-15"
  }')
echo "Response: $ERROR_RESPONSE"

echo ""

# Test 7: Invalid token
print_test "TEST 7: INVALID TOKEN"

echo "7.1 Access with invalid token"
INVALID_RESPONSE=$(curl -s -X GET "$BASE_URL/members" \
  -H "Authorization: Bearer invalid_token_here" \
  -H "Content-Type: application/json")
echo "Response: $INVALID_RESPONSE"

echo ""

print_test "TESTING COMPLETED"
print_success "All tests executed. Check responses above for results."

echo ""
echo "=== SUMMARY ==="
echo "✅ Expected to work: Bendahara accessing kas endpoints"
echo "❌ Expected to fail: Non-bendahara roles accessing kas endpoints"
echo "❌ Expected to fail: Invalid tokens, missing fields, invalid data"