# Using ONLY Apache (No Vite Dev Server)

## Option 1: Production Build (Recommended)

### Step 1: Build the React App
```bash
npm install
npm run build
```

This creates a `dist/` folder with compiled HTML/CSS/JS.

### Step 2: Update .htaccess
The `.htaccess` already handles serving the built files. Just make sure Apache serves from the project root.

### Step 3: Access via Apache
Open: **http://localhost/CineVerse/**

The built React app will be served by Apache along with the PHP backend.

---

## Option 2: Development Without Vite

If you want to develop without running `npm run dev`, you can:

### Use Apache + PHP Only (No React Build)

1. Keep XAMPP running
2. Build once: `npm run build`
3. Every time you change React code, rebuild: `npm run build`
4. Access: **http://localhost/CineVerse/**

**Downside**: No hot reload, must rebuild after every change.

---

## Option 3: Skip Frontend Build (Use CDN React)

If you don't want to use npm at all, you'd need to:
- Rewrite the entire frontend to use plain JavaScript or CDN React
- This would require significant changes

---

## ⚡ Recommended Workflow:

### Development:
```bash
# Terminal 1: XAMPP (Apache + MySQL running)
# Terminal 2: npm run dev
```
- Frontend: http://localhost:5173 (with hot reload)
- Backend: http://localhost/api (via proxy)

### Production:
```bash
npm run build
```
- Everything: http://localhost/CineVerse/
- Apache serves both frontend and backend

---

## 🎯 TL;DR:

**You NEED npm to:**
- Install React dependencies
- Build the TypeScript/React code into browser-ready JavaScript

**You DON'T need Vite dev server running if:**
- You build once with `npm run build`
- You're okay rebuilding after every change
- You access via Apache: http://localhost/CineVerse/

**Best approach:**
1. Run `npm install` once (installs dependencies)
2. Run `npm run build` (creates production files)
3. Use Apache only: http://localhost/CineVerse/
4. Rebuild when you change frontend code

The React/TypeScript code MUST be compiled to JavaScript - that's what npm does.
