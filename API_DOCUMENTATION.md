# Vehicle Management API Documentation

## Base URL
```
http://127.0.0.1:8000/api/v1
```

## Authentication
This API uses Laravel Sanctum for authentication. Include the Bearer token in the Authorization header:
```
Authorization: Bearer {your-token}
```

## Test Credentials
- **Admin**: admin@example.com / password
- **Chauffeur**: chauffeur@example.com / password

## API Endpoints

### Authentication

#### Register User
```http
POST /register
```
**Body:**
```json
{
    "name": "John Doe",
    "email": "john@example.com",
    "password": "password123",
    "password_confirmation": "password123",
    "role": "chauffeur"
}
```

#### Login
```http
POST /login
```
**Body:**
```json
{
    "email": "admin@example.com",
    "password": "password"
}
```

#### Logout
```http
POST /logout
```
**Headers:** `Authorization: Bearer {token}`

#### Get Current User
```http
GET /user
```
**Headers:** `Authorization: Bearer {token}`

---

### Vehicles

#### List Vehicles
```http
GET /vehicles
```
**Query Parameters:**
- `status` (optional): active, archived
- `search` (optional): Search by registration number or model
- `page` (optional): Page number for pagination

#### Create Vehicle (Admin Only)
```http
POST /vehicles
```
**Body:**
```json
{
    "registration_number": "ABC-123",
    "model": "Toyota Camry",
    "year": 2022
}
```

#### Get Vehicle Details
```http
GET /vehicles/{id}
```

#### Update Vehicle (Admin Only)
```http
PUT /vehicles/{id}
```
**Body:**
```json
{
    "registration_number": "ABC-123",
    "model": "Toyota Camry",
    "year": 2022
}
```

#### Archive Vehicle (Admin Only)
```http
POST /vehicles/{id}/archive
```

#### Restore Vehicle (Admin Only)
```http
POST /vehicles/{id}/restore
```

#### Get My Vehicle (Chauffeur Only)
```http
GET /my-vehicle
```

---

### Vehicle Documents

#### List Vehicle Documents
```http
GET /vehicles/{vehicle_id}/documents
```

#### Create Document
```http
POST /vehicles/{vehicle_id}/documents
```
**Body (multipart/form-data):**
```
type: carte_grise|assurance|controle_technique
expiration_date: 2024-12-31
file: [file upload]
```

#### Get Document Details
```http
GET /vehicles/{vehicle_id}/documents/{document_id}
```

#### Update Document
```http
PUT /vehicles/{vehicle_id}/documents/{document_id}
```

#### Delete Document
```http
DELETE /vehicles/{vehicle_id}/documents/{document_id}
```

#### Get Expiring Documents
```http
GET /vehicles/{vehicle_id}/documents/expiring?days=30
```

---

### Maintenance Records

#### List Maintenance Records
```http
GET /vehicles/{vehicle_id}/maintenances
```

#### Create Maintenance Record
```http
POST /vehicles/{vehicle_id}/maintenances
```
**Body (multipart/form-data):**
```
maintenance_type: Oil Change
description: Regular oil change service
date: 2024-01-15
reminder_date: 2024-04-15
invoice: [file upload]
```

#### Get Maintenance Details
```http
GET /vehicles/{vehicle_id}/maintenances/{maintenance_id}
```

#### Update Maintenance Record
```http
PUT /vehicles/{vehicle_id}/maintenances/{maintenance_id}
```

#### Delete Maintenance Record
```http
DELETE /vehicles/{vehicle_id}/maintenances/{maintenance_id}
```

#### Get Upcoming Maintenance
```http
GET /vehicles/{vehicle_id}/maintenances/upcoming?days=7
```

---

### Vehicle Exchanges

#### List Exchanges
```http
GET /exchanges
```
**Query Parameters:**
- `status` (optional): pending, approved, rejected

#### Create Exchange Request (Chauffeur Only)
```http
POST /exchanges
```
**Body (multipart/form-data):**
```
to_driver_id: 2
vehicle_id: 1
note: Need to exchange for maintenance
before_photo: [file upload]
```

#### Get Exchange Details
```http
GET /exchanges/{id}
```

#### Update Exchange (Initiator Only)
```http
PUT /exchanges/{id}
```

#### Delete Exchange
```http
DELETE /exchanges/{id}
```

#### Approve Exchange (Admin Only)
```http
POST /exchanges/{id}/approve
```

#### Reject Exchange (Admin Only)
```http
POST /exchanges/{id}/reject
```

#### Get My Exchanges (Chauffeur Only)
```http
GET /my-exchanges
```

---

### User Management (Admin Only)

#### List Users
```http
GET /users
```
**Query Parameters:**
- `role` (optional): admin, chauffeur
- `search` (optional): Search by name or email

#### Create User
```http
POST /users
```
**Body:**
```json
{
    "name": "Jane Doe",
    "email": "jane@example.com",
    "password": "password123",
    "password_confirmation": "password123",
    "role": "chauffeur",
    "vehicle_id": 1
}
```

#### Get User Details
```http
GET /users/{id}
```

#### Update User
```http
PUT /users/{id}
```

#### Delete User
```http
DELETE /users/{id}
```

#### Assign Vehicle to User
```http
POST /users/{id}/assign-vehicle
```
**Body:**
```json
{
    "vehicle_id": 1
}
```

## Response Format

### Success Response
```json
{
    "message": "Success message",
    "data": {
        // Response data
    }
}
```

### Error Response
```json
{
    "message": "Error message",
    "errors": {
        "field": ["Validation error message"]
    }
}
```

## HTTP Status Codes
- `200` - OK
- `201` - Created
- `400` - Bad Request
- `401` - Unauthorized
- `403` - Forbidden
- `404` - Not Found
- `422` - Validation Error
- `500` - Internal Server Error

## File Upload Guidelines
- **Documents/Invoices**: PDF, JPG, JPEG, PNG (max 2MB)
- **Photos**: JPG, JPEG, PNG (max 2MB)
- Files are stored in `storage/app/public/` and accessible via `/storage/` URL

## Role-Based Access Control
- **Admin**: Full access to all endpoints
- **Chauffeur**: Limited access to own vehicle and exchange requests
