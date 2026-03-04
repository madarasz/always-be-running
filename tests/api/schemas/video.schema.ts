import { z } from 'zod';

const VideoTagSchema = z.object({
  id: z.number().optional(),
  video_id: z.number().optional(),
  user_id: z.number().nullable().optional(),
  tagged_by_user_id: z.number().optional(),
  is_runner: z.union([z.boolean(), z.number()]).nullable().optional(),
  import_player_name: z.string().nullable().optional(),
}).passthrough();

const VideoItemSchema = z.object({
  id: z.number(),
  video_id: z.string(),
  video_title: z.string(),
  thumbnail_url: z.string(),
  channel_name: z.string(),
  type: z.number(),
  length: z.string().nullable(),
  flag_removed: z.union([z.boolean(), z.number()]),
  video_tags: z.array(VideoTagSchema).optional(),
});

const CardpoolSchema = z.object({
  id: z.string(),
  name: z.string(),
});

const TournamentTypeSchema = z.object({
  type_name: z.string(),
});

const TournamentFormatSchema = z.object({
  format_name: z.string(),
});

export const VideoTournamentSchema = z.object({
  id: z.number(),
  title: z.string(),
  date: z.string(),
  location_country: z.string(),
  players_number: z.number().nullable(),
  charity: z.union([z.boolean(), z.number()]),
  seoUrl: z.string(),
  videos: z.array(VideoItemSchema),
  cardpool: CardpoolSchema.nullable().optional(),
  tournament_type: TournamentTypeSchema.optional(),
  tournament_format: TournamentFormatSchema.optional(),
});

export const VideosResponseSchema = z.array(VideoTournamentSchema);
