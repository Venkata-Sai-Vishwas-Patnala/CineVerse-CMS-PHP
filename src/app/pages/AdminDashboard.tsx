import { Link } from "react-router";
import { Home, LayoutDashboard, Film, Users, Tag, TrendingUp, Eye, Star } from "lucide-react";
import { useEffect, useState } from "react";
import { adminApi, moviesApi, categoriesApi } from "../../lib/api";
import { useAuth } from "../context/AuthContext";
import { AddMovieForm } from "../components/AddMovieForm";
import { toast } from "sonner";

export function AdminDashboard() {
  const { user }                = useAuth();
  const [tab, setTab]           = useState<"overview" | "movies" | "users" | "categories">("overview");
  const [stats, setStats]       = useState<any>(null);
  const [movies, setMovies]     = useState<any[]>([]);
  const [users, setUsers]       = useState<any[]>([]);
  const [categories, setCategories] = useState<any[]>([]);
  const [loading, setLoading]   = useState(true);
  const [showAddMovie, setShowAddMovie] = useState(false);

  useEffect(() => {
    if (user?.role !== "admin") return;
    loadData();
  }, [tab, user]);

  const loadData = async () => {
    setLoading(true);
    try {
      if (tab === "overview") {
        const res = await adminApi.stats();
        setStats(res.data);
      } else if (tab === "movies") {
        const res = await moviesApi.list({ limit: 50 });
        setMovies(res.data ?? []);
      } else if (tab === "users") {
        const res = await adminApi.users({ limit: 50 });
        setUsers(res.data ?? []);
      } else if (tab === "categories") {
        const res = await categoriesApi.list();
        setCategories(res.data ?? []);
      }
    } catch (err: any) {
      toast.error(err.message);
    } finally {
      setLoading(false);
    }
  };

  const deleteMovie = async (id: number) => {
    if (!confirm("Delete this movie?")) return;
    try {
      await moviesApi.delete(id);
      setMovies((prev) => prev.filter((m) => m.id !== id));
      toast.success("Movie deleted.");
    } catch (err: any) {
      toast.error(err.message);
    }
  };

  const deleteUser = async (id: number) => {
    if (!confirm("Delete this user?")) return;
    try {
      await adminApi.deleteUser(id);
      setUsers((prev) => prev.filter((u) => u.id !== id));
      toast.success("User deleted.");
    } catch (err: any) {
      toast.error(err.message);
    }
  };

  const deleteCategory = async (id: number) => {
    if (!confirm("Delete this category?")) return;
    try {
      await categoriesApi.delete(id);
      setCategories((prev) => prev.filter((c) => c.id !== id));
      toast.success("Category deleted.");
    } catch (err: any) {
      toast.error(err.message);
    }
  };

  if (user?.role !== "admin") {
    return (
      <div className="min-h-screen bg-black flex items-center justify-center">
        <div className="text-center">
          <h1 className="text-white text-2xl font-bold mb-4">Access Denied</h1>
          <Link to="/" className="text-red-600 hover:text-red-500">Go to Home</Link>
        </div>
      </div>
    );
  }

  return (
    <div className="min-h-screen bg-black">
      <header className="bg-zinc-900 border-b border-zinc-800 sticky top-0 z-50">
        <div className="container mx-auto px-4 py-4">
          <div className="flex items-center justify-between">
            <div className="flex items-center gap-3">
              <LayoutDashboard className="w-8 h-8 text-red-600" />
              <h1 className="text-2xl font-bold text-white">Admin Dashboard</h1>
            </div>
            <Link to="/" className="flex items-center gap-2 px-4 py-2 bg-zinc-800 text-white rounded-lg hover:bg-zinc-700 transition-colors">
              <Home className="w-5 h-5" />
              Back to Home
            </Link>
          </div>
        </div>
      </header>

      <div className="container mx-auto px-4 py-8">
        {/* Tabs */}
        <div className="flex gap-2 mb-8 bg-zinc-900 rounded-lg p-1 w-fit">
          {(["overview", "movies", "users", "categories"] as const).map((t) => (
            <button
              key={t}
              onClick={() => setTab(t)}
              className={`px-4 py-2 rounded-md text-sm font-medium transition-colors capitalize ${tab === t ? "bg-red-600 text-white" : "text-white/60 hover:text-white"}`}
            >
              {t}
            </button>
          ))}
        </div>

        {loading && (
          <div className="flex items-center justify-center py-20">
            <div className="w-12 h-12 border-4 border-red-600 border-t-transparent rounded-full animate-spin" />
          </div>
        )}

        {/* Overview Tab */}
        {!loading && tab === "overview" && stats && (
          <div className="space-y-8">
            <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
              <div className="bg-zinc-900 rounded-xl p-6 border border-zinc-800">
                <div className="flex items-center justify-between mb-2">
                  <Film className="w-8 h-8 text-red-600" />
                  <span className="text-3xl font-bold text-white">{stats.total_movies}</span>
                </div>
                <p className="text-white/60 text-sm">Total Movies</p>
              </div>
              <div className="bg-zinc-900 rounded-xl p-6 border border-zinc-800">
                <div className="flex items-center justify-between mb-2">
                  <Users className="w-8 h-8 text-blue-600" />
                  <span className="text-3xl font-bold text-white">{stats.total_users}</span>
                </div>
                <p className="text-white/60 text-sm">Total Users</p>
              </div>
              <div className="bg-zinc-900 rounded-xl p-6 border border-zinc-800">
                <div className="flex items-center justify-between mb-2">
                  <Star className="w-8 h-8 text-yellow-600" />
                  <span className="text-3xl font-bold text-white">{stats.total_reviews}</span>
                </div>
                <p className="text-white/60 text-sm">Total Reviews</p>
              </div>
              <div className="bg-zinc-900 rounded-xl p-6 border border-zinc-800">
                <div className="flex items-center justify-between mb-2">
                  <Tag className="w-8 h-8 text-green-600" />
                  <span className="text-3xl font-bold text-white">{stats.total_categories}</span>
                </div>
                <p className="text-white/60 text-sm">Categories</p>
              </div>
            </div>

            <div className="bg-zinc-900 rounded-xl p-6 border border-zinc-800">
              <h2 className="text-white text-xl font-semibold mb-4">Top Movies by Watchlist</h2>
              <div className="space-y-3">
                {stats.top_movies?.map((movie: any) => (
                  <div key={movie.id} className="flex items-center justify-between p-3 bg-zinc-800 rounded-lg">
                    <div className="flex items-center gap-3">
                      <img src={movie.poster ?? "https://via.placeholder.com/50"} alt={movie.title} className="w-12 h-16 object-cover rounded" />
                      <div>
                        <p className="text-white font-medium">{movie.title}</p>
                        <p className="text-white/50 text-sm">⭐ {movie.rating} ({movie.rating_count} ratings)</p>
                      </div>
                    </div>
                    <span className="text-white/60 text-sm">{movie.watchlist_count} in watchlist</span>
                  </div>
                ))}
              </div>
            </div>

            <div className="bg-zinc-900 rounded-xl p-6 border border-zinc-800">
              <h2 className="text-white text-xl font-semibold mb-4">Recent Reviews</h2>
              <div className="space-y-3">
                {stats.recent_reviews?.map((review: any) => (
                  <div key={review.id} className="p-3 bg-zinc-800 rounded-lg">
                    <div className="flex items-center justify-between mb-2">
                      <span className="text-white font-medium">{review.username}</span>
                      <span className="text-yellow-400">{"⭐".repeat(review.rating)}</span>
                    </div>
                    <p className="text-white/60 text-sm">{review.movie_title}</p>
                    {review.review_text && <p className="text-white/70 text-sm mt-2 line-clamp-2">{review.review_text}</p>}
                  </div>
                ))}
              </div>
            </div>
          </div>
        )}

        {/* Movies Tab */}
        {!loading && tab === "movies" && (
          <div>
            <div className="flex items-center justify-between mb-6">
              <h2 className="text-white text-2xl font-semibold">Manage Movies</h2>
              <button onClick={() => setShowAddMovie(!showAddMovie)} className="px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg transition-colors">
                {showAddMovie ? "Cancel" : "Add New Movie"}
              </button>
            </div>

            {showAddMovie && (
              <div className="mb-8">
                <AddMovieForm onSuccess={() => { setShowAddMovie(false); loadData(); }} />
              </div>
            )}

            <div className="bg-zinc-900 rounded-xl overflow-hidden border border-zinc-800">
              <table className="w-full">
                <thead className="bg-zinc-800">
                  <tr>
                    <th className="text-left text-white/70 text-sm font-medium px-4 py-3">Movie</th>
                    <th className="text-left text-white/70 text-sm font-medium px-4 py-3">Year</th>
                    <th className="text-left text-white/70 text-sm font-medium px-4 py-3">Rating</th>
                    <th className="text-left text-white/70 text-sm font-medium px-4 py-3">Status</th>
                    <th className="text-right text-white/70 text-sm font-medium px-4 py-3">Actions</th>
                  </tr>
                </thead>
                <tbody>
                  {movies.map((movie) => (
                    <tr key={movie.id} className="border-t border-zinc-800">
                      <td className="px-4 py-3">
                        <div className="flex items-center gap-3">
                          <img src={movie.poster ?? "https://via.placeholder.com/40"} alt={movie.title} className="w-10 h-14 object-cover rounded" />
                          <span className="text-white">{movie.title}</span>
                        </div>
                      </td>
                      <td className="px-4 py-3 text-white/60">{movie.release_year}</td>
                      <td className="px-4 py-3 text-white/60">⭐ {movie.rating}</td>
                      <td className="px-4 py-3">
                        <span className={`px-2 py-1 rounded-full text-xs ${movie.status === "published" ? "bg-green-600/20 text-green-400" : "bg-yellow-600/20 text-yellow-400"}`}>
                          {movie.status}
                        </span>
                      </td>
                      <td className="px-4 py-3 text-right">
                        <button onClick={() => deleteMovie(movie.id)} className="text-red-500 hover:text-red-400 text-sm">Delete</button>
                      </td>
                    </tr>
                  ))}
                </tbody>
              </table>
            </div>
          </div>
        )}

        {/* Users Tab */}
        {!loading && tab === "users" && (
          <div>
            <h2 className="text-white text-2xl font-semibold mb-6">Manage Users</h2>
            <div className="bg-zinc-900 rounded-xl overflow-hidden border border-zinc-800">
              <table className="w-full">
                <thead className="bg-zinc-800">
                  <tr>
                    <th className="text-left text-white/70 text-sm font-medium px-4 py-3">User</th>
                    <th className="text-left text-white/70 text-sm font-medium px-4 py-3">Email</th>
                    <th className="text-left text-white/70 text-sm font-medium px-4 py-3">Role</th>
                    <th className="text-left text-white/70 text-sm font-medium px-4 py-3">Joined</th>
                    <th className="text-right text-white/70 text-sm font-medium px-4 py-3">Actions</th>
                  </tr>
                </thead>
                <tbody>
                  {users.map((u) => (
                    <tr key={u.id} className="border-t border-zinc-800">
                      <td className="px-4 py-3 text-white">{u.username}</td>
                      <td className="px-4 py-3 text-white/60">{u.email}</td>
                      <td className="px-4 py-3">
                        <span className={`px-2 py-1 rounded-full text-xs ${u.role === "admin" ? "bg-red-600/20 text-red-400" : "bg-blue-600/20 text-blue-400"}`}>
                          {u.role}
                        </span>
                      </td>
                      <td className="px-4 py-3 text-white/60">{new Date(u.created_at).toLocaleDateString()}</td>
                      <td className="px-4 py-3 text-right">
                        {u.id !== user.id && (
                          <button onClick={() => deleteUser(u.id)} className="text-red-500 hover:text-red-400 text-sm">Delete</button>
                        )}
                      </td>
                    </tr>
                  ))}
                </tbody>
              </table>
            </div>
          </div>
        )}

        {/* Categories Tab */}
        {!loading && tab === "categories" && (
          <div>
            <h2 className="text-white text-2xl font-semibold mb-6">Manage Categories</h2>
            <div className="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
              {categories.map((cat) => (
                <div key={cat.id} className={`p-6 rounded-xl bg-gradient-to-br ${cat.color} relative group`}>
                  <div className="text-white font-semibold mb-1">{cat.name}</div>
                  <div className="text-white/70 text-sm">{cat.movie_count} movies</div>
                  <button
                    onClick={() => deleteCategory(cat.id)}
                    className="absolute top-2 right-2 opacity-0 group-hover:opacity-100 transition-opacity text-white/70 hover:text-red-400 text-xs"
                  >
                    Delete
                  </button>
                </div>
              ))}
            </div>
          </div>
        )}
      </div>
    </div>
  );
}
