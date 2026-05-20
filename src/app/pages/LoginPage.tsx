import { useState } from "react";
import { useNavigate, Link } from "react-router";
import { Film } from "lucide-react";
import { useAuth } from "../context/AuthContext";
import { toast } from "sonner";

export function LoginPage() {
  const { login, register }     = useAuth();
  const navigate                = useNavigate();
  const [tab, setTab]           = useState<"login" | "register">("login");
  const [loading, setLoading]   = useState(false);
  const [form, setForm]         = useState({ username: "", email: "", password: "" });

  const set = (k: string, v: string) => setForm((f) => ({ ...f, [k]: v }));

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    setLoading(true);
    try {
      if (tab === "login") {
        await login(form.email, form.password);
        toast.success("Welcome back!");
      } else {
        await register(form.username, form.email, form.password);
        toast.success("Account created!");
      }
      navigate("/");
    } catch (err: any) {
      toast.error(err.message);
    } finally {
      setLoading(false);
    }
  };

  return (
    <div className="min-h-screen bg-black flex items-center justify-center px-4">
      <div className="w-full max-w-md">
        <Link to="/" className="flex items-center justify-center gap-2 mb-8">
          <Film className="w-10 h-10 text-red-600" />
          <span className="text-3xl font-bold text-white">Cineverse</span>
        </Link>

        <div className="bg-zinc-900 rounded-2xl p-8 border border-zinc-800">
          <div className="flex mb-6 bg-zinc-800 rounded-lg p-1">
            {(["login", "register"] as const).map((t) => (
              <button
                key={t}
                onClick={() => setTab(t)}
                className={`flex-1 py-2 rounded-md text-sm font-medium transition-colors capitalize ${tab === t ? "bg-red-600 text-white" : "text-white/60 hover:text-white"}`}
              >
                {t === "login" ? "Sign In" : "Sign Up"}
              </button>
            ))}
          </div>

          <form onSubmit={handleSubmit} className="space-y-4">
            {tab === "register" && (
              <div>
                <label className="block text-white/70 text-sm mb-1">Username</label>
                <input
                  type="text"
                  value={form.username}
                  onChange={(e) => set("username", e.target.value)}
                  required
                  className="w-full px-4 py-3 bg-zinc-800 text-white rounded-lg border border-zinc-700 focus:border-red-600 focus:outline-none"
                  placeholder="johndoe"
                />
              </div>
            )}
            <div>
              <label className="block text-white/70 text-sm mb-1">Email</label>
              <input
                type="email"
                value={form.email}
                onChange={(e) => set("email", e.target.value)}
                required
                className="w-full px-4 py-3 bg-zinc-800 text-white rounded-lg border border-zinc-700 focus:border-red-600 focus:outline-none"
                placeholder="you@example.com"
              />
            </div>
            <div>
              <label className="block text-white/70 text-sm mb-1">Password</label>
              <input
                type="password"
                value={form.password}
                onChange={(e) => set("password", e.target.value)}
                required
                minLength={6}
                className="w-full px-4 py-3 bg-zinc-800 text-white rounded-lg border border-zinc-700 focus:border-red-600 focus:outline-none"
                placeholder="••••••••"
              />
            </div>
            <button
              type="submit"
              disabled={loading}
              className="w-full py-3 bg-red-600 hover:bg-red-700 text-white rounded-lg font-medium transition-colors disabled:opacity-50"
            >
              {loading ? "Please wait..." : tab === "login" ? "Sign In" : "Create Account"}
            </button>
          </form>

          {tab === "login" && (
            <p className="text-center text-white/40 text-sm mt-4">
              Default admin: <span className="text-white/60">admin@cineverse.com / password</span>
            </p>
          )}
        </div>
      </div>
    </div>
  );
}
