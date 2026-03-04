import { describe, it, expect } from 'vitest';
import { fetchApi } from '../helpers/api-client.js';
import { VideosResponseSchema } from '../schemas/video.schema.js';

describe('GET /api/videos', () => {
  it('returns valid schema', async () => {
    const { data, status } = await fetchApi('/api/videos', VideosResponseSchema);

    expect(status).toBe(200);
    expect(Array.isArray(data)).toBe(true);
  });

  it('tournaments have videos array', async () => {
    const { data } = await fetchApi('/api/videos', VideosResponseSchema);

    for (const tournament of data) {
      expect(tournament.id).toBeTypeOf('number');
      expect(tournament.title).toBeTypeOf('string');
      expect(Array.isArray(tournament.videos)).toBe(true);
    }
  });

  it('video type is valid (1=YouTube, 2=Twitch)', async () => {
    const { data } = await fetchApi('/api/videos', VideosResponseSchema);

    for (const tournament of data) {
      for (const video of tournament.videos) {
        expect([1, 2]).toContain(video.type);
      }
    }
  });

  it('videos have valid thumbnail URLs', async () => {
    const { data } = await fetchApi('/api/videos', VideosResponseSchema);

    for (const tournament of data) {
      for (const video of tournament.videos) {
        expect(video.thumbnail_url).toMatch(/^https?:\/\//);
      }
    }
  });

  it('video length format is valid when present', async () => {
    const { data } = await fetchApi('/api/videos', VideosResponseSchema);

    for (const tournament of data) {
      for (const video of tournament.videos) {
        if (video.length) {
          // Format: "HH:MM:SS" or "MM:SS"
          expect(video.length).toMatch(/^\d+:\d{2}(:\d{2})?$/);
        }
      }
    }
  });

  it('tournaments include cardpool when present', async () => {
    const { data } = await fetchApi('/api/videos', VideosResponseSchema);

    const tournamentsWithCardpool = data.filter(t => t.cardpool);

    for (const tournament of tournamentsWithCardpool) {
      expect(tournament.cardpool!.id).toBeTypeOf('string');
      expect(tournament.cardpool!.name).toBeTypeOf('string');
    }
  });
});
