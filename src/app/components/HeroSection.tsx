import { Play, Star } from "lucide-react";
import { Link } from "react-router";
import { Button } from "./ui/button";
import { ImageWithFallback } from "./figma/ImageWithFallback";
import { useEffect, useState } from "react";
import { moviesApi, reviewsApi } from "../../lib/api";
import { useAuth } from "../context/AuthContext";
import { toast } from "sonner";

export function HeroSection() {
  const { user }                    = useAuth();
  const [movie, setMovie]           = useState<any>(null);
  const [rating, setRating]         = useState(0);
  const [hoverRating, setHoverRating] = useState(0);
  const [submitting, setSubmitting] = useState(false);

  useEffect(() => {
    moviesApi.featured().then((res) => setMovie(res.data)).catch(() => {});
  }, []);

  const handleRate = async (star: number) => {
    if (!user) { toast.error("Please log in to rate movies."); return; }
    setRating(star);
    setSubmitting(true);
    try {
      await reviewsApi.create({ movie_id: movie.id, rating: star });
      toast.success(`Rated ${star}/5!`);
    } catch (e: any) {
      toast.error(e.message);
    } finally {
      setSubmitting(false);
    }
  };

  if (!movie) {
    return (
      <div className="relative h-[85vh] w-full bg-zinc-950 flex items-center justify-center">
        <div className="w-12 h-12 border-4 border-red-600 border-t-transparent rounded-full animate-spin" />
      </div>
    );
  }

  const posterSrc = movie.poster?.startsWith("/uploads")
    ? movie.poster
    : movie.poster ?? "https://images.unsplash.com/photo-1692528673971-356bc4800482?w=1080";

  return (
    <div className="relative h-[85vh] w-full overflow-hidden">
      <div className="absolute inset-0">
        <ImageWithFallback src={posterSrc} alt={movie.title} className="w-full h-full object-cover" />
        <div className="absolute inset-0 bg-gradient-to-r from-black via-black/80 to-transparent" />
        <div className="absolute inset-0 bg-gradient-to-t from-black via-transparent to-transparent" />
      </div>

      <div className="relative h-full max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex items-center">
        <div className="max-w-2xl space-y-6">
          <h1 className="text-5xl md:text-7xl font-bold text-white tracking-tight">{movie.title}</h1>

          <div className="flex items-center gap-4 text-sm text-white/80">
            <span className="px-2 py-1 border border-white/30 rounded">{movie.release_year}</span>
            <span>•</span>
            <span>{movie.duration}</span>
            {movie.categories?.[0] && (
              <>
                <span>•</span>
                <span className="px-3 py-1 bg-red-600 rounded-full text-white">{movie.categories[0].name}</span>
              </>
            )}
          </div>

          <p className="text-lg text-white/90 leading-relaxed max-w-xl line-clamp-3">{movie.description}</p>

          <div className="flex items-center gap-3">
            <span className="text-white/70">Rate this movie:</span>
            <div className="flex gap-1">
              {[1, 2, 3, 4, 5].map((star) => (
                <button
                  key={star}
                  onClick={() => handleRate(star)}
                  onMouseEnter={() => setHoverRating(star)}
                  onMouseLeave={() => setHoverRating(0)}
                  disabled={submitting}
                  className="transition-transform hover:scale-110 disabled:opacity-50"
                >
                  <Star className={`w-6 h-6 ${star <= (hoverRating || rating) ? "fill-yellow-400 text-yellow-400" : "text-white/30"}`} />
                </button>
              ))}
            </div>
            {rating > 0 && <span className="text-yellow-400 font-semibold">{rating}/5</span>}
          </div>

          <div className="flex items-center gap-4 pt-4">
            <Link to={`/movie/${movie.slug}`}>
              <Button size="lg" className="bg-red-600 hover:bg-red-700 text-white">
                <Play className="w-5 h-5 mr-2 fill-white" />
                Read Reviews
              </Button>
            </Link>
            <Link to={`/movie/${movie.slug}`}>
              <Button size="lg" variant="outline" className="border-white/30 text-white hover:bg-white/10">
                More Info
              </Button>
            </Link>
          </div>
        </div>
      </div>
    </div>
  );
}
