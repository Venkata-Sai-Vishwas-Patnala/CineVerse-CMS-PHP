# 🚀 Quick Setup Guide

Follow these steps to get CineVerse running:

## Step 1: Start XAMPP
1. Open XAMPP Control Panel
2. Start **Apache** and **MySQL**

## Step 2: Create Database
1. Open browser and go to: http://localhost/phpmyadmin
2. Click "New" to create a database
3. Name it: `cineverse`
4. Click "Create"
5. Select the `cineverse` database
6. Click "Import" tab
7. Choose file: `backend/setup.sql`
8. Click "Go" to import

## Step 3: Install Frontend Dependencies
Open terminal in project folder and run:
```bash
npm install
```

## Step 4: Start Development Server
```bash
npm run dev
```

## Step 5: Access the Application
Open browser and go to: **http://localhost:5173**

## Step 6: Login as Admin
- Email: `admin@cineverse.com`
- Password: `password`

## 🎉 You're Done!

### What to do next:
1. Go to Admin Dashboard (click Admin in navbar)
2. Add new movies with the form
3. Upload poster and backdrop images
4. Create categories and manage users
5. Browse movies as a regular user
6. Submit reviews and ratings
7. Add movies to your watchlist

### Test User Accounts:
You can create new users by clicking "Sign Up" or use the admin account.

### Troubleshooting:
- If you see "Database connection failed", make sure MySQL is running in XAMPP
- If uploads don't work, check that `public/uploads/` folder exists
- If API calls fail, ensure Apache is running and serving from the correct directory

### File Upload Locations:
- Movie posters: `public/uploads/posters/`
- Movie backdrops: `public/uploads/backdrops/`
- User avatars: `public/uploads/avatars/`

### API Base URL:
All API calls go through: `http://localhost/api/`

The Vite dev server automatically proxies these to your PHP backend.

---

**Need help?** Check the main README.md for detailed documentation.
