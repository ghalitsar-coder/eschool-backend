# Authentication Endpoints - API Documentation

Base URL: `http://127.0.0.1:8000/api`

## Available Endpoints

### 1. Register User

-   **Method**: `POST`
-   **Endpoint**: `/register`
-   **Description**: Register a new user account

#### Request Body:

```json
{
    "profile_id": 1,
    "name": "John Doe",
    "email": "john@example.com",
    "password": "password123",
    "password_confirmation": "password123"
}
```

#### cURL Example:

```bash
curl -X POST http://127.0.0.1:8000/api/register \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "profile_id": 1,
    "name": "John Doe",
    "email": "john@example.com",
    "password": "password123",
    "password_confirmation": "password123"
  }'
```

#### Response (201 Created):

```json
{
    "message": "User registered successfully",
    "user": {
        "id": 15,
        "name": "John Doe",
        "email": "john@example.com",
        "profile": {
            "id": 1,
            "name": "Bu Sari",
            "date_of_birth": "1980-05-15",
            "gender": "F",
            "address": "Jl. Anggrek No. 1, Jakarta",
            "status": "active"
        }
    },
    "token_info": {
        "type": "Bearer",
        "expires_in": 3600
    }
}
```

### 2. Login User

-   **Method**: `POST`
-   **Endpoint**: `/login`
-   **Description**: Login with existing credentials

#### Request Body:

```json
{
    "email": "bu.sari@example.com",
    "password": "password123"
}
```

#### cURL Example:

```bash
curl -X POST http://127.0.0.1:8000/api/login \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "email": "bu.sari@example.com",
    "password": "password123"
  }'
```

#### Response (200 OK):

```json
{
    "message": "User logged in successfully",
    "user": {
        "id": 1,
        "name": "Bu Sari",
        "email": "bu.sari@example.com",
        "profile": {
            "id": 1,
            "name": "Bu Sari",
            "date_of_birth": "1980-05-15",
            "gender": "F",
            "address": "Jl. Anggrek No. 1, Jakarta",
            "status": "active"
        }
    },
    "token_info": {
        "type": "Bearer",
        "expires_in": 3600
    }
}
```

### 3. Get Current User Info

-   **Method**: `GET`
-   **Endpoint**: `/me`
-   **Description**: Get authenticated user information
-   **Authentication**: Required (JWT Cookie)

#### cURL Example:

```bash
curl -X GET http://127.0.0.1:8000/api/me \
  -H "Accept: application/json" \
  -b "token=YOUR_JWT_TOKEN_HERE"
```

#### Response (200 OK):

```json
{
    "user": {
        "id": 1,
        "name": "Bu Sari",
        "email": "bu.sari@example.com",
        "email_verified_at": null,
        "profile": {
            "id": 1,
            "name": "Bu Sari",
            "date_of_birth": "1980-05-15",
            "gender": "F",
            "address": "Jl. Anggrek No. 1, Jakarta",
            "status": "active",
            "created_at": "2025-09-07T...",
            "updated_at": "2025-09-07T..."
        },
        "created_at": "2025-09-07T...",
        "updated_at": "2025-09-07T..."
    }
}
```

### 4. Refresh Token

-   **Method**: `POST`
-   **Endpoint**: `/refresh`
-   **Description**: Refresh JWT token to extend session
-   **Authentication**: Required (JWT Cookie)

#### cURL Example:

```bash
curl -X POST http://127.0.0.1:8000/api/refresh \
  -H "Accept: application/json" \
  -b "token=YOUR_JWT_TOKEN_HERE"
```

#### Response (200 OK):

```json
{
    "message": "Token refreshed successfully",
    "user": {
        "id": 1,
        "name": "Bu Sari",
        "email": "bu.sari@example.com",
        "profile": {
            "id": 1,
            "name": "Bu Sari",
            "date_of_birth": "1980-05-15",
            "gender": "F",
            "address": "Jl. Anggrek No. 1, Jakarta",
            "status": "active"
        }
    },
    "token_info": {
        "type": "Bearer",
        "expires_in": 3600
    }
}
```

### 5. Logout User

-   **Method**: `POST`
-   **Endpoint**: `/logout`
-   **Description**: Logout and invalidate token
-   **Authentication**: Required (JWT Cookie)

#### cURL Example:

```bash
curl -X POST http://127.0.0.1:8000/api/logout \
  -H "Accept: application/json" \
  -b "token=YOUR_JWT_TOKEN_HERE"
```

#### Response (200 OK):

```json
{
    "message": "User logged out successfully"
}
```

## Available Test Users

Based on the seeded data, you can use these existing users:

| ID  | Name     | Email                | Role                | School    |
| --- | -------- | -------------------- | ------------------- | --------- |
| 1   | Bu Sari  | bu.sari@example.com  | Staff/Supervisor    | Sekolah A |
| 2   | Pak Joko | pak.joko@example.com | Teacher/Coordinator | Sekolah A |
| 3   | Bu Rina  | bu.rina@example.com  | Staff/Supervisor    | Sekolah B |
| 4   | Pak Heru | pak.heru@example.com | Staff/Supervisor    | Sekolah C |
| 5   | Andi     | andi@example.com     | Student/Treasurer   | Sekolah A |

All users have the password: `password123`

## Available Profiles for Registration

| ID  | Name     | Date of Birth | Gender | School Context    |
| --- | -------- | ------------- | ------ | ----------------- |
| 1   | Bu Sari  | 1980-05-15    | F      | Sekolah A Staff   |
| 2   | Pak Joko | 1975-08-22    | M      | Sekolah A Teacher |
| 3   | Bu Rina  | 1982-11-30    | F      | Sekolah B Staff   |
| 4   | Pak Heru | 1978-03-12    | M      | Sekolah C Staff   |
| 5   | Andi     | 2005-07-10    | M      | Sekolah A Student |

## Error Responses

### Validation Errors (422 Unprocessable Entity):

```json
{
    "message": "The given data was invalid.",
    "errors": {
        "email": ["The email field is required."],
        "password": ["The password field is required."]
    }
}
```

### Authentication Errors (401 Unauthorized):

```json
{
    "error": "Unauthorized",
    "message": "Invalid email or password"
}
```

### Token Errors (401 Unauthorized):

```json
{
    "error": "Token not provided",
    "message": "No authentication token found"
}
```

## Notes for Testing

1. **Cookie-based Authentication**: Tokens are stored in HTTP-only cookies automatically
2. **Token Expiry**: Default JWT TTL is 60 minutes (configurable in `config/jwt.php`)
3. **CORS**: Make sure CORS is properly configured for your frontend domain
4. **HTTPS**: In production, ensure all authentication endpoints use HTTPS

## Postman Collection

You can import these endpoints into Postman:

1. Create a new collection called "Eschool Auth API"
2. Add each endpoint with the specified methods and headers
3. For protected routes, Postman will automatically handle cookies after login
4. Set environment variables for base URL: `{{BASE_URL}}` = `http://127.0.0.1:8000/api`
