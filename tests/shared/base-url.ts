const DEFAULT_BASE_URL = 'http://localhost:8000';

function toAbsoluteBaseUrl(value?: string): string | undefined {
  if (!value) {
    return undefined;
  }

  const trimmed = value.trim();
  if (!/^https?:\/\//i.test(trimmed)) {
    return undefined;
  }

  return trimmed.replace(/\/+$/, '');
}

export const BASE_URL = toAbsoluteBaseUrl(process.env.BASE_URL)
  || toAbsoluteBaseUrl(process.env.API_BASE_URL)
  || DEFAULT_BASE_URL;

export function appUrl(path = ''): string {
  if (!path) {
    return BASE_URL;
  }

  const normalizedPath = path.startsWith('/') ? path : `/${path}`;
  return `${BASE_URL}${normalizedPath}`;
}