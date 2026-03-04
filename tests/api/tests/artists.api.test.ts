import { describe, it, expect } from 'vitest';
import { fetchApi } from '../helpers/api-client.js';
import { ArtistsResponseSchema } from '../schemas/artist.schema.js';

describe('GET /api/artists', () => {
  it('returns valid schema', async () => {
    const { data, status } = await fetchApi('/api/artists', ArtistsResponseSchema);

    expect(status).toBe(200);
    expect(Array.isArray(data)).toBe(true);
  });

  it('artists have required fields', async () => {
    const { data } = await fetchApi('/api/artists', ArtistsResponseSchema);

    for (const artist of data) {
      expect(artist.id).toBeTypeOf('number');
      // displayArtistName, description and url can be null
      expect(artist.displayArtistName === null || typeof artist.displayArtistName === 'string').toBe(true);
      expect(artist.description === null || typeof artist.description === 'string').toBe(true);
      expect(artist.url === null || typeof artist.url === 'string').toBe(true);
    }
  });

  it('artist URLs are valid when present and non-empty', async () => {
    const { data } = await fetchApi('/api/artists', ArtistsResponseSchema);

    for (const artist of data) {
      if (artist.url && artist.url !== '' && artist.url.includes('://')) {
        expect(artist.url).toMatch(/^https?:\/\//);
      }
    }
  });

  it('artist items array is valid when present', async () => {
    const { data } = await fetchApi('/api/artists', ArtistsResponseSchema);

    for (const artist of data) {
      if (artist.items) {
        expect(Array.isArray(artist.items)).toBe(true);
        for (const item of artist.items) {
          expect(item.id).toBeTypeOf('number');
          expect(item.artist_id).toBe(artist.id);
        }
      }
    }
  });
});
