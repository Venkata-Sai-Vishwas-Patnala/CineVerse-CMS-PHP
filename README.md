# 🎬 CineVerse - Full-Stack Movie Review CMS

> A modern, feature-rich movie review and content management system built with cutting-edge technologies.

[![License: MIT](https://img.shields.io/badge/License-MIT-yellow.svg)](https://opensource.org/licenses/MIT)
[![React](https://img.shields.io/badge/React-18-blue?logo=react)](https://react.dev)
[![PHP](https://img.shields.io/badge/PHP-8.0+-purple?logo=php)](https://www.php.net)
[![MySQL](https://img.shields.io/badge/MySQL-8.0+-blue?logo=mysql)](https://www.mysql.com)
[![TypeScript](https://img.shields.io/badge/TypeScript-5.0+-blue?logo=typescript)](https://www.typescriptlang.org)
[![Tailwind CSS](https://img.shields.io/badge/Tailwind-4.0-06B6D4?logo=tailwindcss)](https://tailwindcss.com)

---

## 📸 Project Showcase

### 🏠 Home Page - Featured Movies & Trending Section
![CineVerse Home Page](https://via.placeholder.com/1200x600?text=CineVerse+Home+Page)
*Browse featured movies with dynamic hero section and trending carousel*

---

### 🎥 Movie Details Page
![Movie Details](https://via.placeholder.com/1200x600?text=Movie+Details+Page)
*Comprehensive movie information with reviews, ratings, and streaming availability*

---

### 👨‍💼 Admin Dashboard
![Admin Dashboard](https://via.placeholder.com/1200x600?text=Admin+Dashboard)
*Manage movies, users, categories, and view platform statistics*

---

### 👤 User Profile & Watchlist
![User Profile](https://via.placeholder.com/1200x600?text=User+Profile+%26+Watchlist)
*Personalized watchlist and profile management*

---

## ✨ Key Features

### 🎥 Frontend Features
- **Modern UI** - Dark theme with responsive design using Tailwind CSS
- **Movie Discovery** - Browse, search, and filter movies by category
- **Featured Content** - Dynamic hero section with featured movies
- **Trending Section** - Carousel showcasing trending movies
- **Movie Details** - Comprehensive information with reviews and ratings
- **User Authentication** - Secure login/register system
- **Watchlist** - Save favorite movies for later
- **Review System** - Submit and manage movie reviews with star ratings
- **User Profiles** - Personalized user dashboard

### 🔧 Backend Features
- **RESTful API** - Clean, well-documented API architecture
- **Complete CRUD** - Full movie, review, and category management
- **Authentication** - Session-based user authentication
- **Role-Based Access** - User and Admin roles with permissions
- **File Uploads** - Handle posters, backdrops, and avatars
- **Review Aggregation** - Automatic rating calculations
- **Watchlist Management** - User-specific watchlist operations
- **Streaming Platforms** - Track movie availability across platforms
- **Admin Dashboard** - Statistics and management tools
- **Bulk Operations** - Batch operations for admins

---

## 🚀 Quick Start

### Prerequisites
- **XAMPP** (Apache + MySQL + PHP 8.0+)
- **Node.js** 18+ and npm/pnpm
- Modern web browser

### Installation

#### 1️⃣ Clone the Repository
```bash
git clone https://github.com/Venkata-Sai-Vishwas-Patnala/CineVerse-CMS-PHP.git
cd CineVerse-CMS-PHP
```

#### 2️⃣ Database Setup
```bash
# Start XAMPP and ensure Apache & MySQL are running
# Open phpMyAdmin: http://localhost/phpmyadmin

# Import the database schema
mysql -u root -p < backend/setup.sql
```

**Default Admin Credentials:**
- Email: `admin@cineverse.com`
- Password: `password`

#### 3️⃣ Frontend Setup
```bash
# Install dependencies
npm install

# Build for production
npm run build

# Or start development server
npm run dev
```

#### 4️⃣ Access the Application
- **Production**: http://localhost/
- **Development**: http://localhost:5173

---

## 📁 Project Structure

```
CineVerse/
├── 📂 backend/                    # PHP Backend
│   ├── 📂 api/                   # API Endpoints
│   │   ├── auth.php              # Authentication (login, register, profile)
│   │   ├── movies.php            # Movie CRUD & search
│   │   ├── reviews.php           # Review management
│   │   ├── categories.php        # Category management
│   │   ├── watchlist.php         # Watchlist operations
│   │   ├── upload.php            # File upload handling
│   │   └── admin.php             # Admin operations
│   ├── 📂 config/                # Configuration
│   │   ├── db.php                # Database connection
│   │   └── auth.php              # Authentication helpers
│   ├── index.php                 # API router
│   └── setup.sql                 # Database schema
│
├── 📂 src/                       # React Frontend
│   ├── 📂 app/
│   │   ├── 📂 components/        # Reusable components
│   │   │   ├── Navbar.tsx
│   │   │   ├── HeroSection.tsx
│   │   │   ├── MovieCard.tsx
│   │   │   ├── Categories.tsx
│   │   │   ├── TrendingMovies.tsx
│   │   │   ├── AddMovieForm.tsx
│   │   │   └── Footer.tsx
│   │   ├── 📂 pages/             # Page components
│   │   │   ├── HomePage.tsx
│   │   │   ├── MovieDetails.tsx
│   │   │   ├── LoginPage.tsx
│   │   │   ├── ProfilePage.tsx
│   │   │   ├── AdminDashboard.tsx
│   │   │   └── NotFound.tsx
│   │   ├── 📂 context/           # React Context
│   │   │   └── AuthContext.tsx
│   │   └── routes.tsx            # Route definitions
│   ├── 📂 lib/
│   │   └── api.ts                # API client
│   └── 📂 styles/                # CSS & Tailwind
│
├── 📂 public/
│   └── 📂 uploads/               # User uploads
│       ├── posters/
│       ├── backdrops/
│       └── avatars/
│
├── .htaccess                     # Apache routing
├── vite.config.ts                # Vite configuration
└── package.json                  # Dependencies
```

---

## 🔌 API Documentation

### Authentication Endpoints
| Method | Endpoint | Description | Auth |
|--------|----------|-------------|------|
| POST | `/api/auth/register` | Register new user | ❌ |
| POST | `/api/auth/login` | User login | ❌ |
| POST | `/api/auth/logout` | User logout | ✅ |
| GET | `/api/auth/me` | Get current user | ✅ |
| PUT | `/api/auth/profile` | Update profile | ✅ |
| PUT | `/api/auth/password` | Change password | ✅ |

### Movie Endpoints
| Method | Endpoint | Description | Auth |
|--------|----------|-------------|------|
| GET | `/api/movies` | List movies (with filters) | ❌ |
| GET | `/api/movies/featured` | Get featured movie | ❌ |
| GET | `/api/movies/trending` | Get trending movies | ❌ |
| GET | `/api/movies/{id}` | Get movie by ID | ❌ |
| POST | `/api/movies` | Create movie | ✅ Admin |
| PUT | `/api/movies/{id}` | Update movie | ✅ Admin |
| DELETE | `/api/movies/{id}` | Delete movie | ✅ Admin |

### Review Endpoints
| Method | Endpoint | Description | Auth |
|--------|----------|-------------|------|
| GET | `/api/reviews?movie_id={id}` | List reviews | ❌ |
| POST | `/api/reviews` | Create/update review | ✅ |
| DELETE | `/api/reviews/{id}` | Delete review | ✅ |

### Category Endpoints
| Method | Endpoint | Description | Auth |
|--------|----------|-------------|------|
| GET | `/api/categories` | List all categories | ❌ |
| POST | `/api/categories` | Create category | ✅ Admin |
| PUT | `/api/categories/{id}` | Update category | ✅ Admin |
| DELETE | `/api/categories/{id}` | Delete category | ✅ Admin |

### Watchlist Endpoints
| Method | Endpoint | Description | Auth |
|--------|----------|-------------|------|
| GET | `/api/watchlist` | Get user's watchlist | ✅ |
| POST | `/api/watchlist` | Add to watchlist | ✅ |
| DELETE | `/api/watchlist/{movie_id}` | Remove from watchlist | ✅ |

### Admin Endpoints
| Method | Endpoint | Description | Auth |
|--------|----------|-------------|------|
| GET | `/api/admin/stats` | Dashboard statistics | ✅ Admin |
| GET | `/api/admin/users` | List users | ✅ Admin |
| POST | `/api/admin/users` | Create user | ✅ Admin |
| PUT | `/api/admin/users/{id}` | Update user | ✅ Admin |
| DELETE | `/api/admin/users/{id}` | Delete user | ✅ Admin |

---

## 👥 User Roles & Permissions

### 👤 Regular User
- ✅ Browse and search movies
- ✅ View detailed movie information
- ✅ Submit and manage reviews
- ✅ Rate movies (1-5 stars)
- ✅ Create and manage watchlist
- ✅ Update profile information
- ✅ View streaming availability

### 👨💼 Admin User
- ✅ All user permissions
- ✅ Add/edit/delete movies
- ✅ Manage user accounts
- ✅ Manage categories and genres
- ✅ View dashboard statistics
- ✅ Manage streaming platforms
- ✅ Perform bulk operations
- ✅ Moderate reviews

---

## 🛠️ Technology Stack

### Frontend
| Technology | Version | Purpose |
|-----------|---------|---------|
| **React** | 18 | UI framework |
| **TypeScript** | 5.0+ | Type safety |
| **Vite** | 6.0+ | Build tool & dev server |
| **React Router** | 7 | Client-side routing |
| **Tailwind CSS** | 4.0 | Styling |
| **shadcn/ui** | Latest | UI components |
| **Lucide Icons** | Latest | Icon library |
| **Sonner** | Latest | Toast notifications |

### Backend
| Technology | Version | Purpose |
|-----------|---------|---------|
| **PHP** | 8.0+ | Server-side language |
| **MySQL** | 8.0+ | Database |
| **PDO** | Built-in | Database abstraction |
| **bcrypt** | Built-in | Password hashing |
| **Apache** | 2.4+ | Web server |

---

## 🔒 Security Features

- 🔐 **Password Hashing** - bcrypt for secure password storage
- 🛡️ **SQL Injection Prevention** - Prepared statements with PDO
- 🔑 **CSRF Protection** - Session-based tokens
- 📁 **File Validation** - Secure file upload handling with type checking
- 👮 **Role-Based Access Control** - Permission-based operations
- 🧹 **Input Sanitization** - Clean user inputs
- 🔒 **Session Security** - Secure session management
- 📝 **CORS Headers** - Proper cross-origin handling

---

## 📊 Database Schema

### Tables (8 Total)
| Table | Purpose |
|-------|---------|
| **users** | User accounts and profiles |
| **movies** | Movie information and metadata |
| **categories** | Movie genres/categories |
| **platforms** | Streaming platforms (Netflix, Prime, etc.) |
| **reviews** | User reviews and ratings |
| **watchlist** | User watchlist items |
| **movie_categories** | Movie-category relationships |
| **movie_platforms** | Movie-platform availability |

### Pre-seeded Data
- 1 admin user (`admin@cineverse.com`)
- 10 movie categories (Action, Drama, Comedy, etc.)
- 5 streaming platforms (Netflix, Amazon Prime, Disney+, HBO Max, Hulu)
- 6 sample movies with complete relationships

---

## ⚙️ Configuration

### Database Configuration
Edit `backend/config/db.php`:
```php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'cineverse');
```

### Environment Variables
Create `.env` file in root:
```env
VITE_API_URL=http://localhost
VITE_APP_NAME=CineVerse
```

### File Permissions
```bash
# Windows (run as administrator)
icacls "c:\xampp\htdocs\public\uploads" /grant Everyone:F /T
```

---

## 🐛 Troubleshooting

### Database Connection Failed
- ✅ Ensure MySQL is running in XAMPP
- ✅ Check credentials in `backend/config/db.php`
- ✅ Verify `cineverse` database exists
- ✅ Check MySQL port (default: 3306)

### File Upload Errors
- ✅ Check folder permissions on `public/uploads/`
- ✅ Verify PHP `upload_max_filesize` in php.ini
- ✅ Ensure write permissions on upload directories
- ✅ Check file size limits (default: 5MB)

### API 404 Errors
- ✅ Verify `.htaccess` is in root directory
- ✅ Enable `mod_rewrite` in Apache
- ✅ Check Apache DocumentRoot configuration
- ✅ Verify API endpoint URLs

### CORS Issues
- ✅ Verify Vite proxy in `vite.config.ts`
- ✅ Check backend CORS headers
- ✅ Ensure correct origin in requests
- ✅ Check browser console for errors

### Frontend Build Issues
- ✅ Clear `node_modules` and reinstall: `rm -rf node_modules && npm install`
- ✅ Clear Vite cache: `rm -rf dist`
- ✅ Check Node.js version: `node --version` (should be 18+)

---

## 🚀 Deployment

### Production Build
```bash
npm run build
```

### Serve Production Build
```bash
# Copy dist/ contents to Apache root
# Access via http://localhost/
```

### Docker Deployment (Optional)
```bash
# Build Docker image
docker build -t cineverse .

# Run container
docker run -p 80:80 -p 3306:3306 cineverse
```

---

## 📄 License

This project is open source and available under the **MIT License**.

See [LICENSE](LICENSE) file for details.

---

## 🤝 Contributing

Contributions, issues, and feature requests are welcome!

### How to Contribute
1. Fork the repository
2. Create your feature branch (`git checkout -b feature/AmazingFeature`)
3. Commit your changes (`git commit -m 'Add some AmazingFeature'`)
4. Push to the branch (`git push origin feature/AmazingFeature`)
5. Open a Pull Request

### Development Guidelines
- Follow existing code style
- Add tests for new features
- Update documentation
- Keep commits atomic and descriptive

---

## 📧 Support & Contact

- **Issues**: [GitHub Issues](https://github.com/Venkata-Sai-Vishwas-Patnala/CineVerse-CMS-PHP/issues)
- **Email**: support@cineverse.com
- **Author**: [Venkata Sai Vishwas Patnala](https://github.com/Venkata-Sai-Vishwas-Patnala)

---

## 🎉 Acknowledgments

- Built with ❤️ for movie enthusiasts
- Inspired by modern streaming platforms
- Thanks to all contributors and supporters
- Special thanks to the React, PHP, and open-source communities

---

## 📈 Project Statistics

- **Total Files**: 500+
- **Lines of Code**: 10,000+
- **API Endpoints**: 30+
- **Database Tables**: 8
- **Components**: 20+
- **Pages**: 6

---

**Made with ❤️ by [Venkata Sai Vishwas Patnala](https://github.com/Venkata-Sai-Vishwas-Patnala)**

⭐ If you find this project helpful, please consider giving it a star!

---

## 🔗 Quick Links

- [GitHub Repository](https://github.com/Venkata-Sai-Vishwas-Patnala/CineVerse-CMS-PHP)
- [Live Demo](http://localhost/)
- [API Documentation](./API.md)
- [Contributing Guide](./CONTRIBUTING.md)
