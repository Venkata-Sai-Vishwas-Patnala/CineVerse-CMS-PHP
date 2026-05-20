import { Star } from "lucide-react";
import { useState } from "react";
import { Link } from "react-router";
import { ImageWithFallback } from "./figma/ImageWithFallback";

interface MovieCardProps {
  title: string;
  genre: string;
  rating: number;
  image: string;
  movieId: string;
}

export function MovieCard({ title, genre, rating, image, movieId }: MovieCardProps) {
  const [userRating, setUserRating] = useState(0);
  const [hoverRating, setHoverRating] = useState(0);

  return (
    <div className="group relative bg-zinc-900 rounded-lg overflow-hidden transition-transform hover:scale-105 hover:z-10">
      {/* Movie Poster */}
      <Link to={`/movie/${movieId}`} className="relative aspect-[2/3] overflow-hidden block">
        <ImageWithFallback
          src={image}
          alt={title}
          className="w-full h-full object-cover"
        />
        <div className="absolute inset-0 bg-gradient-to-t from-black via-transparent to-transparent opacity-0 group-hover:opacity-100 transition-opacity"></div>
      </Link>

      {/* Movie Info */}
      <div className="p-4 space-y-3">
        <h3 className="text-white font-semibold text-lg line-clamp-1">{title}</h3>
        
        <div className="flex items-center justify-between">
          <span className="text-sm text-white/60">{genre}</span>
          <div className="flex items-center gap-1">
            <Star className="w-4 h-4 fill-yellow-400 text-yellow-400" />
            <span className="text-white/90 text-sm font-medium">{rating}</span>
          </div>
        </div>

        {/* User Rating */}
        <div className="pt-2 border-t border-white/10">
          <p className="text-xs text-white/50 mb-2">Your Rating:</p>
          <div className="flex gap-1">
            {[1, 2, 3, 4, 5].map((star) => (
              <button
                key={star}
                onClick={() => setUserRating(star)}
                onMouseEnter={() => setHoverRating(star)}
                onMouseLeave={() => setHoverRating(0)}
                className="transition-transform hover:scale-110"
              >
                <Star
                  className={`w-4 h-4 ${
                    star <= (hoverRating || userRating)
                      ? "fill-yellow-400 text-yellow-400"
                      : "text-white/30"
                  }`}
                />
              </button>
            ))}
          </div>
        </div>
      </div>
    </div>
  );
}
