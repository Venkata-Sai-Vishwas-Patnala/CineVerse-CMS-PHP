import { Film, Facebook, Twitter, Instagram, Youtube } from "lucide-react";

export function Footer() {
  return (
    <footer className="bg-black border-t border-white/10 py-12">
      <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div className="grid grid-cols-1 md:grid-cols-4 gap-8 mb-8">
          {/* Brand */}
          <div className="space-y-4">
            <div className="flex items-center gap-2">
              <Film className="w-8 h-8 text-red-600" />
              <span className="text-2xl font-bold text-white">Cineverse</span>
            </div>
            <p className="text-white/60 text-sm">
              Your ultimate destination for movie reviews and ratings.
            </p>
          </div>

          {/* Quick Links */}
          <div>
            <h3 className="text-white font-semibold mb-4">Quick Links</h3>
            <ul className="space-y-2">
              <li>
                <a href="#home" className="text-white/60 hover:text-white text-sm transition-colors">
                  Home
                </a>
              </li>
              <li>
                <a href="#movies" className="text-white/60 hover:text-white text-sm transition-colors">
                  Movies
                </a>
              </li>
              <li>
                <a href="#reviews" className="text-white/60 hover:text-white text-sm transition-colors">
                  Reviews
                </a>
              </li>
              <li>
                <a href="#genres" className="text-white/60 hover:text-white text-sm transition-colors">
                  Genres
                </a>
              </li>
            </ul>
          </div>

          {/* Support */}
          <div>
            <h3 className="text-white font-semibold mb-4">Support</h3>
            <ul className="space-y-2">
              <li>
                <a href="#" className="text-white/60 hover:text-white text-sm transition-colors">
                  About Us
                </a>
              </li>
              <li>
                <a href="#" className="text-white/60 hover:text-white text-sm transition-colors">
                  Contact
                </a>
              </li>
              <li>
                <a href="#" className="text-white/60 hover:text-white text-sm transition-colors">
                  Privacy Policy
                </a>
              </li>
              <li>
                <a href="#" className="text-white/60 hover:text-white text-sm transition-colors">
                  Terms of Service
                </a>
              </li>
            </ul>
          </div>

          {/* Social Media */}
          <div>
            <h3 className="text-white font-semibold mb-4">Follow Us</h3>
            <div className="flex gap-4">
              <a
                href="#"
                className="w-10 h-10 rounded-full bg-white/10 hover:bg-red-600 flex items-center justify-center transition-colors"
              >
                <Facebook className="w-5 h-5 text-white" />
              </a>
              <a
                href="#"
                className="w-10 h-10 rounded-full bg-white/10 hover:bg-red-600 flex items-center justify-center transition-colors"
              >
                <Twitter className="w-5 h-5 text-white" />
              </a>
              <a
                href="#"
                className="w-10 h-10 rounded-full bg-white/10 hover:bg-red-600 flex items-center justify-center transition-colors"
              >
                <Instagram className="w-5 h-5 text-white" />
              </a>
              <a
                href="#"
                className="w-10 h-10 rounded-full bg-white/10 hover:bg-red-600 flex items-center justify-center transition-colors"
              >
                <Youtube className="w-5 h-5 text-white" />
              </a>
            </div>
          </div>
        </div>

        {/* Copyright */}
        <div className="pt-8 border-t border-white/10">
          <p className="text-center text-white/60 text-sm">
            © 2026 Cineverse. All rights reserved.
          </p>
        </div>
      </div>
    </footer>
  );
}
