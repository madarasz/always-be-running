import { describe, it, expect, beforeAll } from 'vitest';
import { fetchApi, fetchRaw } from '../helpers/api-client.js';
import {
  EntriesResponseSchema,
  EntryErrorSchema,
  EntryWarningSchema,
} from '../schemas/entry.schema.js';
import { ResultsResponseSchema } from '../schemas/tournament.schema.js';

describe('GET /api/entries', () => {
  let concludedTournamentId: number | null = null;

  beforeAll(async () => {
    const { data } = await fetchApi(
      '/api/tournaments/results?limit=10',
      ResultsResponseSchema
    );
    const withEntries = data.find((t) => t.claim_count > 0);
    if (withEntries) {
      concludedTournamentId = withEntries.id;
    }
  });

  it('returns error for missing tournament id', async () => {
    const { data, status } = await fetchRaw('/api/entries');

    expect(status).toBe(200);
    expect(data).toHaveProperty('error');
  });

  it('returns error for non-existent tournament', async () => {
    const { data, status } = await fetchRaw('/api/entries?id=999999999');

    expect(status).toBe(200);
    expect(data).toHaveProperty('error');
  });

  it('returns valid schema for concluded tournament with entries', async () => {
    if (!concludedTournamentId) {
      console.warn('No concluded tournament with entries found, skipping test');
      return;
    }

    const { data, status } = await fetchApi(
      `/api/entries?id=${concludedTournamentId}`,
      EntriesResponseSchema
    );

    expect(status).toBe(200);
    expect(Array.isArray(data)).toBe(true);
    expect(data.length).toBeGreaterThan(0);
  });

  it('entries are ordered by rank', async () => {
    if (!concludedTournamentId) {
      console.warn('No concluded tournament with entries found, skipping test');
      return;
    }

    const { data } = await fetchApi(
      `/api/entries?id=${concludedTournamentId}`,
      EntriesResponseSchema
    );

    if (data.length > 1) {
      for (let i = 1; i < data.length; i++) {
        const prevRank = data[i - 1].rank_top ?? data[i - 1].rank_swiss;
        const currRank = data[i].rank_top ?? data[i].rank_swiss;
        expect(currRank).toBeGreaterThanOrEqual(prevRank);
      }
    }
  });

  it('entries have required identity fields', async () => {
    if (!concludedTournamentId) {
      console.warn('No concluded tournament with entries found, skipping test');
      return;
    }

    const { data } = await fetchApi(
      `/api/entries?id=${concludedTournamentId}`,
      EntriesResponseSchema
    );

    for (const entry of data) {
      expect(entry.rank_swiss).toBeTypeOf('number');
      expect(entry).toHaveProperty('runner_deck_identity_id');
      expect(entry).toHaveProperty('corp_deck_identity_id');
      expect(entry).toHaveProperty('runner_deck_url');
      expect(entry).toHaveProperty('corp_deck_url');
    }
  });

  it('deck URLs are valid when present', async () => {
    if (!concludedTournamentId) {
      console.warn('No concluded tournament with entries found, skipping test');
      return;
    }

    const { data } = await fetchApi(
      `/api/entries?id=${concludedTournamentId}`,
      EntriesResponseSchema
    );

    for (const entry of data) {
      if (entry.runner_deck_url && entry.runner_deck_url !== '') {
        expect(entry.runner_deck_url).toMatch(/^https?:\/\//);
      }
      if (entry.corp_deck_url && entry.corp_deck_url !== '') {
        expect(entry.corp_deck_url).toMatch(/^https?:\/\//);
      }
    }
  });
});
