# eSchool API Documentation - Updated

## Authentication

### Login
**POST** `/api/login`
- Request:
  ```json
  {
    "email": "user@example.com",
    "password": "password"
  }
  ```
- Response:
  ```json
  {
    "data": {
      "user": {
        "id": 1,
        "name": "John Doe",
        "email": "user@example.com",
        "role": "staff",
        "school_id": 1
      }
    },
    "message": "Login successful",
    "expires_in": 3600
  }
  ```

## Eschool Management

### Create Eschool
**POST** `/api/eschools`
- Request:
  ```json
  {
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
  }
  ```
- Response:
  ```json
  {
    "id": 1,
    "school_id": 1,
    "coordinator_id": 2,
    "treasurer_id": 3,
    "name": "Basketball Club",
    "description": "School basketball club",
    "monthly_kas_amount": 20000,
    "schedule_days": ["Senin", "Rabu", "Jumat"],
    "total_schedule_days": 3,
    "is_active": true,
    "created_at": "2023-01-01T00:00:00.000000Z",
    "updated_at": "2023-01-01T00:00:00.000000Z",
    "coordinator": {
      "id": 2,
      "name": "Coordinator Name",
      "email": "coordinator@example.com",
      "role": "koordinator",
      "school_id": 1
    },
    "treasurer": {
      "id": 3,
      "name": "Treasurer Name",
      "email": "treasurer@example.com",
      "role": "bendahara",
      "school_id": 1
    },
    "members_count": 0
  }
  ```

### Update Eschool
**PUT** `/api/eschools/{id}`
- Request:
  ```json
  {
    "treasurer_option": "new",
    "new_treasurer_name": "New Treasurer Name",
    "new_treasurer_email": "newtreasurer@example.com",
    "new_treasurer_nip": "1122334455",
    "new_treasurer_date_of_birth": "1990-01-01",
    "new_treasurer_gender": "L",
    "new_treasurer_address": "789 Pine Rd",
    "new_treasurer_phone": "081234567892",
    "name": "Updated Basketball Club",
    "description": "Updated school basketball club",
    "monthly_kas_amount": 25000,
    "schedule_days": ["Senin", "Rabu", "Jumat", "Sabtu"],
    "total_schedule_days": 4,
    "is_active": true
  }
  ```
- Response:
  ```json
  {
    "id": 1,
    "school_id": 1,
    "coordinator_id": 2,
    "treasurer_id": 4,
    "name": "Updated Basketball Club",
    "description": "Updated school basketball club",
    "monthly_kas_amount": 25000,
    "schedule_days": ["Senin", "Rabu", "Jumat", "Sabtu"],
    "total_schedule_days": 4,
    "is_active": true,
    "created_at": "2023-01-01T00:00:00.000000Z",
    "updated_at": "2023-01-02T00:00:00.000000Z",
    "coordinator": {
      "id": 2,
      "name": "Coordinator Name",
      "email": "coordinator@example.com",
      "role": "koordinator",
      "school_id": 1
    },
    "treasurer": {
      "id": 4,
      "name": "New Treasurer Name",
      "email": "newtreasurer@example.com",
      "role": "bendahara",
      "school_id": 1
    },
    "members_count": 0
  }
  ```

### Get Eligible Treasurers
**GET** `/api/eschools/users/treasurers`
- Response:
  ```json
  [
    {
      "id": 3,
      "name": "Treasurer Name",
      "email": "treasurer@example.com"
    }
  ]
  ```