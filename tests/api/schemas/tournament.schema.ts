import { z } from 'zod';

const BaseTournamentSchema = z.object({
  id: z.number(),
  title: z.string(),
  creator_id: z.number().nullable(),
  creator_name: z.string().nullable(),
  creator_supporter: z.union([z.boolean(), z.number()]).optional(),
  creator_class: z.string().optional(),
  created_at: z.string(),
  location: z.string(),
  location_lat: z.number().nullable(),
  location_lng: z.number().nullable(),
  location_country: z.string(),
  location_state: z.string().nullable(),
  address: z.string(),
  store: z.string(),
  place_id: z.string(),
  contact: z.string(),
  approved: z.number().nullable(),
  registration_count: z.number(),
  photos: z.number(),
  url: z.string(),
  link_facebook: z.string().nullable(),
});

export const UpcomingTournamentSchema = BaseTournamentSchema.extend({
  cardpool: z.string().optional(),
  date: z.string().optional(),
  type: z.string().optional(),
  format: z.string().optional(),
  mwl: z.string().optional(),
  concluded: z.union([z.boolean(), z.number()]).optional(),
  charity: z.union([z.boolean(), z.number()]).optional(),
  end_date: z.string().nullable().optional(),
  recurring_day: z.string().optional(),
  videos: z.number().optional(),
  players_count: z.number().optional(),
  top_count: z.number().nullable().optional(),
  claim_count: z.number().optional(),
  claim_conflict: z.union([z.boolean(), z.number()]).optional(),
  matchdata: z.union([z.boolean(), z.number()]).optional(),
  winner_runner_identity: z.string().nullable().optional(),
  winner_corp_identity: z.string().nullable().optional(),
});

export const UpcomingResponseSchema = z.object({
  tournaments: z.array(UpcomingTournamentSchema),
  recurring_events: z.array(UpcomingTournamentSchema),
  rendered_in: z.number(),
});

export const ResultTournamentSchema = BaseTournamentSchema.extend({
  cardpool: z.string(),
  date: z.string(),
  type: z.string(),
  format: z.string(),
  mwl: z.string(),
  concluded: z.union([z.boolean(), z.number()]),
  charity: z.union([z.boolean(), z.number()]),
  players_count: z.number(),
  top_count: z.number().nullable(),
  claim_count: z.number(),
  claim_conflict: z.union([z.boolean(), z.number()]),
  matchdata: z.union([z.boolean(), z.number()]),
  videos: z.number(),
  winner_runner_identity: z.string().nullable().optional(),
  winner_corp_identity: z.string().nullable().optional(),
  rendered_in: z.number().optional(),
  tournament_count: z.number().optional(),
});

export const ResultsResponseSchema = z.array(ResultTournamentSchema);
