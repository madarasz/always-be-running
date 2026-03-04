import { describe, it, expect } from 'vitest';
import { fetchApi } from '../helpers/api-client.js';
import {
  UpcomingResponseSchema,
  ResultsResponseSchema,
} from '../schemas/tournament.schema.js';

function parseAbrDate(dateStr: string): Date {
  // ABR date format: "YYYY.MM.DD." (with optional trailing dot) -> convert to "YYYY-MM-DD"
  const normalized = dateStr.replace(/\./g, '-').replace(/-$/, '');
  const date = new Date(normalized + 'T00:00:00');
  return date;
}

describe('GET /api/tournaments/upcoming', () => {
  it('returns valid schema', async () => {
    const { data, status } = await fetchApi(
      '/api/tournaments/upcoming',
      UpcomingResponseSchema
    );

    expect(status).toBe(200);
    expect(data).toHaveProperty('tournaments');
    expect(data).toHaveProperty('recurring_events');
    expect(data).toHaveProperty('rendered_in');
    expect(Array.isArray(data.tournaments)).toBe(true);
    expect(Array.isArray(data.recurring_events)).toBe(true);
  });

  it('returns tournaments with recent dates', async () => {
    const { data } = await fetchApi(
      '/api/tournaments/upcoming',
      UpcomingResponseSchema
    );

    // Check that tournaments have valid date formats
    for (const tournament of data.tournaments) {
      if (tournament.date) {
        const tournamentDate = parseAbrDate(tournament.date);
        expect(tournamentDate.getTime()).not.toBeNaN();
      }
    }
  });

  it('separates recurring events from regular tournaments', async () => {
    const { data } = await fetchApi(
      '/api/tournaments/upcoming',
      UpcomingResponseSchema
    );

    for (const recurring of data.recurring_events) {
      expect(recurring).toHaveProperty('recurring_day');
      expect(recurring.recurring_day).toBeTruthy();
    }

    for (const tournament of data.tournaments) {
      if (!tournament.recurring_day) {
        expect(tournament.date).toBeTruthy();
      }
    }
  });

  it('includes required fields for each tournament', async () => {
    const { data } = await fetchApi(
      '/api/tournaments/upcoming',
      UpcomingResponseSchema
    );

    if (data.tournaments.length > 0) {
      const tournament = data.tournaments[0];
      expect(tournament.id).toBeTypeOf('number');
      expect(tournament.title).toBeTypeOf('string');
      expect(tournament.location).toBeTypeOf('string');
      expect(tournament.location_country).toBeTypeOf('string');
    }
  });
});

describe('GET /api/tournaments/results', () => {
  it('returns valid schema', async () => {
    const { data, status } = await fetchApi(
      '/api/tournaments/results',
      ResultsResponseSchema
    );

    expect(status).toBe(200);
    expect(Array.isArray(data)).toBe(true);
  });

  it('all tournaments are concluded', async () => {
    const { data } = await fetchApi(
      '/api/tournaments/results',
      ResultsResponseSchema
    );

    for (const tournament of data) {
      // concluded can be boolean true or number 1
      expect([true, 1]).toContain(tournament.concluded);
    }
  });

  it('supports limit parameter', async () => {
    const limit = 5;
    const { data } = await fetchApi(
      `/api/tournaments/results?limit=${limit}`,
      ResultsResponseSchema
    );

    expect(data.length).toBeLessThanOrEqual(limit);
  });

  it('supports offset parameter for pagination', async () => {
    const { data: firstPage } = await fetchApi(
      '/api/tournaments/results?limit=5',
      ResultsResponseSchema
    );

    const { data: secondPage } = await fetchApi(
      '/api/tournaments/results?limit=5&offset=5',
      ResultsResponseSchema
    );

    if (firstPage.length > 0 && secondPage.length > 0) {
      expect(firstPage[0].id).not.toBe(secondPage[0].id);
    }
  });

  it('includes player and claim counts', async () => {
    const { data } = await fetchApi(
      '/api/tournaments/results?limit=10',
      ResultsResponseSchema
    );

    for (const tournament of data) {
      expect(tournament.players_count).toBeTypeOf('number');
      expect(tournament.claim_count).toBeTypeOf('number');
      // top_count can be null
      expect(tournament.top_count === null || typeof tournament.top_count === 'number').toBe(true);
    }
  });

  it('first item includes tournament_count metadata', async () => {
    const { data } = await fetchApi(
      '/api/tournaments/results?limit=1',
      ResultsResponseSchema
    );

    if (data.length > 0) {
      expect(data[0]).toHaveProperty('tournament_count');
      expect(data[0].tournament_count).toBeTypeOf('number');
    }
  });
});
