import { z } from 'zod';

const PhotoSchema = z.object({
  id: z.number(),
}).passthrough();

const PrizeItemSchema = z.object({
  id: z.number(),
  artist_id: z.number(),
  photos: z.array(PhotoSchema).optional(),
}).passthrough();

const ArtistUserSchema = z.object({
  id: z.number(),
  displayUsername: z.string().optional(),
}).passthrough();

export const ArtistSchema = z.object({
  id: z.number(),
  description: z.string().nullable(),
  url: z.string().nullable(),
  displayArtistName: z.string(),
  items: z.array(PrizeItemSchema).optional(),
  user: ArtistUserSchema.nullable().optional(),
});

export const ArtistsResponseSchema = z.array(ArtistSchema);
