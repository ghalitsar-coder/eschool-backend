# API Authentication Documentation

## Overview
This document describes the authentication endpoints for the Eschool Management System API. The system uses JWT (JSON Web Tokens) for authentication with support for token refresh.

## Base URL
```
http://localhost:8000/api
```

## Endpoints

### 1. Register
Create a new user account.

**Endpoint:** `POST /register`

**Request Body:**
```json
{
  "profile_id": 1,
  "name": "John Doe",
  "email": "john@example.com",
  "password": "password123",
  "password_confirmation": "password123"
}
```

**Response (Success - 201):**
```json
{
  "message": "User registered successfully",
  "user": {
    "id": 1,
    "profile_id": 1,
    "name": "John Doe",
    "email": "john@example.com",
    "email_verified_at": null,
    "created_at": "2023-01-01T00:00:00.000000Z",
    "updated_at": "2023-01-01T00:00:00.000000Z"
  },
  "token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9...",
  "token_type": "bearer",
  "expires_in": 3600
}
```

**Response (Validation Error - 422):**
```json
{
  "name": [
    "The name field is required."
  ],
  "email": [
    "The email field is required.",
    "The email must be a valid email address."
  ],
  "password": [
    "The password field is required.",
    "The password confirmation does not match."
  ]
}
```

### 2. Login
Authenticate a user and obtain a JWT token.

**Endpoint:** `POST /login`

**Request Body:**
```json
{
  "email": "john@example.com",
  "password": "password123"
}
```

**Response (Success - 200):**
```json
{
  "message": "User logged in successfully",
  "token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9...",
  "token_type": "bearer",
  "expires_in": 3600
}
```

**Response (Unauthorized - 401):**
```json
{
  "error": "Unauthorized"
}
```

**Response (Validation Error - 422):**
```json
{
  "email": [
    "The email field is required."
  ],
  "password": [
    "The password field is required."
  ]
}
```

### 3. Refresh Token
Obtain a new JWT token using the current token.

**Endpoint:** `POST /refresh`

**Headers:**
```
Authorization: Bearer <current_token>
```

**Response (Success - 200):**
```json
{
  "message": "Token refreshed successfully",
  "token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9...",
  "token_type": "bearer",
  "expires_in": 3600
}
```

### 4. Logout
Invalidate the current JWT token.

**Endpoint:** `POST /logout`

**Headers:**
```
Authorization: Bearer <current_token>
```

**Response (Success - 200):**
```json
{
  "message": "User logged out successfully"
}
```

### 5. Get Authenticated User
Retrieve the authenticated user's data.

**Endpoint:** `GET /me`

**Headers:**
```
Authorization: Bearer <current_token>
```

**Response (Success - 200):**
```json
{
  "id": 1,
  "profile_id": 1,
  "name": "John Doe",
  "email": "john@example.com",
  "email_verified_at": null,
  "created_at": "2023-01-01T00:00:00.000000Z",
  "updated_at": "2023-01-01T00:00:00.000000Z"
}
```

## Error Responses

### 401 Unauthorized
```json
{
  "error": "Unauthorized"
}
```

### 422 Validation Error
```json
{
  "field_name": [
    "Error message for the field"
  ]
}
```

## Token Information

- **Token Type:** Bearer
- **Default Expiration:** 60 minutes
- **Refresh Token Expiration:** 7 days
- **Algorithm:** HS256

## Usage Examples

### cURL Examples

**Register:**
```bash
curl -X POST http://localhost:8000/api/register \
  -H "Content-Type: application/json" \
  -d '{
    "profile_id": 1,
    "name": "John Doe",
    "email": "john@example.com",
    "password": "password123",
    "password_confirmation": "password123"
  }'
```

**Login:**
```bash
curl -X POST http://localhost:8000/api/login \
  -H "Content-Type: application/json" \
  -d '{
    "email": "john@example.com",
    "password": "password123"
  }'
```

**Refresh Token:**
```bash
curl -X POST http://localhost:8000/api/refresh \
  -H "Authorization: Bearer <current_token>"
```

**Logout:**
```bash
curl -X POST http://localhost:8000/api/logout \
  -H "Authorization: Bearer <current_token>"
```

**Get User Data:**
```bash
curl -X GET http://localhost:8000/api/me \
  -H "Authorization: Bearer <current_token>"
```