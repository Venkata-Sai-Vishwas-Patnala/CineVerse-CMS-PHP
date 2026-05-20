import { useParams, Link } from "react-router";
import { Star, ArrowLeft, Play, Plus, Check, Trash2 } from "lucide-react";
import { Navbar } from "../components/Navbar";
import { Footer } from "../components/Footer";
import { ImageWithFallback } from "../components/figma/ImageWithFallback";
import { useEffect, useState } from "react";
import { moviesApi, reviewsApi, watchlistApi } from "../../lib/api";
import { useAuth } from "../context/AuthContext";
import { toast } from "sonner";

export function MovieDetails() {
  const { movieId }                   = useParams();
  const { user }                      = useAuth();
  const [movie, setMovie]             = useState<any>(null);
  const [reviews, setReviews]         = useState<any[]>([]);
  const [reviewTotal, setReviewTotal] = useState(0);
  const [loading, setLoading]         = useState(true);
  const [inWatchlist, setInWatchlist] = useState(false);
  const [userRating, setUserRating]   = useState(0);
  const [hoverRating, setHoverRating] = useState(0);
  const [reviewText, setReviewText]   = useState("");
  const [submitting, setSubmitting]   = useState(false);

  useEffect(() => {
    if (!movieId) return;
    setLoading(true);
    moviesApi.bySlug(movieId)
      .then((res) => {
        setMovie(res.data);
        return reviewsApi.list(res.data.id);
      })
      .then((res) => {
        setReviews(res.data ?? []);
        setReviewTotal(res.total ?? 0);
      })
      .catch(() => {})
      .finally(() => setLoading(false));
  }, [movieId]);

  useEffect(() => {
    if (!user || !movie) return;
    watchlistApi.list().then((res) => {
      setInWatchlist((res.data ?? []).some((m: any) => m.id === movie.id));
    }).catch(() => {});
  }, [user, movie]);

  const toggleWatchlist = async () => {
    if (!user) { toast.error("Please log in first."); return; }
    try {
      if (inWatchlist) {
        await watchlistApi.remove(movie.id);
        setInWatchlist(false);
        toast.success("Removed from watchlist.");
      } else {
        await watchlistApi.add(movie.id);
        setInWatchlist(true);
        toast.success("Added to watchlist.");
      }
    } catch (e: any) {
      toast.error(e.message);
    }
  };

  const submitReview = async (e: React.FormEvent) => {
    e.preventDefault();
    if (!user) { toast.error("Please log in to review."); return; }
    if (!userRating) { toast.error("Please select a rating."); return; }
    setSubmitting(true);
    try {
      const res = await reviewsApi.create({ movie_id: movie.id, rating: userRating, review_text: reviewText });
      setReviews((prev) => {
        const exists = prev.findIndex((r) => r.user_id === user.id);
        if (exists >= 0) { const updated = [...prev]; updated[exists] = res.data; return updated; }
        return [res.data, ...prev];
      });
      toast.success("Review submitted!");
      setReviewText("");
    } catch (e: any) {
      toast.error(e.message);
    } finally {
      setSubmitting(false);
    }
  };

  const deleteReview = async (reviewId: number) => {
    try {
      await reviewsApi.delete(reviewId);
      setReviews((prev) => prev.filter((r) => r.id !== reviewId));
      toast.success("Review deleted.");
    } catch (e: any) {
      toast.error(e.message);
    }
  };

  if (loading) {
    return (
      <div className="min-h-screen bg-black flex items-center justify-center">
        <div className="w-12 h-12 border-4 border-red-600 border-t-transparent rounded-full animate-spin" />
      </div>
    );
  }

  if (!movie) {
    return (
      <div className="min-h-screen bg-black">
        <Navbar />
        <div className="pt-32 pb-20 px-4 text-center">
          <h1 className="text-white text-3xl font-bold mb-4">Movie Not Found</h1>
          <Link to="/" className="text-red-600 hover:text-red-500">Go back to home</Link>
        </div>
        <Footer />
      </div>
    );
  }

  const posterSrc = movie.poster?.startsWith("/uploads") ? movie.poster : movie.poster ?? "https://images.unsplash.com/photo-1536440136628-849c177e76a1?w=800";

  return (
    <div className="min-h-screen bg-black">
      <Navbar />

      <div className="relative pt-16">
        <div className="absolute inset-0 overflow-hidden">
          <ImageWithFallback src={movie.backdrop ?? posterSrc} alt={movie.title} className="w-full h-full object-cover opacity-20 blur-xl" />
          <div className="absolute inset-0 bg-gradient-to-b from-black via-black/80 to-black" />
        </div>

        <div className="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
          <Link to="/" className="inline-flex items-center gap-2 text-white/70 hover:text-white transition-colors mb-8">
            <ArrowLeft className="w-5 h-5" />
            Back to Home
          </Link>

          <div className="grid md:grid-cols-3 gap-8">
            <div className="md:col-span-1">
              <div className="relative aspect-[2/3] rounded-lg overflow-hidden shadow-2xl">
                <ImageWithFallback src={posterSrc} alt={movie.title} className="w-full h-full object-cover" />
              </div>
            </div>

            <div className="md:col-span-2 space-y-6">
              <div>
                <h1 className="text-4xl md:text-5xl font-bold text-white mb-2">{movie.title}</h1>
                <div className="flex flex-wrap items-center gap-4 text-white/70">
                  <span>{movie.release_year}</span>
                  <span>•</span>
                  <span>{movie.duration}</span>
                  <span>•</span>
                  <div className="flex items-center gap-2">
                    <Star className="w-5 h-5 fill-yellow-400 text-yellow-400" />
                    <span className="text-white font-semibold">{movie.rating}/5</span>
                    <span className="text-white/50 text-sm">({movie.rating_count} ratings)</span>
                  </div>
                </div>
              </div>

              <div className="flex flex-wrap gap-2">
                {(movie.categories ?? []).map((cat: any) => (
                  <span key={cat.id} className="px-4 py-2 bg-red-600/20 border border-red-600 text-red-500 rounded-full text-sm font-medium">
                    {cat.name}
                  </span>
                ))}
              </div>

              <div>
                <h2 className="text-xl font-semibold text-white mb-3">Overview</h2>
                <p className="text-white/80 leading-relaxed">{movie.description}</p>
              </div>

              <div className="grid md:grid-cols-2 gap-6">
                <div>
                  <h3 className="text-white font-semibold mb-2">Director</h3>
                  <p className="text-white/70">{movie.director}</p>
                </div>
                <div>
                  <h3 className="text-white font-semibold mb-2">Cast</h3>
                  <p className="text-white/70">{Array.isArray(movie.cast) ? movie.cast.join(", ") : movie.cast}</p>
                </div>
              </div>

              <div className="flex flex-wrap gap-4 pt-4">
                <button className="flex items-center gap-2 px-6 py-3 bg-red-600 hover:bg-red-700 text-white rounded-lg transition-colors font-medium">
                  <Play className="w-5 h-5" />
                  Watch Now
                </button>
                <button
                  onClick={toggleWatchlist}
                  className={`flex items-center gap-2 px-6 py-3 rounded-lg transition-colors font-medium ${inWatchlist ? "bg-green-600/20 border border-green-600 text-green-400" : "bg-white/10 hover:bg-white/20 text-white"}`}
                >
                  {inWatchlist ? <Check className="w-5 h-5" /> : <Plus className="w-5 h-5" />}
                  {inWatchlist ? "In Watchlist" : "Add to Watchlist"}
                </button>
              </div>

              {/* Streaming Platforms */}
              {(movie.platforms ?? []).filter((p: any) => p.available).length > 0 && (
                <div className="pt-6 border-t border-white/10">
                  <h2 className="text-xl font-semibold text-white mb-4">Available on Streaming Platforms</h2>
                  <div className="grid grid-cols-2 md:grid-cols-3 gap-4">
                    {movie.platforms.filter((p: any) => p.available).map((platform: any) => (
                      <div
                        key={platform.id}
                        className="p-4 rounded-lg border bg-zinc-900 border-zinc-700 hover:border-red-600 transition-all"
                      >
                        <div className="flex items-center gap-3">
                          <span className="text-3xl">{platform.logo}</span>
                          <div>
                            <p className="text-white font-medium text-sm">{platform.name}</p>
                          </div>
                        </div>
                      </div>
                    ))}
                  </div>
                </div>
              )}
            </div>
          </div>

          {/* Reviews Section */}
          <div className="mt-16 border-t border-white/10 pt-12">
            <h2 className="text-2xl font-bold text-white mb-8">Reviews ({reviewTotal})</h2>

            {/* Submit Review */}
            <form onSubmit={submitReview} className="bg-zinc-900 rounded-xl p-6 mb-8 space-y-4">
              <h3 className="text-white font-semibold">Write a Review</h3>
              <div className="flex items-center gap-2">
                <span className="text-white/60 text-sm">Your rating:</span>
                {[1, 2, 3, 4, 5].map((star) => (
                  <button
                    key={star}
                    type="button"
                    onClick={() => setUserRating(star)}
                    onMouseEnter={() => setHoverRating(star)}
                    onMouseLeave={() => setHoverRating(0)}
                    className="transition-transform hover:scale-110"
                  >
                    <Star className={`w-6 h-6 ${star <= (hoverRating || userRating) ? "fill-yellow-400 text-yellow-400" : "text-white/30"}`} />
                  </button>
                ))}
              </div>
              <textarea
                value={reviewText}
                onChange={(e) => setReviewText(e.target.value)}
                placeholder="Share your thoughts about this movie..."
                rows={3}
                className="w-full px-4 py-3 bg-zinc-800 text-white rounded-lg border border-zinc-700 focus:border-red-600 focus:outline-none resize-none"
              />
              <button
                type="submit"
                disabled={submitting}
                className="px-6 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg transition-colors font-medium disabled:opacity-50"
              >
                {submitting ? "Submitting..." : "Submit Review"}
              </button>
            </form>

            {/* Reviews List */}
            <div className="space-y-4">
              {reviews.length === 0 && (
                <p className="text-white/50 text-center py-8">No reviews yet. Be the first to review!</p>
              )}
              {reviews.map((review) => (
                <div key={review.id} className="bg-zinc-900 rounded-xl p-6">
                  <div className="flex items-start justify-between mb-3">
                    <div className="flex items-center gap-3">
                      <div className="w-9 h-9 rounded-full bg-red-600/20 flex items-center justify-center text-red-400 font-bold text-sm">
                        {review.username?.[0]?.toUpperCase()}
                      </div>
                      <div>
                        <p className="text-white font-medium">{review.username}</p>
                        <p className="text-white/40 text-xs">{new Date(review.created_at).toLocaleDateString()}</p>
                      </div>
                    </div>
                    <div className="flex items-center gap-3">
                      <div className="flex gap-0.5">
                        {[1, 2, 3, 4, 5].map((s) => (
                          <Star key={s} className={`w-4 h-4 ${s <= review.rating ? "fill-yellow-400 text-yellow-400" : "text-white/20"}`} />
                        ))}
                      </div>
                      {(user?.id === review.user_id || user?.role === "admin") && (
                        <button onClick={() => deleteReview(review.id)} className="text-white/40 hover:text-red-500 transition-colors">
                          <Trash2 className="w-4 h-4" />
                        </button>
                      )}
                    </div>
                  </div>
                  {review.review_text && <p className="text-white/70 leading-relaxed">{review.review_text}</p>}
                </div>
              ))}
            </div>
          </div>
        </div>
      </div>

      <Footer />
    </div>
  );
}
