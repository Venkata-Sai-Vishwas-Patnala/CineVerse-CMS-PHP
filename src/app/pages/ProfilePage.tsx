import { useEffect, useState } from "react";
import { Link, useNavigate } from "react-router";
import { Navbar } from "../components/Navbar";
import { Footer } from "../components/Footer";
import { useAuth } from "../context/AuthContext";
import { authApi, watchlistApi, uploadApi } from "../../lib/api";
import { toast } from "sonner";
import { Star, Trash2, User } from "lucide-react";

export function ProfilePage() {
  const { user, refresh, logout } = useAuth();
  const navigate                  = useNavigate();
  const [watchlist, setWatchlist] = useState<any[]>([]);
  const [tab, setTab]             = useState<"watchlist" | "profile" | "password">("watchlist");
  const [form, setForm]           = useState({ username: "", email: "", avatar: "" });
  const [pwForm, setPwForm]       = useState({ current_password: "", new_password: "" });
  const [saving, setSaving]       = useState(false);

  useEffect(() => {
    if (!user) { navigate("/login"); return; }
    setForm({ username: user.username, email: user.email, avatar: user.avatar ?? "" });
    watchlistApi.list().then((res) => setWatchlist(res.data ?? [])).catch(() => {});
  }, [user]);

  const removeFromWatchlist = async (movieId: number) => {
    await watchlistApi.remove(movieId);
    setWatchlist((prev) => prev.filter((m) => m.id !== movieId));
    toast.success("Removed from watchlist.");
  };

  const saveProfile = async (e: React.FormEvent) => {
    e.preventDefault();
    setSaving(true);
    try {
      await authApi.profile(form);
      await refresh();
      toast.success("Profile updated.");
    } catch (err: any) {
      toast.error(err.message);
    } finally {
      setSaving(false);
    }
  };

  const changePassword = async (e: React.FormEvent) => {
    e.preventDefault();
    setSaving(true);
    try {
      await authApi.password(pwForm.current_password, pwForm.new_password);
      toast.success("Password changed.");
      setPwForm({ current_password: "", new_password: "" });
    } catch (err: any) {
      toast.error(err.message);
    } finally {
      setSaving(false);
    }
  };

  const handleAvatarUpload = async (e: React.ChangeEvent<HTMLInputElement>) => {
    const file = e.target.files?.[0];
    if (!file) return;
    try {
      const res = await uploadApi.avatar(file);
      setForm((f) => ({ ...f, avatar: res.url }));
      await refresh();
      toast.success("Avatar updated.");
    } catch (err: any) {
      toast.error(err.message);
    }
  };

  if (!user) return null;

  return (
    <div className="min-h-screen bg-black">
      <Navbar />
      <div className="max-w-5xl mx-auto px-4 pt-24 pb-16">
        <div className="flex items-center gap-4 mb-8">
          <div className="relative">
            {user.avatar
              ? <img src={user.avatar} alt={user.username} className="w-16 h-16 rounded-full object-cover" />
              : <div className="w-16 h-16 rounded-full bg-red-600/20 flex items-center justify-center"><User className="w-8 h-8 text-red-400" /></div>
            }
          </div>
          <div>
            <h1 className="text-2xl font-bold text-white">{user.username}</h1>
            <p className="text-white/50">{user.email}</p>
            {user.role === "admin" && <span className="text-xs px-2 py-0.5 bg-red-600/20 text-red-400 rounded-full">Admin</span>}
          </div>
        </div>

        {/* Tabs */}
        <div className="flex gap-2 mb-8 bg-zinc-900 rounded-lg p-1 w-fit">
          {(["watchlist", "profile", "password"] as const).map((t) => (
            <button
              key={t}
              onClick={() => setTab(t)}
              className={`px-4 py-2 rounded-md text-sm font-medium transition-colors capitalize ${tab === t ? "bg-red-600 text-white" : "text-white/60 hover:text-white"}`}
            >
              {t === "watchlist" ? `Watchlist (${watchlist.length})` : t === "password" ? "Change Password" : "Edit Profile"}
            </button>
          ))}
        </div>

        {/* Watchlist Tab */}
        {tab === "watchlist" && (
          <div className="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-4">
            {watchlist.length === 0 && <p className="text-white/50 col-span-full text-center py-12">Your watchlist is empty.</p>}
            {watchlist.map((movie) => (
              <div key={movie.id} className="group relative bg-zinc-900 rounded-lg overflow-hidden">
                <Link to={`/movie/${movie.slug}`}>
                  <div className="aspect-[2/3] overflow-hidden">
                    <img
                      src={movie.poster ?? "https://images.unsplash.com/photo-1536440136628-849c177e76a1?w=400"}
                      alt={movie.title}
                      className="w-full h-full object-cover group-hover:scale-105 transition-transform"
                    />
                  </div>
                </Link>
                <div className="p-2">
                  <p className="text-white text-xs font-medium line-clamp-1">{movie.title}</p>
                  <div className="flex items-center justify-between mt-1">
                    <div className="flex items-center gap-1">
                      <Star className="w-3 h-3 fill-yellow-400 text-yellow-400" />
                      <span className="text-white/60 text-xs">{movie.rating}</span>
                    </div>
                    <button onClick={() => removeFromWatchlist(movie.id)} className="text-white/30 hover:text-red-500 transition-colors">
                      <Trash2 className="w-3.5 h-3.5" />
                    </button>
                  </div>
                </div>
              </div>
            ))}
          </div>
        )}

        {/* Profile Tab */}
        {tab === "profile" && (
          <form onSubmit={saveProfile} className="max-w-md space-y-4">
            <div className="flex items-center gap-4 mb-4">
              <label className="cursor-pointer">
                <span className="px-4 py-2 bg-zinc-800 text-white/70 hover:text-white rounded-lg text-sm transition-colors">Upload Avatar</span>
                <input type="file" accept="image/*" onChange={handleAvatarUpload} className="hidden" />
              </label>
              {form.avatar && <img src={form.avatar} alt="avatar" className="w-10 h-10 rounded-full object-cover" />}
            </div>
            <div>
              <label className="block text-white/70 text-sm mb-1">Username</label>
              <input type="text" value={form.username} onChange={(e) => setForm((f) => ({ ...f, username: e.target.value }))} required className="w-full px-4 py-3 bg-zinc-900 text-white rounded-lg border border-zinc-700 focus:border-red-600 focus:outline-none" />
            </div>
            <div>
              <label className="block text-white/70 text-sm mb-1">Email</label>
              <input type="email" value={form.email} onChange={(e) => setForm((f) => ({ ...f, email: e.target.value }))} required className="w-full px-4 py-3 bg-zinc-900 text-white rounded-lg border border-zinc-700 focus:border-red-600 focus:outline-none" />
            </div>
            <button type="submit" disabled={saving} className="px-6 py-3 bg-red-600 hover:bg-red-700 text-white rounded-lg font-medium transition-colors disabled:opacity-50">
              {saving ? "Saving..." : "Save Changes"}
            </button>
          </form>
        )}

        {/* Password Tab */}
        {tab === "password" && (
          <form onSubmit={changePassword} className="max-w-md space-y-4">
            <div>
              <label className="block text-white/70 text-sm mb-1">Current Password</label>
              <input type="password" value={pwForm.current_password} onChange={(e) => setPwForm((f) => ({ ...f, current_password: e.target.value }))} required className="w-full px-4 py-3 bg-zinc-900 text-white rounded-lg border border-zinc-700 focus:border-red-600 focus:outline-none" />
            </div>
            <div>
              <label className="block text-white/70 text-sm mb-1">New Password</label>
              <input type="password" value={pwForm.new_password} onChange={(e) => setPwForm((f) => ({ ...f, new_password: e.target.value }))} required minLength={6} className="w-full px-4 py-3 bg-zinc-900 text-white rounded-lg border border-zinc-700 focus:border-red-600 focus:outline-none" />
            </div>
            <button type="submit" disabled={saving} className="px-6 py-3 bg-red-600 hover:bg-red-700 text-white rounded-lg font-medium transition-colors disabled:opacity-50">
              {saving ? "Saving..." : "Change Password"}
            </button>
          </form>
        )}
      </div>
      <Footer />
    </div>
  );
}
