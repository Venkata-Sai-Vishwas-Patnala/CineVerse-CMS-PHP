import { Clapperboard, Drama, Heart, Laugh, Sparkles, Zap, BookOpen, Shield, Compass, Clock } from "lucide-react";
import { useEffect, useState } from "react";
import { categoriesApi } from "../../lib/api";
import { useNavigate } from "react-router";

const iconMap: Record<string, any> = {
  Zap, Drama, Laugh, Heart, Sparkles, Clapperboard, BookOpen, Shield, Compass, Clock,
};

export function Categories() {
  const [categories, setCategories] = useState<any[]>([]);
  const navigate = useNavigate();

  useEffect(() => {
    categoriesApi.list().then((res) => setCategories(res.data ?? [])).catch(() => {});
  }, []);

  return (
    <section className="py-16 bg-zinc-950">
      <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <h2 className="text-3xl font-bold text-white mb-8">Browse by Genre</h2>

        <div className="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4">
          {categories.map((category) => {
            const Icon = iconMap[category.icon] ?? Clapperboard;
            return (
              <button
                key={category.id}
                onClick={() => navigate(`/?category=${category.slug}`)}
                className={`group relative overflow-hidden rounded-lg p-6 bg-gradient-to-br ${category.color} hover:scale-105 transition-transform`}
              >
                <div className="flex flex-col items-center gap-3">
                  <Icon className="w-10 h-10 text-white" />
                  <span className="text-white font-semibold">{category.name}</span>
                  {category.movie_count > 0 && (
                    <span className="text-white/70 text-xs">{category.movie_count} movies</span>
                  )}
                </div>
                <div className="absolute inset-0 bg-black/0 group-hover:bg-black/20 transition-colors" />
              </button>
            );
          })}
        </div>
      </div>
    </section>
  );
}
