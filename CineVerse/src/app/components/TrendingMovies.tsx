import { MovieCard } from "./MovieCard";
import { TrendingUp } from "lucide-react";
import { useEffect, useState } from "react";
import { moviesApi } from "../../lib/api";

export function TrendingMovies() {
  const [movies, setMovies] = useState<any[]>([]);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    moviesApi.trending()
      .then((res) => setMovies(res.data ?? []))
      .catch(() => {})
      .finally(() => setLoading(false));
  }, []);

  return (
    <section className="py-16 bg-black">
      <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div className="flex items-center gap-3 mb-8">
          <TrendingUp className="w-8 h-8 text-red-600" />
          <h2 className="text-3xl font-bold text-white">Trending Now</h2>
        </div>

        {loading ? (
          <div className="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4">
            {Array.from({ length: 6 }).map((_, i) => (
              <div key={i} className="bg-zinc-900 rounded-lg aspect-[2/3] animate-pulse" />
            ))}
          </div>
        ) : (
          <div className="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4">
            {movies.map((movie) => (
              <MovieCard
                key={movie.id}
                movieId={movie.slug}
                title={movie.title}
                genre={movie.genres?.[0] ?? movie.genre_names ?? ""}
                rating={parseFloat(movie.rating)}
                image={movie.poster ?? `https://images.unsplash.com/photo-1536440136628-849c177e76a1?w=400`}
              />
            ))}
          </div>
        )}
      </div>
    </section>
  );
}
