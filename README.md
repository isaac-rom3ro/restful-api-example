# RESTful API Example with JWT Authentication

A complete RESTful API implementation in PHP with JWT (JSON Web Token) authentication, featuring secure token management, user isolation, and a modern web client interface.

## 🏗️ Architecture Overview

This project implements a secure task management API with the following key components:

- **JWT Authentication**: Secure token-based authentication with access and refresh tokens
- **User Isolation**: Each user can only access their own tasks
- **RESTful Design**: Follows REST principles with proper HTTP methods and status codes
- **Security Features**: Password hashing, SQL injection prevention, token rotation
- **Modern Client**: HTML/JavaScript client demonstrating the complete authentication flow

## 📁 Project Structure

```
restful-api-example/
├── api/                          # API endpoints
│   ├── bootstrap.php             # Application initialization
│   ├── index.php                 # Main API router
│   ├── login.php                 # User authentication
│   ├── logout.php                # User logout
│   ├── refresh.php               # Token refresh
│   └── tokens.php                # Token generation logic
├── src/                          # Core classes
│   ├── Auth.php                  # Authentication handler
│   ├── Database.php              # Database connection
│   ├── ErrorHandler.php          # Error management
│   ├── JWTCodec.php              # JWT encoding/decoding
│   ├── RefreshTokenGateway.php   # Refresh token management
│   ├── TaskController.php        # Task business logic
│   ├── TaskGateway.php           # Task data access
│   ├── UserGateway.php           # User data access
│   ├── InvalidSignatureException.php
│   └── TokenExpiredException.php
├── register.php                  # User registration page
├── example-client.html           # Demo client interface
├── delete-expired-refresh-tokens.php  # Cleanup script
├── composer.json                 # Dependencies
└── README.md                     # This file
```

## 🔐 Authentication Flow

### 1. User Registration
- Users register via `register.php`
- Passwords are securely hashed using `password_hash()`
- Each user gets a unique API key

### 2. Login Process
```
POST /api/login.php
{
    "username": "user@example.com",
    "password": "password123"
}
```

**Response:**
```json
{
    "access_token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9...",
    "refresh_token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9..."
}
```

### 3. Token Management
- **Access Token**: Short-lived (20 seconds) for API requests
- **Refresh Token**: Long-lived (5 days) for getting new access tokens
- **Token Rotation**: Old refresh tokens are invalidated when new ones are issued

### 4. API Authentication
All API requests require the access token in the Authorization header:
```
Authorization: Bearer <access_token>
```

### 5. Token Refresh
When access tokens expire, clients can get new ones:
```
POST /api/refresh.php
{
    "token": "<refresh_token>"
}
```

### 6. Logout
Users can logout by invalidating their refresh token:
```
POST /api/logout.php
{
    "token": "<refresh_token>"
}
```

## 🎯 API Endpoints

### Tasks Resource
All endpoints require authentication via JWT access token.

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/api/tasks` | Get all tasks for authenticated user |
| POST | `/api/tasks` | Create a new task |
| GET | `/api/tasks/{id}` | Get specific task |
| PATCH | `/api/tasks/{id}` | Update specific task |
| DELETE | `/api/tasks/{id}` | Delete specific task |

### Task Data Structure
```json
{
    "id": 1,
    "name": "Complete project documentation",
    "priority": 1,
    "is_completed": false,
    "user_id": 1
}
```

### Example API Usage

#### Create a Task
```bash
curl -X POST https://localhost/api/tasks \
  -H "Authorization: Bearer <access_token>" \
  -H "Content-Type: application/json" \
  -d '{"name": "New task", "priority": 1}'
```

#### Get All Tasks
```bash
curl -X GET https://localhost/api/tasks \
  -H "Authorization: Bearer <access_token>"
```

#### Update a Task
```bash
curl -X PATCH https://localhost/api/tasks/1 \
  -H "Authorization: Bearer <access_token>" \
  -H "Content-Type: application/json" \
  -d '{"is_completed": true}'
```

## 🖥️ Example Client

The `example-client.html` file provides a complete demonstration of the authentication flow:

### Features
- **Login Form**: Username/password authentication
- **Task Management**: View, create, and manage tasks
- **Token Handling**: Automatic token refresh when access tokens expire
- **Logout**: Secure logout with token invalidation

### Client Flow
1. **Login**: User enters credentials and receives tokens
2. **Token Storage**: Tokens stored in localStorage
3. **API Requests**: All requests include access token
4. **Token Refresh**: Automatic refresh when tokens expire
5. **Logout**: Tokens removed and invalidated

### JavaScript Example
```javascript
// Login
const response = await fetch('https://localhost/api/login.php', {
    method: 'POST',
    body: JSON.stringify({
        username: 'user@example.com',
        password: 'password123'
    })
});

// Store tokens
const { access_token, refresh_token } = await response.json();
localStorage.setItem("access_token", access_token);
localStorage.setItem("refresh_token", refresh_token);

// Make authenticated request
const tasksResponse = await fetch('https://localhost/api/tasks', {
    headers: {
        "Authorization": "Bearer " + access_token
    }
});
```

## 🛡️ Security Features

### 1. Password Security
- Passwords hashed using `password_hash()` with `PASSWORD_DEFAULT`
- Secure comparison using `password_verify()`

### 2. JWT Security
- HMAC-SHA256 signing for token integrity
- Base64URL encoding for URL-safe transmission
- Short-lived access tokens (20 seconds)
- Token rotation on refresh

### 3. Database Security
- Prepared statements prevent SQL injection
- User isolation ensures data privacy
- Refresh tokens hashed before storage

### 4. Input Validation
- Request method validation
- Required field validation
- Data type validation (e.g., priority must be integer)

### 5. Error Handling
- Consistent JSON error responses
- No sensitive information in error messages
- Proper HTTP status codes

## 🗄️ Database Schema

### Users Table
```sql
CREATE TABLE user (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    username VARCHAR(255) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    api_key VARCHAR(32) UNIQUE NOT NULL
);
```

### Tasks Table
```sql
CREATE TABLE task (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    priority INT,
    is_completed BOOLEAN DEFAULT FALSE,
    user_id INT NOT NULL,
    FOREIGN KEY (user_id) REFERENCES user(id)
);
```

### Refresh Tokens Table
```sql
CREATE TABLE refresh_token (
    id INT AUTO_INCREMENT PRIMARY KEY,
    token_hash VARCHAR(255) NOT NULL,
    expires_at INT NOT NULL
);
```

## 🚀 Setup Instructions

### 1. Prerequisites
- PHP 8.0 or higher
- MySQL/MariaDB database
- Web server (Apache/Nginx)
- Composer

### 2. Installation
```bash
# Clone the repository
git clone <repository-url>
cd restful-api-example

# Install dependencies
composer install

# Create .env file
cp .env.example .env
```

### 3. Environment Configuration
Create a `.env` file with your database credentials:
```env
DB_HOST=localhost
DB_NAME=your_database_name
DB_USER=your_database_user
DB_PASSWORD=your_database_password
SECRET_KEY=your_secret_key_here
```

### 4. Database Setup
```sql
-- Create database
CREATE DATABASE your_database_name;

-- Create tables (see schema above)
-- Run the SQL commands from the Database Schema section
```

### 5. Web Server Configuration
Configure your web server to serve the project and ensure the `api/` directory is accessible.

### 6. Cleanup Script
Set up a cron job to clean expired refresh tokens:
```bash
# Run every hour
0 * * * * php /path/to/delete-expired-refresh-tokens.php
```

## 🔧 Configuration

### Token Expiration Times
- Access tokens: 20 seconds (in `api/tokens.php`)
- Refresh tokens: 5 days (in `api/tokens.php`)

### Database Connection
Configure in `.env` file or modify `src/Database.php`

### Error Reporting
Development: `ini_set("display_errors", "On")` in `api/bootstrap.php`
Production: Set to `"Off"` and configure proper logging

## 📝 API Response Codes

| Code | Description |
|------|-------------|
| 200 | OK - Request successful |
| 201 | Created - Resource created successfully |
| 400 | Bad Request - Invalid request data |
| 401 | Unauthorized - Authentication required/failed |
| 404 | Not Found - Resource not found |
| 405 | Method Not Allowed - HTTP method not supported |
| 422 | Unprocessable Entity - Validation errors |
| 500 | Internal Server Error - Server error |

## 🔍 Testing

### Using the Example Client
1. Open `example-client.html` in a web browser
2. Register a new user via `register.php`
3. Login with your credentials
4. Test task management features

### Using curl
```bash
# Login
curl -X POST https://localhost/api/login.php \
  -H "Content-Type: application/json" \
  -d '{"username":"test","password":"test123"}'

# Use returned access token for subsequent requests
curl -X GET https://localhost/api/tasks \
  -H "Authorization: Bearer <access_token>"
```

## 🛠️ Development

### Adding New Endpoints
1. Create new PHP file in `api/` directory
2. Include `bootstrap.php` for initialization
3. Add authentication check
4. Implement business logic using appropriate gateway classes

### Adding New Resources
1. Create new gateway class in `src/`
2. Create new controller class in `src/`
3. Add routing logic in `api/index.php`
4. Update database schema if needed

## 📚 Key Concepts

### JWT (JSON Web Tokens)
- **Header**: Algorithm and token type
- **Payload**: User data and expiration
- **Signature**: HMAC hash for integrity

### Token Rotation
- Old refresh tokens invalidated when new ones issued
- Prevents token reuse attacks
- Maintains session security

### User Isolation
- All database queries include `user_id` filter
- Users can only access their own data
- Prevents unauthorized data access

### Prepared Statements
- Prevents SQL injection attacks
- Proper data type handling
- Secure parameter binding

## 🤝 Contributing

1. Fork the repository
2. Create a feature branch
3. Make your changes
4. Add tests if applicable
5. Submit a pull request

## 📄 License

This project is open source and available under the [MIT License](LICENSE).

## 🆘 Support

For issues and questions:
1. Check the documentation
2. Review the example client code
3. Test with curl commands
4. Check server error logs

---

**Note**: This is a demonstration project. For production use, consider additional security measures such as rate limiting, HTTPS enforcement, and comprehensive logging.
