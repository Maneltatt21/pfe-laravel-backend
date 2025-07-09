# 📚 Swagger API Documentation

## 🎯 Overview
Your Vehicle Management API now has **comprehensive Swagger/OpenAPI documentation** that provides an interactive interface for exploring and testing all API endpoints.

## 🌐 Access the Documentation
**Swagger UI URL:** `http://127.0.0.1:8000/api/documentation`

The documentation is automatically generated from code annotations and provides:
- ✅ Interactive API explorer
- ✅ Request/response examples
- ✅ Authentication testing
- ✅ Schema definitions
- ✅ Parameter descriptions

## 🔧 Features Implemented

### 📖 Documentation Sections
1. **Authentication** - Login, register, logout, get user
2. **Vehicles** - CRUD operations, search, filtering
3. **Documents** - Vehicle document management
4. **Maintenance** - Maintenance record tracking
5. **Exchanges** - Vehicle exchange requests
6. **Users** - User management (Admin only)

### 🔐 Security
- **Bearer Token Authentication** configured
- **Role-based access control** documented
- **Authorization examples** provided

### 📊 Schema Definitions
- **User Model** - Complete user schema
- **Vehicle Model** - Vehicle properties and relationships
- **VehicleDocument** - Document management schema
- **Maintenance** - Maintenance record schema
- **VehicleExchange** - Exchange request schema
- **Error Responses** - Standardized error formats
- **Paginated Responses** - Pagination structure

### 🎮 Interactive Features
- **Try It Out** buttons for testing endpoints
- **Authentication** - Add your bearer token once, use everywhere
- **Request Examples** - Pre-filled example requests
- **Response Examples** - Expected response formats
- **Parameter Validation** - Input validation hints

## 🚀 How to Use

### 1. **Access the Documentation**
Open `http://127.0.0.1:8000/api/documentation` in your browser

### 2. **Authenticate**
1. Click the **"Authorize"** button (🔒 icon)
2. Enter your bearer token: `Bearer {your-token}`
3. Click **"Authorize"**

### 3. **Get a Token**
1. Use the **POST /login** endpoint
2. Enter credentials:
   - Admin: `admin@example.com` / `password`
   - Chauffeur: `chauffeur@example.com` / `password`
3. Copy the token from the response
4. Use it in the authorization header

### 4. **Test Endpoints**
1. Expand any endpoint section
2. Click **"Try it out"**
3. Fill in parameters/request body
4. Click **"Execute"**
5. View the response

## 📋 Available Endpoints

### Authentication
- `POST /register` - Register new user
- `POST /login` - User login
- `POST /logout` - User logout
- `GET /user` - Get current user

### Vehicles
- `GET /vehicles` - List vehicles (with pagination & filtering)
- `POST /vehicles` - Create vehicle (Admin only)
- `GET /vehicles/{id}` - Get vehicle details
- `PUT /vehicles/{id}` - Update vehicle (Admin only)
- `DELETE /vehicles/{id}` - Archive vehicle (Admin only)
- `POST /vehicles/{id}/archive` - Archive vehicle
- `POST /vehicles/{id}/restore` - Restore vehicle
- `GET /my-vehicle` - Get chauffeur's vehicle

### Documents
- `GET /vehicles/{id}/documents` - List vehicle documents
- `POST /vehicles/{id}/documents` - Upload document
- `GET /vehicles/{id}/documents/{doc_id}` - Get document
- `PUT /vehicles/{id}/documents/{doc_id}` - Update document
- `DELETE /vehicles/{id}/documents/{doc_id}` - Delete document
- `GET /vehicles/{id}/documents/expiring` - Get expiring documents

### Maintenance
- `GET /vehicles/{id}/maintenances` - List maintenance records
- `POST /vehicles/{id}/maintenances` - Create maintenance record
- `GET /vehicles/{id}/maintenances/{maintenance_id}` - Get maintenance
- `PUT /vehicles/{id}/maintenances/{maintenance_id}` - Update maintenance
- `DELETE /vehicles/{id}/maintenances/{maintenance_id}` - Delete maintenance
- `GET /vehicles/{id}/maintenances/upcoming` - Get upcoming maintenance

### Exchanges
- `GET /exchanges` - List exchange requests
- `POST /exchanges` - Create exchange request
- `GET /exchanges/{id}` - Get exchange details
- `PUT /exchanges/{id}` - Update exchange
- `DELETE /exchanges/{id}` - Delete exchange
- `POST /exchanges/{id}/approve` - Approve exchange (Admin)
- `POST /exchanges/{id}/reject` - Reject exchange (Admin)
- `GET /my-exchanges` - Get user's exchanges

### Users (Admin Only)
- `GET /users` - List users
- `POST /users` - Create user
- `GET /users/{id}` - Get user details
- `PUT /users/{id}` - Update user
- `DELETE /users/{id}` - Delete user
- `POST /users/{id}/assign-vehicle` - Assign vehicle to user

## 🔄 Regenerating Documentation

To update the documentation after code changes:

```bash
php artisan l5-swagger:generate
```

## 📝 Adding New Endpoints

To document new endpoints, add OpenAPI annotations to your controllers:

```php
#[OA\Get(
    path: "/your-endpoint",
    summary: "Endpoint description",
    tags: ["YourTag"],
    responses: [
        new OA\Response(response: 200, description: "Success")
    ]
)]
public function yourMethod() {
    // Your code
}
```

## 🎉 Benefits

✅ **Interactive Testing** - Test all endpoints directly from the browser  
✅ **Complete Documentation** - All endpoints, parameters, and responses documented  
✅ **Authentication Ready** - Bearer token authentication integrated  
✅ **Schema Validation** - Request/response schemas defined  
✅ **Role-based Access** - Admin/Chauffeur permissions documented  
✅ **Error Handling** - Standard error response formats  
✅ **File Uploads** - Document and photo upload endpoints documented  

Your API is now fully documented and ready for development, testing, and integration! 🚀
