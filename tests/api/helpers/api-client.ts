import { z, ZodSchema } from 'zod';

const BASE_URL = process.env.API_BASE_URL || 'http://localhost:8000';

export interface ApiResponse<T> {
  data: T;
  status: number;
  duration: number;
}

export interface ApiError {
  status: number;
  message: string;
  data?: unknown;
}

export async function fetchApi<T>(
  path: string,
  schema: ZodSchema<T>,
  options: RequestInit = {}
): Promise<ApiResponse<T>> {
  const url = `${BASE_URL}${path}`;
  const start = performance.now();

  const response = await fetch(url, {
    ...options,
    headers: {
      Accept: 'application/json',
      ...options.headers,
    },
  });

  const duration = performance.now() - start;

  if (!response.ok) {
    const error: ApiError = {
      status: response.status,
      message: response.statusText,
    };
    try {
      error.data = await response.json();
    } catch {
      // Response may not be JSON
    }
    throw error;
  }

  const json = await response.json();
  const parsed = schema.parse(json);

  return {
    data: parsed,
    status: response.status,
    duration,
  };
}

export async function fetchRaw(
  path: string,
  options: RequestInit = {}
): Promise<{ data: unknown; status: number; duration: number }> {
  const url = `${BASE_URL}${path}`;
  const start = performance.now();

  const response = await fetch(url, {
    ...options,
    headers: {
      Accept: 'application/json',
      ...options.headers,
    },
  });

  const duration = performance.now() - start;
  const data = await response.json();

  return {
    data,
    status: response.status,
    duration,
  };
}

export function expectValidationError(
  schema: ZodSchema,
  data: unknown
): z.ZodError | null {
  const result = schema.safeParse(data);
  if (result.success) {
    return null;
  }
  return result.error;
}
