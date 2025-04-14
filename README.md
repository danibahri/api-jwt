# API Native PHP with JWT Authentication

A simple native PHP API implementation with JWT authentication for managing student data.

## Requirements

- PHP 7.4 or higher
- MySQL 5.7 or higher
- Composer for dependency management

## Setup Instructions

### 1. Prepare Environment

- Ensure you have PHP and MySQL installed on your system
- Set up a virtual host that points the `localhost` domain to the `public_html` folder
- Import `database.sql` into MySQL to create the necessary database and tables

### 2. Install Dependencies

```bash
composer install
```

This will install the Firebase JWT library required for authentication.

### 3. File Structure

- PHP files are stored in the `public_html` folder
- HTML files are stored in the `public_html/ui` folder

### 4. Usage Flow

- Open `http://localhost/ui/index.html` for the main page
- Register a new user account at `http://localhost/ui/register.html`
- Login at `http://localhost/ui/login.html` to obtain a JWT token
- Use the dashboard at `http://localhost/ui/dashboard.html` to manage student data
- JWT tokens are stored in localStorage and sent in the header when accessing the API

### 5. API Endpoints

| Method | Endpoint                              | Description                 |
| ------ | ------------------------------------- | --------------------------- |
| GET    | `http://localhost/api/mahasiswa`      | Retrieve all student data   |
| GET    | `http://localhost/api/mahasiswa/{id}` | Retrieve student data by ID |
| POST   | `http://localhost/api/mahasiswa`      | Add new student data        |
| PUT    | `http://localhost/api/mahasiswa/{id}` | Update student data         |
| DELETE | `http://localhost/api/mahasiswa/{id}` | Delete student data         |

### 6. Token Usage

- All API endpoints require a JWT token
- Token must be sent in the header: `Authorization: Bearer {token}`
- Tokens are valid for 1 hour before expiring

## Features

This system implements the following features:

- User registration and login with encrypted passwords
- Authentication using JSON Web Tokens (JWT)
- Protected API endpoints with token validation
- CRUD operations for student data
- Simple UI for student data management

## License

[MIT License](LICENSE)
