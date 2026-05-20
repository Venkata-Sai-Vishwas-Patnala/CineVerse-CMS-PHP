import { Link } from "react-router";

export function NotFound() {
  return (
    <div className="min-h-screen bg-black flex items-center justify-center">
      <div className="text-center">
        <h1 className="text-6xl font-bold text-red-600 mb-4">404</h1>
        <p className="text-white text-xl mb-8">Page not found</p>
        <Link
          to="/"
          className="px-6 py-3 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors"
        >
          Go Home
        </Link>
      </div>
    </div>
  );
}
