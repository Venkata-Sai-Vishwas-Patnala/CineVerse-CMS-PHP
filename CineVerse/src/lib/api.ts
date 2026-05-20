const BASE = '/api';

async function request<T>(path: string, options: RequestInit = {}): Promise<T> {
  const res = await fetch(`${BASE}${path}`, {
    credentials: 'include',
    headers: { 'Content-Type': 'application/json', ...(options.headers ?? {}) },
    ...options,
  });
  const data = await res.json();
  if (!res.ok) throw new Error(data.error ?? 'Request failed');
  return data;
}

// ── Auth ─────────────────────────────────────────────────────────────────────
export const authApi = {
  me:       ()                                          => request<any>('/auth/me'),
  login:    (email: string, password: string)           => request<any>('/auth/login',    { method: 'POST', body: JSON.stringify({ email, password }) }),
  register: (username: string, email: string, password: string) => request<any>('/auth/register', { method: 'POST', body: JSON.stringify({ username, email, password }) }),
  logout:   ()                                          => request<any>('/auth/logout',   { method: 'POST' }),
  profile:  (data: { username: string; email: string; avatar?: string }) => request<any>('/auth/profile',  { method: 'PUT',  body: JSON.stringify(data) }),
  password: (current_password: string, new_password: string)             => request<any>('/auth/password', { method: 'PUT',  body: JSON.stringify({ current_password, new_password }) }),
};

// ── Movies ───────────────────────────────────────────────────────────────────
export const moviesApi = {
  list:     (params?: Record<string, any>) => request<any>('/movies?' + new URLSearchParams(params ?? {}).toString()),
  featured: ()                             => request<any>('/movies/featured'),
  trending: ()                             => request<any>('/movies/trending'),
  get:      (id: number)                   => request<any>(`/movies/${id}`),
  bySlug:   (slug: string)                 => request<any>(`/movies/slug?slug=${slug}`),
  create:   (data: any)                    => request<any>('/movies',     { method: 'POST',   body: JSON.stringify(data) }),
  update:   (id: number, data: any)        => request<any>(`/movies/${id}`, { method: 'PUT',  body: JSON.stringify(data) }),
  delete:   (id: number)                   => request<any>(`/movies/${id}`, { method: 'DELETE' }),
};

// ── Reviews ──────────────────────────────────────────────────────────────────
export const reviewsApi = {
  list:   (movie_id: number, page = 1) => request<any>(`/reviews?movie_id=${movie_id}&page=${page}`),
  create: (data: { movie_id: number; rating: number; review_text?: string }) => request<any>('/reviews', { method: 'POST', body: JSON.stringify(data) }),
  delete: (id: number)                 => request<any>(`/reviews/${id}`, { method: 'DELETE' }),
};

// ── Categories ───────────────────────────────────────────────────────────────
export const categoriesApi = {
  list:   ()                                                    => request<any>('/categories'),
  create: (data: { name: string; color?: string; icon?: string }) => request<any>('/categories',     { method: 'POST',   body: JSON.stringify(data) }),
  update: (id: number, data: any)                               => request<any>(`/categories/${id}`, { method: 'PUT',    body: JSON.stringify(data) }),
  delete: (id: number)                                          => request<any>(`/categories/${id}`, { method: 'DELETE' }),
};

// ── Watchlist ─────────────────────────────────────────────────────────────────
export const watchlistApi = {
  list:   (page = 1)       => request<any>(`/watchlist?page=${page}`),
  add:    (movie_id: number) => request<any>('/watchlist',     { method: 'POST',   body: JSON.stringify({ movie_id }) }),
  remove: (movie_id: number) => request<any>(`/watchlist/${movie_id}`, { method: 'DELETE' }),
};

// ── Admin ─────────────────────────────────────────────────────────────────────
export const adminApi = {
  stats:           ()                          => request<any>('/admin/stats'),
  users:           (params?: Record<string, any>) => request<any>('/admin/users?' + new URLSearchParams(params ?? {}).toString()),
  createUser:      (data: any)                 => request<any>('/admin/users',     { method: 'POST',   body: JSON.stringify(data) }),
  updateUser:      (id: number, data: any)     => request<any>(`/admin/users/${id}`, { method: 'PUT',  body: JSON.stringify(data) }),
  deleteUser:      (id: number)                => request<any>(`/admin/users/${id}`, { method: 'DELETE' }),
  platforms:       ()                          => request<any>('/admin/platforms'),
  createPlatform:  (data: any)                 => request<any>('/admin/platforms',  { method: 'POST',   body: JSON.stringify(data) }),
  deletePlatform:  (id: number)                => request<any>(`/admin/platforms/${id}`, { method: 'DELETE' }),
  bulkMovies:      (ids: number[], action: string, status?: string) => request<any>('/admin/bulk', { method: 'POST', body: JSON.stringify({ ids, action, status }) }),
};

// ── Upload ────────────────────────────────────────────────────────────────────
export const uploadApi = {
  poster:   (file: File) => uploadFile(file, 'poster'),
  backdrop: (file: File) => uploadFile(file, 'backdrop'),
  avatar:   (file: File) => uploadFile(file, 'avatar'),
};

async function uploadFile(file: File, type: string): Promise<{ url: string }> {
  const form = new FormData();
  form.append(type, file);
  const res = await fetch(`${BASE}/upload/${type}`, { method: 'POST', credentials: 'include', body: form });
  const data = await res.json();
  if (!res.ok) throw new Error(data.error ?? 'Upload failed');
  return data;
}
