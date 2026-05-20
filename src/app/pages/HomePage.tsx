import { Navbar } from "../components/Navbar";
import { HeroSection } from "../components/HeroSection";
import { TrendingMovies } from "../components/TrendingMovies";
import { Categories } from "../components/Categories";
import { Footer } from "../components/Footer";
import { useEffect, useState } from "react";
import { useSearchParams } from "react-router";
import { moviesApi } from "../../lib/api";
import { MovieCard } from "../components/MovieCard";

export function HomePage() {
  const [searchParams]          = useSearchParams();
  const [movies, setMovies]     = useState<any[]>([]);
  const [loading, setLoading]   = useState(false);
  const search                  = searchParams.get("search");
  const category                = searchParams.get("category");
  const trending                = searchParams.get("trending");

  useEffect(() => {
    if (search || category || trending) {
      setLoading(true);
      const params: any = { limit: 24 };
      if (search) params.search = search;
      if (category) params.category = category;
      if (trending) params.trending = 1;

      moviesApi.list(params)
        .then((res) => setMovies(res.data ?? []))
        .catch(() => {})
        .finally(() => setLoading(false));
    } else {
      setMovies([]);
    }
  }, [search, category, trending]);

  const showFiltered = search || category || trending;

  return (
    <div className="min-h-screen bg-black">
      <Navbar />
      <main className="pt-16">
        {!showFiltered && <HeroSection />}
        
        {showFiltered && (
          <section className="py-16 bg-black min-h-[60vh]">
            <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
              <h2 className="text-3xl font-bold text-white mb-8">
                {search && `Search results for "${search}"`}
                {category && `Movies in ${category}`}
                {trending && "Trending Movies"}
              </h2>

              {loading ? (
                <div className="flex items-center justify-center py-20">
                  <div className="w-12 h-12 border-4 border-red-600 border-t-transparent rounded-full animate-spin" />
                </div>
              ) : movies.length === 0 ? (
                <p className="text-white/50 text-center py-20">No movies found.</p>
              ) : (
                <div className="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4">
                  {movies.map((movie) => (
                    <MovieCard
                      key={movie.id}
                      movieId={movie.slug}
                      title={movie.title}
                      genre={movie.genres?.[0] ?? movie.genre_names ?? ""}
                      rating={parseFloat(movie.rating)}
                      image={movie.poster ?? "https://images.unsplash.com/photo-1536440136628-849c177e76a1?w=400"}
                    />
                  ))}
                </div>
              )}
            </div>
          </section>
        )}

        {!showFiltered && (
          <>
            <TrendingMovies />
            <Categories />
          </>
        )}
      </main>
      <Footer />
    </div>
  );
}
