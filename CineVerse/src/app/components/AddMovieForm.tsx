import { useState, useEffect } from "react";
import { toast } from "sonner";
import { Plus, X, Upload } from "lucide-react";
import { moviesApi, categoriesApi, uploadApi, adminApi } from "../../lib/api";

interface AddMovieFormProps {
  onSuccess?: () => void;
}

export function AddMovieForm({ onSuccess }: AddMovieFormProps) {
  const [formData, setFormData] = useState({
    title: "",
    description: "",
    director: "",
    cast: "",
    release_year: "",
    duration: "",
    rating: 0,
    poster: "",
    backdrop: "",
    trailer_url: "",
    is_featured: false,
    is_trending: false,
    status: "published",
  });

  const [categories, setCategories]   = useState<any[]>([]);
  const [platforms, setPlatforms]     = useState<any[]>([]);
  const [selectedCats, setSelectedCats] = useState<number[]>([]);
  const [selectedPlats, setSelectedPlats] = useState<any[]>([]);
  const [uploading, setUploading]     = useState(false);

  useEffect(() => {
    categoriesApi.list().then((res) => setCategories(res.data ?? [])).catch(() => {});
    adminApi.platforms().then((res) => setPlatforms(res.data ?? [])).catch(() => {});
  }, []);

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();

    if (!formData.title || !formData.description || selectedCats.length === 0) {
      toast.error("Please fill in all required fields");
      return;
    }

    try {
      await moviesApi.create({
        ...formData,
        categories: selectedCats,
        platforms: selectedPlats,
      });
      toast.success(`Movie "${formData.title}" added successfully!`);

      setFormData({
        title: "",
        description: "",
        director: "",
        cast: "",
        release_year: "",
        duration: "",
        rating: 0,
        poster: "",
        backdrop: "",
        trailer_url: "",
        is_featured: false,
        is_trending: false,
        status: "published",
      });
      setSelectedCats([]);
      setSelectedPlats([]);
      onSuccess?.();
    } catch (err: any) {
      toast.error(err.message);
    }
  };

  const handleFileUpload = async (e: React.ChangeEvent<HTMLInputElement>, type: "poster" | "backdrop") => {
    const file = e.target.files?.[0];
    if (!file) return;
    setUploading(true);
    try {
      const res = type === "poster" ? await uploadApi.poster(file) : await uploadApi.backdrop(file);
      setFormData((f) => ({ ...f, [type]: res.url }));
      toast.success(`${type} uploaded!`);
    } catch (err: any) {
      toast.error(err.message);
    } finally {
      setUploading(false);
    }
  };

  const toggleCategory = (id: number) => {
    setSelectedCats((prev) => prev.includes(id) ? prev.filter((c) => c !== id) : [...prev, id]);
  };

  const togglePlatform = (id: number) => {
    setSelectedPlats((prev) => {
      const exists = prev.find((p) => p.id === id);
      if (exists) return prev.filter((p) => p.id !== id);
      return [...prev, { id, available: 1 }];
    });
  };

  return (
    <form onSubmit={handleSubmit} className="max-w-3xl mx-auto space-y-6 bg-zinc-900 p-8 rounded-xl border border-zinc-800">
      <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
          <label className="block text-white mb-2 font-medium">Movie Title *</label>
          <input
            type="text"
            value={formData.title}
            onChange={(e) => setFormData({ ...formData, title: e.target.value })}
            className="w-full px-4 py-3 bg-zinc-800 text-white rounded-lg border border-zinc-700 focus:border-red-600 focus:outline-none"
            placeholder="Enter movie title"
            required
          />
        </div>

        <div>
          <label className="block text-white mb-2 font-medium">Director</label>
          <input
            type="text"
            value={formData.director}
            onChange={(e) => setFormData({ ...formData, director: e.target.value })}
            className="w-full px-4 py-3 bg-zinc-800 text-white rounded-lg border border-zinc-700 focus:border-red-600 focus:outline-none"
            placeholder="Director name"
          />
        </div>
      </div>

      <div>
        <label className="block text-white mb-2 font-medium">Description *</label>
        <textarea
          value={formData.description}
          onChange={(e) => setFormData({ ...formData, description: e.target.value })}
          className="w-full px-4 py-3 bg-zinc-800 text-white rounded-lg border border-zinc-700 focus:border-red-600 focus:outline-none resize-none"
          placeholder="Enter movie description"
          rows={4}
          required
        />
      </div>

      <div>
        <label className="block text-white mb-2 font-medium">Cast (comma-separated)</label>
        <input
          type="text"
          value={formData.cast}
          onChange={(e) => setFormData({ ...formData, cast: e.target.value })}
          className="w-full px-4 py-3 bg-zinc-800 text-white rounded-lg border border-zinc-700 focus:border-red-600 focus:outline-none"
          placeholder="Actor 1, Actor 2, Actor 3"
        />
      </div>

      <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div>
          <label className="block text-white mb-2 font-medium">Release Year</label>
          <input
            type="number"
            value={formData.release_year}
            onChange={(e) => setFormData({ ...formData, release_year: e.target.value })}
            className="w-full px-4 py-3 bg-zinc-800 text-white rounded-lg border border-zinc-700 focus:border-red-600 focus:outline-none"
            placeholder="2024"
            min="1900"
            max="2030"
          />
        </div>

        <div>
          <label className="block text-white mb-2 font-medium">Duration</label>
          <input
            type="text"
            value={formData.duration}
            onChange={(e) => setFormData({ ...formData, duration: e.target.value })}
            className="w-full px-4 py-3 bg-zinc-800 text-white rounded-lg border border-zinc-700 focus:border-red-600 focus:outline-none"
            placeholder="120 min"
          />
        </div>

        <div>
          <label className="block text-white mb-2 font-medium">Status</label>
          <select
            value={formData.status}
            onChange={(e) => setFormData({ ...formData, status: e.target.value })}
            className="w-full px-4 py-3 bg-zinc-800 text-white rounded-lg border border-zinc-700 focus:border-red-600 focus:outline-none"
          >
            <option value="published">Published</option>
            <option value="draft">Draft</option>
          </select>
        </div>
      </div>

      <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
          <label className="block text-white mb-2 font-medium">Poster Image</label>
          <label className="flex items-center justify-center gap-2 px-4 py-3 bg-zinc-800 text-white rounded-lg border border-zinc-700 hover:border-red-600 cursor-pointer transition-colors">
            <Upload className="w-5 h-5" />
            {uploading ? "Uploading..." : formData.poster ? "Change Poster" : "Upload Poster"}
            <input type="file" accept="image/*" onChange={(e) => handleFileUpload(e, "poster")} className="hidden" disabled={uploading} />
          </label>
          {formData.poster && <img src={formData.poster} alt="poster" className="mt-2 w-20 h-28 object-cover rounded" />}
        </div>

        <div>
          <label className="block text-white mb-2 font-medium">Backdrop Image</label>
          <label className="flex items-center justify-center gap-2 px-4 py-3 bg-zinc-800 text-white rounded-lg border border-zinc-700 hover:border-red-600 cursor-pointer transition-colors">
            <Upload className="w-5 h-5" />
            {uploading ? "Uploading..." : formData.backdrop ? "Change Backdrop" : "Upload Backdrop"}
            <input type="file" accept="image/*" onChange={(e) => handleFileUpload(e, "backdrop")} className="hidden" disabled={uploading} />
          </label>
          {formData.backdrop && <img src={formData.backdrop} alt="backdrop" className="mt-2 w-32 h-20 object-cover rounded" />}
        </div>
      </div>

      <div>
        <label className="block text-white mb-2 font-medium">Trailer URL</label>
        <input
          type="url"
          value={formData.trailer_url}
          onChange={(e) => setFormData({ ...formData, trailer_url: e.target.value })}
          className="w-full px-4 py-3 bg-zinc-800 text-white rounded-lg border border-zinc-700 focus:border-red-600 focus:outline-none"
          placeholder="https://youtube.com/..."
        />
      </div>

      <div>
        <label className="block text-white mb-2 font-medium">Categories *</label>
        <div className="flex flex-wrap gap-2">
          {categories.map((cat) => (
            <button
              key={cat.id}
              type="button"
              onClick={() => toggleCategory(cat.id)}
              className={`px-3 py-1.5 rounded-full text-sm transition-colors ${selectedCats.includes(cat.id) ? "bg-red-600 text-white" : "bg-zinc-800 text-white/60 hover:text-white"}`}
            >
              {cat.name}
            </button>
          ))}
        </div>
      </div>

      <div>
        <label className="block text-white mb-2 font-medium">Streaming Platforms</label>
        <div className="flex flex-wrap gap-2">
          {platforms.map((plat) => (
            <button
              key={plat.id}
              type="button"
              onClick={() => togglePlatform(plat.id)}
              className={`px-3 py-1.5 rounded-full text-sm transition-colors ${selectedPlats.some((p) => p.id === plat.id) ? "bg-green-600 text-white" : "bg-zinc-800 text-white/60 hover:text-white"}`}
            >
              {plat.logo} {plat.name}
            </button>
          ))}
        </div>
      </div>

      <div className="flex items-center gap-6">
        <label className="flex items-center gap-2 text-white cursor-pointer">
          <input
            type="checkbox"
            checked={formData.is_featured}
            onChange={(e) => setFormData({ ...formData, is_featured: e.target.checked })}
            className="w-4 h-4 accent-red-600"
          />
          Featured Movie
        </label>
        <label className="flex items-center gap-2 text-white cursor-pointer">
          <input
            type="checkbox"
            checked={formData.is_trending}
            onChange={(e) => setFormData({ ...formData, is_trending: e.target.checked })}
            className="w-4 h-4 accent-red-600"
          />
          Trending
        </label>
      </div>

      <button
        type="submit"
        disabled={uploading}
        className="w-full px-6 py-4 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors font-medium text-lg disabled:opacity-50"
      >
        Add Movie
      </button>
    </form>
  );
}
