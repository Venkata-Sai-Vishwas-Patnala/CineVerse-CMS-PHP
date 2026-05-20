import { Film, Search, User, Shield, LogOut, X } from "lucide-react";
import { Link, useLocation, useNavigate } from "react-router";
import { Button } from "./ui/button";
import { useAuth } from "../context/AuthContext";
import { useState } from "react";
import { toast } from "sonner";

export function Navbar() {
  const location              = useLocation();
  const navigate              = useNavigate();
  const { user, logout }      = useAuth();
  const isAdminPage           = location.pathname.startsWith("/admin");
  const [search, setSearch]   = useState("");
  const [showSearch, setShowSearch] = useState(false);

  const handleLogout = async () => {
    await logout();
    toast.success("Logged out successfully.");
    navigate("/");
  };

  const handleSearch = (e: React.FormEvent) => {
    e.preventDefault();
    if (search.trim()) {
      navigate(`/?search=${encodeURIComponent(search.trim())}`);
      setShowSearch(false);
      setSearch("");
    }
  };

  return (
    <nav className="fixed top-0 left-0 right-0 z-50 bg-black/95 backdrop-blur-sm border-b border-white/10">
      <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div className="flex items-center justify-between h-16">
          <Link to="/" className="flex items-center gap-2">
            <Film className="w-8 h-8 text-red-600" />
            <span className="text-2xl font-bold text-white">Cineverse</span>
          </Link>

          <div className="hidden md:flex items-center gap-8">
            <Link to="/" className="text-white hover:text-red-500 transition-colors">Home</Link>
            <Link to="/?trending=1" className="text-white/70 hover:text-white transition-colors">Movies</Link>
            <Link to="/?section=reviews" className="text-white/70 hover:text-white transition-colors">Reviews</Link>
            <Link to="/?section=genres" className="text-white/70 hover:text-white transition-colors">Genres</Link>
            {user?.role === "admin" && (
              <Link
                to="/admin"
                className={`flex items-center gap-2 transition-colors ${isAdminPage ? "text-red-500" : "text-white/70 hover:text-white"}`}
              >
                <Shield className="w-4 h-4" />
                Admin
              </Link>
            )}
          </div>

          <div className="flex items-center gap-4">
            {showSearch ? (
              <form onSubmit={handleSearch} className="flex items-center gap-2">
                <input
                  autoFocus
                  value={search}
                  onChange={(e) => setSearch(e.target.value)}
                  placeholder="Search movies..."
                  className="px-3 py-1.5 bg-zinc-800 text-white rounded-lg border border-zinc-700 focus:border-red-600 focus:outline-none text-sm w-48"
                />
                <button type="button" onClick={() => setShowSearch(false)} className="text-white/70 hover:text-white">
                  <X className="w-4 h-4" />
                </button>
              </form>
            ) : (
              <button onClick={() => setShowSearch(true)} className="text-white/70 hover:text-white transition-colors">
                <Search className="w-5 h-5" />
              </button>
            )}

            {user ? (
              <div className="flex items-center gap-3">
                <Link to="/profile" className="flex items-center gap-2 text-white/80 hover:text-white transition-colors text-sm">
                  {user.avatar
                    ? <img src={user.avatar} alt={user.username} className="w-7 h-7 rounded-full object-cover" />
                    : <User className="w-5 h-5" />
                  }
                  <span className="hidden md:inline">{user.username}</span>
                </Link>
                <button onClick={handleLogout} className="text-white/60 hover:text-red-500 transition-colors">
                  <LogOut className="w-5 h-5" />
                </button>
              </div>
            ) : (
              <Link to="/login">
                <Button className="bg-red-600 hover:bg-red-700 text-white">
                  <User className="w-4 h-4 mr-2" />
                  Login
                </Button>
              </Link>
            )}
          </div>
        </div>
      </div>
    </nav>
  );
}
