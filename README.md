# CineVerse - Full-Stack Movie Review CMS

A complete movie review and content management system built with **React + Vite + TypeScript** frontend and **PHP + MySQL** backend.

## 🚀 Features

### Frontend (React + Vite + TypeScript + Tailwind + shadcn/ui)
- ✅ Modern responsive UI with dark theme
- ✅ Movie browsing with search and filters
- ✅ Featured hero section with dynamic content
- ✅ Trending movies carousel
- ✅ Category/genre filtering
- ✅ Detailed movie pages with reviews
- ✅ User authentication (login/register)
- ✅ User profiles with watchlist
- ✅ Star rating system
- ✅ Review submission and management

### Backend (PHP + MySQL)
- ✅ RESTful API architecture
- ✅ Complete CRUD operations for movies
- ✅ User authentication with sessions
- ✅ Role-based access control (User/Admin)
- ✅ File upload handling (posters, backdrops, avatars)
- ✅ Review system with rating aggregation
- ✅ Watchlist management
- ✅ Category/genre management
- ✅ Streaming platform availability tracking
- ✅ Admin dashboard with statistics
- ✅ User management
- ✅ Bulk operations

## Screenshots
### Homepage
<img width="1434" height="910" alt="Screenshot 2026-05-25 002114" src="https://github.com/user-attachments/assets/287efa6e-8a88-44db-af97-0114d5ecd48b" />

### Overview page
<img width="1432" height="903" alt="Screenshot 2026-05-25 002142" src="https://github.com/user-attachments/assets/9094ced5-4a27-4ae4-8ad3-e6aa3238700a" />

### Trending page
<img width="1432" height="821" alt="Screenshot 2026-05-25 002221" src="https://github.com/user-attachments/assets/069e837c-6378-436e-bbda-8ce152e3ee41" />

### Genre page
<img width="1426" height="910" alt="Screenshot 2026-05-25 002242" src="https://github.com/user-attachments/assets/66197609-2624-4568-a21a-7035c60c2cf6" />

### Admin Dashboard
<img width="1431" height="911" alt="Screenshot 2026-05-25 002159" src="https://github.com/user-attachments/assets/0464f3d0-0215-4de6-b5be-f7c8c7a10144" />

## 📋 Prerequisites

- **XAMPP** (Apache + MySQL + PHP 8.0+)
- **Node.js** 18+ and npm/pnpm
- Modern web browser

## 🛠️ Installation & Setup

### 1. Database Setup

1. Start **XAMPP** and ensure Apache and MySQL are running
2. Open **phpMyAdmin** (http://localhost/phpmyadmin)
3. Import the database schema:
   ```bash
   # Navigate to the project directory
   cd c:\xampp\htdocs\CineVerse
   
   # Import SQL file via phpMyAdmin or command line:
   mysql -u root -p < backend/setup.sql
   ```
   Or manually execute `backend/setup.sql` in phpMyAdmin

4. Default admin credentials:
   - Email: `admin@cineverse.com`
   - Password: `password`

### 2. Backend Configuration

The backend is already configured for XAMPP defaults. If you need to change database credentials:

Edit `backend/config/db.php`:
```php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'cineverse');
```

### 3. Frontend Setup

```bash
# Install dependencies
npm install
# or
pnpm install

# Start development server
npm run dev
# or
pnpm dev
```

The app will be available at: **http://localhost:5173**

### 4. File Permissions

Ensure the uploads directory is writable:
```bash
# Windows (run as administrator)
icacls "c:\xampp\htdocs\CineVerse\public\uploads" /grant Everyone:F /T
```

## 📁 Project Structure

```
CineVerse/
├── backend/                    # PHP Backend
│   ├── api/                   # API endpoints
│   │   ├── auth.php          # Authentication
│   │   ├── movies.php        # Movies CRUD
│   │   ├── reviews.php       # Reviews CRUD
│   │   ├── categories.php    # Categories CRUD
│   │   ├── watchlist.php     # Watchlist management
│   │   ├── upload.php        # File uploads
│   │   └── admin.php         # Admin operations
│   ├── config/               # Configuration
│   │   ├── db.php           # Database connection
│   │   └── auth.php         # Auth helpers
│   ├── index.php            # API router
│   └── setup.sql            # Database schema
├── src/                      # React Frontend
│   ├── app/
│   │   ├── components/      # React components
│   │   ├── pages/           # Page components
│   │   ├── context/         # React context (Auth)
│   │   └── routes.tsx       # Route definitions
│   ├── lib/
│   │   └── api.ts          # API client
│   └── styles/             # CSS styles
├── public/
│   └── uploads/            # Uploaded files
│       ├── posters/
│       ├── backdrops/
│       └── avatars/
├── .htaccess               # Apache rewrite rules
├── vite.config.ts          # Vite configuration
└── package.json            # Dependencies
```

## 🔌 API Endpoints

### Authentication
- `POST /api/auth/register` - Register new user
- `POST /api/auth/login` - Login
- `POST /api/auth/logout` - Logout
- `GET /api/auth/me` - Get current user
- `PUT /api/auth/profile` - Update profile
- `PUT /api/auth/password` - Change password

### Movies
- `GET /api/movies` - List movies (with filters)
- `GET /api/movies/featured` - Get featured movie
- `GET /api/movies/trending` - Get trending movies
- `GET /api/movies/{id}` - Get movie by ID
- `GET /api/movies/slug?slug={slug}` - Get movie by slug
- `POST /api/movies` - Create movie (admin)
- `PUT /api/movies/{id}` - Update movie (admin)
- `DELETE /api/movies/{id}` - Delete movie (admin)

### Reviews
- `GET /api/reviews?movie_id={id}` - List reviews for movie
- `POST /api/reviews` - Create/update review
- `DELETE /api/reviews/{id}` - Delete review

### Categories
- `GET /api/categories` - List all categories
- `POST /api/categories` - Create category (admin)
- `PUT /api/categories/{id}` - Update category (admin)
- `DELETE /api/categories/{id}` - Delete category (admin)

### Watchlist
- `GET /api/watchlist` - Get user's watchlist
- `POST /api/watchlist` - Add to watchlist
- `DELETE /api/watchlist/{movie_id}` - Remove from watchlist

### Admin
- `GET /api/admin/stats` - Dashboard statistics
- `GET /api/admin/users` - List users
- `POST /api/admin/users` - Create user
- `PUT /api/admin/users/{id}` - Update user
- `DELETE /api/admin/users/{id}` - Delete user
- `GET /api/admin/platforms` - List platforms
- `POST /api/admin/platforms` - Create platform
- `DELETE /api/admin/platforms/{id}` - Delete platform
- `POST /api/admin/bulk` - Bulk operations

### Upload
- `POST /api/upload/poster` - Upload poster image
- `POST /api/upload/backdrop` - Upload backdrop image
- `POST /api/upload/avatar` - Upload avatar image

## 👤 User Roles

### Regular User
- Browse and search movies
- View movie details
- Submit reviews and ratings
- Manage personal watchlist
- Update profile

### Admin
- All user permissions
- Add/edit/delete movies
- Manage users
- Manage categories
- View dashboard statistics
- Bulk operations

## 🎨 Tech Stack

**Frontend:**
- React 18
- TypeScript
- Vite
- React Router 7
- Tailwind CSS 4
- shadcn/ui components
- Lucide icons
- Sonner (toast notifications)

**Backend:**
- PHP 8.0+
- MySQL 8.0+
- PDO for database
- Session-based authentication
- RESTful API design

## 🔒 Security Features

- Password hashing with bcrypt
- SQL injection prevention (prepared statements)
- CSRF protection via session
- File upload validation
- Role-based access control
- Input sanitization

## 📝 Default Data

The database comes pre-seeded with:
- 1 admin user
- 10 categories
- 5 streaming platforms
- 6 sample movies with relationships

## 🐛 Troubleshooting

**Database connection failed:**
- Ensure MySQL is running in XAMPP
- Check database credentials in `backend/config/db.php`
- Verify database `cineverse` exists

**File upload errors:**
- Check folder permissions on `public/uploads/`
- Verify PHP `upload_max_filesize` and `post_max_size` in php.ini

**API 404 errors:**
- Ensure `.htaccess` is in the root directory
- Enable `mod_rewrite` in Apache (XAMPP has it enabled by default)
- Check that Apache is serving from `c:\xampp\htdocs\CineVerse`

**CORS errors:**
- Verify Vite proxy is configured in `vite.config.ts`
- Check that backend allows origin `http://localhost:5173`

## 📄 License

This project is open source and available under the MIT License.

## 🤝 Contributing

Contributions, issues, and feature requests are welcome!

---

**Built with ❤️ for movie enthusiasts**
