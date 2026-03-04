import { z } from 'zod';

export const EntrySchema = z.object({
  user_id: z.number().nullable(),
  user_name: z.string().nullable(),
  user_import_name: z.string().nullable(),
  rank_swiss: z.number(),
  rank_top: z.number().nullable(),
  runner_deck_title: z.string(),
  runner_deck_identity_id: z.union([z.number(), z.string()]),
  runner_deck_identity_title: z.string().optional(),
  runner_deck_identity_faction: z.string().optional(),
  runner_deck_url: z.string(),
  corp_deck_title: z.string(),
  corp_deck_identity_id: z.union([z.number(), z.string()]),
  corp_deck_identity_title: z.string().optional(),
  corp_deck_identity_faction: z.string().optional(),
  corp_deck_url: z.string(),
});

export const EntriesResponseSchema = z.array(EntrySchema);

export const EntryErrorSchema = z.object({
  error: z.string(),
});

export const EntryWarningSchema = z.object({
  warn: z.string(),
});
