import { createBrowserRouter } from "react-router";
import { HomePage } from "./pages/HomePage";
import { AdminDashboard } from "./pages/AdminDashboard";
import { MovieDetails } from "./pages/MovieDetails";
import { LoginPage } from "./pages/LoginPage";
import { ProfilePage } from "./pages/ProfilePage";
import { NotFound } from "./pages/NotFound";

export const router = createBrowserRouter([
  {
    path: "/",
    Component: HomePage,
  },
  {
    path: "/login",
    Component: LoginPage,
  },
  {
    path: "/profile",
    Component: ProfilePage,
  },
  {
    path: "/admin",
    Component: AdminDashboard,
  },
  {
    path: "/movie/:movieId",
    Component: MovieDetails,
  },
  {
    path: "*",
    Component: NotFound,
  },
]);
