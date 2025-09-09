# File Upload System for Attendance Proof Documents

## Overview

The file upload system for attendance proof documents allows users to upload files as evidence when marking members as absent. The system includes comprehensive validation, secure storage, and automatic cleanup.

## Features

### 1. File Upload Handling

-   **Supported file types**: PDF, JPG, JPEG, PNG
-   **Maximum file size**: 5MB
-   **Unique filename generation**: Prevents conflicts with timestamp and random string
-   **Storage location**: `storage/app/public/attendance/proofs/`
-   **Public access**: Files are accessible via public URLs

### 2. File Validation

-   **MIME type validation**: Ensures file type matches extension
-   **File size validation**: Prevents files larger than 5MB
-   **Empty file detection**: Rejects empty files
-   **Security checks**: Basic validation to prevent malicious uploads

### 3. File Management

-   **Automatic cleanup**: Files are deleted when attendance records are deleted
-   **File replacement**: Old files are deleted when new ones are uploaded
-   **Status-based cleanup**: Files are removed when status changes from absent to present

## API Endpoints

### Create Attendance with File Upload

```http
POST /api/eschool/{eschoolId}/attendance/records
Content-Type: multipart/form-data
Authorization: Bearer {token}

{
  "date": "2025-01-15",
  "members": [
    {
      "member_id": 5,
      "is_present": false,
      "notes": "Sick leave",
      "proof_document": [FILE]
    }
  ]
}
```

### Update Attendance with File Upload

```http
PUT /api/eschool/{eschoolId}/attendance/records/{id}
Content-Type: multipart/form-data
Authorization: Bearer {token}

{
  "status": "absent",
  "notes": "Updated with proof",
  "proof_document": [FILE]
}
```

## Response Format

### Successful Upload Response

```json
{
    "success": true,
    "message": "Attendance record created successfully.",
    "data": {
        "id": 1,
        "date": "2025-01-15",
        "member": {
            "user_id": 5,
            "name": "John Doe",
            "student_id": "12345"
        },
        "is_present": false,
        "status": "absent",
        "notes": "Sick leave",
        "proof_document": "http://localhost:8000/storage/attendance/proofs/attendance_proof_5_2025-01-15_20250115100000_Ab3jqXkW.jpg",
        "created_at": "2025-01-15T10:00:00Z",
        "updated_at": "2025-01-15T10:00:00Z"
    }
}
```

### Validation Error Response

```json
{
    "success": false,
    "message": "Validation failed.",
    "errors": {
        "members.0.proof_document": [
            "The members.0.proof_document must be a file of type: pdf, jpg, jpeg, png."
        ]
    }
}
```

## File Naming Convention

Files are stored with unique names following this pattern:

```
attendance_proof_{userId}_{date}_{timestamp}_{randomString}.{extension}
```

Example:

```
attendance_proof_5_2025-01-15_20250115100000_Ab3jqXkW.jpg
```

## Storage Configuration

### Laravel Storage Configuration

The system uses Laravel's public disk configuration:

```php
// config/filesystems.php
'public' => [
    'driver' => 'local',
    'root' => storage_path('app/public'),
    'url' => env('APP_URL').'/storage',
    'visibility' => 'public',
],
```

### Storage Link

Ensure the storage link is created:

```bash
php artisan storage:link
```

This creates a symbolic link from `public/storage` to `storage/app/public`.

## Security Considerations

### 1. File Validation

-   MIME type checking prevents disguised malicious files
-   File extension validation provides additional security
-   File size limits prevent storage abuse
-   Empty file detection prevents invalid uploads

### 2. Access Control

-   Files are only accessible to authenticated users
-   Eschool membership validation ensures proper access
-   Role-based permissions control who can upload files

### 3. File Storage

-   Files are stored outside the web root in `storage/app/public`
-   Public access is controlled through Laravel's storage system
-   Unique filenames prevent conflicts and guessing attacks

## Error Handling

### Common Validation Errors

1. **Invalid file type**

    ```json
    {
        "errors": {
            "members.0.proof_document": [
                "The members.0.proof_document must be a file of type: pdf, jpg, jpeg, png."
            ]
        }
    }
    ```

2. **File too large**

    ```json
    {
        "errors": {
            "members.0.proof_document": [
                "The members.0.proof_document may not be greater than 5120 kilobytes."
            ]
        }
    }
    ```

3. **Upload failure**
    ```json
    {
        "success": false,
        "message": "Failed to upload proof document: Storage error"
    }
    ```

## Testing

### Unit Tests

-   File upload functionality
-   File validation rules
-   File deletion
-   Unique filename generation

### Integration Tests

-   Complete upload workflow
-   File cleanup on record deletion
-   File replacement on updates
-   URL generation and access

### Running Tests

```bash
# Run file upload service tests
php artisan test tests/Unit/FileUploadServiceTest.php

# Run attendance controller file upload tests
php artisan test tests/Feature/AttendanceControllerTest.php --filter="proof_document|upload|file"
```

## Maintenance

### File Cleanup

The system automatically handles file cleanup in the following scenarios:

1. When attendance records are deleted
2. When proof documents are replaced with new uploads
3. When attendance status changes from absent to present

### Storage Monitoring

Monitor the `storage/app/public/attendance/proofs/` directory for:

-   Disk space usage
-   Orphaned files (files without corresponding database records)
-   File access patterns

### Backup Considerations

Include the `storage/app/public/attendance/proofs/` directory in your backup strategy to ensure proof documents are preserved.

## Performance Considerations

### File Size Optimization

-   5MB limit balances quality and storage efficiency
-   Consider implementing image compression for large images
-   Monitor storage usage and implement cleanup policies if needed

### Caching

-   File URLs are generated dynamically but can be cached
-   Consider CDN integration for high-traffic scenarios
-   Implement proper cache invalidation when files are updated

## Future Enhancements

### Potential Improvements

1. **Image compression**: Automatically compress uploaded images
2. **Virus scanning**: Integrate antivirus scanning for uploaded files
3. **CDN integration**: Use cloud storage for better performance
4. **Thumbnail generation**: Create thumbnails for image files
5. **Bulk upload**: Support multiple file uploads in a single request
6. **File versioning**: Keep history of replaced files
