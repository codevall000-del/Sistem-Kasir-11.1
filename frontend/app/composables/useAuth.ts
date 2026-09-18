export const useAuth = () => {
  const router = useRouter();

  const getToken = (): string | null => {
    if (import.meta.server) return null;
    return localStorage.getItem('auth_token');
  };

  const getUser = (): any | null => {
    if (import.meta.server) return null;
    const raw = localStorage.getItem('auth_user');
    if (!raw) return null;
    try { return JSON.parse(raw); } catch { return null; }
  };

  const isLoggedIn = (): boolean => !!getToken();

  const isPetugas = (): boolean => getUser()?.role === 'petugas';

  const isAdmin = (): boolean => {
    const role = getUser()?.role;
    return role === 'super_admin' || role === 'superadmin';
  };

  const login = async (email: string, password: string) => {
    const { $api } = useNuxtApp();
    const res = await ($api as any).post('/auth/login', { email, password });
    if (res.data.success) {
      localStorage.setItem('auth_token', res.data.token);
      localStorage.setItem('auth_user', JSON.stringify(res.data.user));
      return res.data;
    }
    throw new Error(res.data.message || 'Login gagal');
  };

  const logout = async () => {
    try {
      const { $api } = useNuxtApp();
      await ($api as any).post('/auth/logout');
    } catch {}
    localStorage.removeItem('auth_token');
    localStorage.removeItem('auth_user');
    router.push('/');
  };

  return { getToken, getUser, isLoggedIn, isPetugas, isAdmin, login, logout };
};
