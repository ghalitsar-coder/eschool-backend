#!/bin/bash

# Test script for eschool API

echo "Testing eschool API..."

# Test login
echo "Testing login..."
curl -X POST http://localhost:8000/api/login \
  -H "Content-Type: application/json" \
  -d '{"email": "staff@example.com", "password": "password"}'

# Test create eschool
echo "Testing create eschool..."
curl -X POST http://localhost:8000/api/eschools \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer YOUR_TOKEN_HERE" \
  -d '{
    "school_id": 1,
    "new_coordinator_name": "Coordinator Name",
    "new_coordinator_email": "coordinator@example.com",
    "new_coordinator_nip": "1234567890",
    "new_coordinator_date_of_birth": "1990-01-01",
    "new_coordinator_gender": "L",
    "new_coordinator_address": "123 Main St",
    "new_coordinator_phone": "081234567890",
    "treasurer_option": "new",
    "new_treasurer_name": "Treasurer Name",
    "new_treasurer_email": "treasurer@example.com",
    "new_treasurer_nip": "0987654321",
    "new_treasurer_date_of_birth": "1990-01-01",
    "new_treasurer_gender": "P",
    "new_treasurer_address": "456 Oak Ave",
    "new_treasurer_phone": "081234567891",
    "name": "Basketball Club",
    "description": "School basketball club",
    "monthly_kas_amount": 20000,
    "schedule_days": ["Senin", "Rabu", "Jumat"],
    "total_schedule_days": 3,
    "is_active": true
  }'

echo "Test completed."
