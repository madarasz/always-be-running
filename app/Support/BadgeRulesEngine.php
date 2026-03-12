<?php

namespace App\Support;

use App\Badge;
use App\CardIdentity;
use App\Entry;
use App\Tournament;
use App\User;
use App\Video;
use App\VideoTag;
use Illuminate\Support\Facades\DB;

class BadgeRulesEngine
{
    public const CLAIM_STATIC_BADGES = [
        13, 14, 15, 93, 94, 27, 28, 29, 30, 34, 35, 36,
        49, 50, 51, 52, 53, 54, 55, 73, 74, 75, 76, 77, 78, 79, 80, 81,
    ];
    public const TO_BADGES = [16, 17, 18, 26, 20, 37, 111];
    public const NDB_BADGES = [21, 25, 39, 31, 32, 33, 72];
    public const VIDEO_BADGES = [47];
    public const COMMUNITY_BADGES = [48, 68];
    public const SENSIE_BADGES = [64];

    public function buildContext(): array
    {
        $fromYear = 2016;
        $toYear = 2022;

        $claimBaseBadgeIds = Badge::where('year', '>=', $fromYear)
            ->where('year', '<=', $toYear)
            ->pluck('id')
            ->all();
        $claimBaseBadgeIds = array_values(array_unique(array_merge(self::CLAIM_STATIC_BADGES, $claimBaseBadgeIds)));

        $badgeLookup = [];
        $badgeLookupAnyYear = [];
        foreach (Badge::select('id', 'tournament_type_id', 'year', 'winlevel')->orderBy('id')->get() as $badge) {
            $yearKey = is_null($badge->year) ? 'null' : (string) $badge->year;
            $badgeLookup[$badge->tournament_type_id][$yearKey][$badge->winlevel] = $badge->id;
            if (!isset($badgeLookupAnyYear[$badge->tournament_type_id][$badge->winlevel])) {
                $badgeLookupAnyYear[$badge->tournament_type_id][$badge->winlevel] = $badge->id;
            }
        }

        // Adds tournament badges to badge list.
        $championshipConfigs = [];
        for ($year = $fromYear; $year <= $toYear; $year++) {
            // yearly championship tracks
            foreach ([5, 4, 3] as $type) {
                $championshipConfigs[] = ['key' => $type.'_'.$year, 'type' => $type, 'year' => $year, 'ids' => []];
            }
        }
        // store champion
        $championshipConfigs[] = ['key' => '2_null', 'type' => 2, 'year' => null, 'ids' => []];
        // 2017 european championship
        $championshipConfigs[] = ['key' => '9_2017', 'type' => 9, 'year' => 2017, 'ids' => [82]];
        // 2018 european championship
        $championshipConfigs[] = ['key' => '9_2018', 'type' => 9, 'year' => 2018, 'ids' => [998]];
        // 2019 european championship
        $championshipConfigs[] = ['key' => '9_2019', 'type' => 9, 'year' => 2019, 'ids' => [2005]];
        // 2017 north american championship, tournament_type_id is a hack
        $championshipConfigs[] = ['key' => '10_2017', 'type' => 10, 'year' => 2017, 'ids' => [617]];
        // 2018 north american championship, tournament_type_id is a hack
        $championshipConfigs[] = ['key' => '10_2018', 'type' => 10, 'year' => 2018, 'ids' => [1542]];
        // 2020 european+african championship
        $championshipConfigs[] = ['key' => '9_2020', 'type' => 9, 'year' => 2020, 'ids' => [2810]];
        // 2020 american championship, tournament_type_id is a hack
        $championshipConfigs[] = ['key' => '10_2020', 'type' => 10, 'year' => 2020, 'ids' => [2811]];
        // 2020 asia-pacific championship, tournament_type_id is a hack
        $championshipConfigs[] = ['key' => '11_2020', 'type' => 11, 'year' => 2020, 'ids' => [2809]];
        // 2021 european+african championship
        $championshipConfigs[] = ['key' => '9_2021', 'type' => 9, 'year' => 2021, 'ids' => [3014]];
        // 2021 american championship, tournament_type_id is a hack
        $championshipConfigs[] = ['key' => '10_2021', 'type' => 10, 'year' => 2021, 'ids' => [3015]];
        // 2021 asia-pacific championship, tournament_type_id is a hack
        $championshipConfigs[] = ['key' => '11_2021', 'type' => 11, 'year' => 2021, 'ids' => [3013]];
        // 2022 european+african championship
        $championshipConfigs[] = ['key' => '9_2022', 'type' => 9, 'year' => 2022, 'ids' => [3342]];
        // 2022 american championship, tournament_type_id is a hack
        $championshipConfigs[] = ['key' => '10_2022', 'type' => 10, 'year' => 2022, 'ids' => [3341]];
        // 2022 asia-pacific championship, tournament_type_id is a hack
        $championshipConfigs[] = ['key' => '11_2022', 'type' => 11, 'year' => 2022, 'ids' => [3340]];

        $championshipSets = [];
        foreach ($championshipConfigs as $config) {
            if (count($config['ids'])) {
                $ids = $config['ids'];
            } elseif (is_null($config['year'])) {
                $ids = Tournament::where('tournament_type_id', $config['type'])
                    ->where('approved', 1)
                    ->pluck('id')
                    ->all();
            } else {
                $ids = Tournament::where('tournament_type_id', $config['type'])
                    ->where('date', '>', $config['year'])
                    ->where('date', '<', ($config['year'] + 1).'.03')
                    ->where('approved', 1)
                    ->pluck('id')
                    ->all();
            }
            $championshipSets[$config['key']] = $this->buildIdSet($ids);
        }

        $identitiesByFaction = [
            'shaper' => $this->buildIdSet(CardIdentity::where('faction_code', 'shaper')->pluck('id')->all()),
            'criminal' => $this->buildIdSet(CardIdentity::where('faction_code', 'criminal')->pluck('id')->all()),
            'anarch' => $this->buildIdSet(CardIdentity::where('faction_code', 'anarch')->pluck('id')->all()),
            'nbn' => $this->buildIdSet(CardIdentity::where('faction_code', 'nbn')->pluck('id')->all()),
            'hb' => $this->buildIdSet(CardIdentity::where('faction_code', 'haas-bioroid')->pluck('id')->all()),
            'weyland' => $this->buildIdSet(CardIdentity::where('faction_code', 'weyland-cons')->pluck('id')->all()),
            'jinteki' => $this->buildIdSet(CardIdentity::where('faction_code', 'jinteki')->pluck('id')->all()),
            'adam' => [$this->setKey('09037') => true],
            'apex' => [$this->setKey('09029') => true],
            'sunny' => [$this->setKey('09045') => true],
        ];

        $tournamentIdsTop = Tournament::where('approved', 1)->where('players_number', '>', 7)
            ->where('top_number', '>', 0)->where('concluded', 1)->pluck('id')->all();
        $tournamentIdsNoTop = Tournament::where('approved', 1)->where('players_number', '>', 7)
            ->where(function($q) {
                $q->whereNull('top_number')->orWhere('top_number', 0);
            })->where('concluded', 1)->pluck('id')->all();

        $nationalBadges = [
            ['tournament_id' => 69, 'winner_badge_id' => 78, 'participant_badge_id' => 79],
            ['tournament_id' => 1026, 'winner_badge_id' => 76, 'participant_badge_id' => 77],
            ['tournament_id' => 782, 'winner_badge_id' => 80, 'participant_badge_id' => 81],
            ['tournament_id' => 1823, 'winner_badge_id' => 98, 'participant_badge_id' => 99],
            ['tournament_id' => 3330, 'winner_badge_id' => 158, 'participant_badge_id' => 159],
        ];
        $nationalInfo = [];
        foreach ($nationalBadges as $cfg) {
            $event = Tournament::find($cfg['tournament_id']);
            if (!$event) {
                continue;
            }

            if ($event->top_number > 0) {
                $winner = Entry::where('tournament_id', $event->id)->where('rank_top', 1)->where('type', 3)->first();
            } else {
                $winner = Entry::where('tournament_id', $event->id)->where('rank', 1)->where('type', 3)->first();
            }

            $participants = Entry::where('tournament_id', $event->id)
                ->where('type', 3)
                ->where('user', '>', 0)
                ->distinct()
                ->pluck('user')
                ->all();

            $nationalInfo[] = [
                'winner_user' => $winner ? $winner->user : null,
                'participants' => $this->buildIdSet($participants),
                'winner_badge_id' => $cfg['winner_badge_id'],
                'participant_badge_id' => $cfg['participant_badge_id'],
            ];
        }

        $recurringIds = Tournament::where('recur_weekly', '>', 0)->where('approved', 1)->pluck('id')->all();

        return [
            'claim_badge_ids' => $claimBaseBadgeIds,
            'badge_lookup' => $badgeLookup,
            'badge_lookup_any_year' => $badgeLookupAnyYear,
            'championship_configs' => $championshipConfigs,
            'championship_sets' => $championshipSets,
            'identities_by_faction' => $identitiesByFaction,
            'tournament_top_set' => $this->buildIdSet($tournamentIdsTop),
            'tournament_no_top_set' => $this->buildIdSet($tournamentIdsNoTop),
            'recurring_ids' => $recurringIds,
            'charity_set' => $this->buildIdSet(Tournament::where('charity', 1)->where('approved', 1)->pluck('id')->all()),
            'road_stores_set' => $this->buildIdSet(Tournament::where('tournament_type_id', 2)->where('approved', 1)->pluck('id')->all()),
            'road_regionals_set' => $this->buildIdSet(Tournament::where('tournament_type_id', 3)->where('approved', 1)->pluck('id')->all()),
            'road_nationals_set' => $this->buildIdSet(Tournament::where('tournament_type_id', 4)->where('approved', 1)->pluck('id')->all()),
            'cos_set' => $this->buildIdSet(Tournament::whereIn('tournament_type_id', [1, 6, 7, 10])->where('players_number', '>', 7)->where('approved', 1)->pluck('id')->all()),
            'national_info' => $nationalInfo,
        ];
    }

    public function computeAll(User $user, array $context): array
    {
        $userId = (int) $user->id;
        $badges = [];
        foreach (array_merge(
            $context['claim_badge_ids'],
            self::TO_BADGES,
            self::NDB_BADGES,
            self::VIDEO_BADGES,
            self::COMMUNITY_BADGES,
            self::SENSIE_BADGES
        ) as $badgeId) {
            $badges[$badgeId] = false;
        }

        $entries = Entry::where('user', $userId)->where('type', 3)->get([
            'tournament_id', 'rank', 'rank_top', 'runner_deck_id', 'runner_deck_identity', 'corp_deck_identity'
        ]);

        foreach ($context['championship_configs'] as $config) {
            $set = $context['championship_sets'][$config['key']] ?? [];
            if (empty($set)) {
                continue;
            }

            $winner = $this->anyEntryMatchesTournamentSet($entries, $set, function ($entry) {
                return intval($entry->rank_top) === 1 || (is_null($entry->rank_top) && intval($entry->rank) === 1);
            });

            if ($winner) {
                $badgeId = $this->badgeIdFromLookup($context, $config['type'], $config['year'], 1);
                if ($badgeId) {
                    $badges[$badgeId] = true;
                }
                continue;
            }

            if ($config['type'] <= 2) {
                continue;
            }

            $topCut = $this->anyEntryMatchesTournamentSet($entries, $set, function ($entry) {
                return intval($entry->rank_top) > 0;
            });
            if (!$topCut && $config['year'] == 2019 && $config['type'] == 9) {
                $topCut = $this->anyEntryMatchesTournamentSet($entries, $set, function ($entry) {
                    return intval($entry->rank) < 14;
                });
            }

            if ($topCut) {
                $badgeId = $this->badgeIdFromLookup($context, $config['type'], $config['year'], 2);
                if ($badgeId) {
                    $badges[$badgeId] = true;
                }
            } elseif (in_array($config['type'], [5, 9, 10, 11])) {
                $participated = $this->anyEntryMatchesTournamentSet($entries, $set, function ($entry) {
                    return intval($entry->runner_deck_id) > 0;
                });
                if ($participated) {
                    $badgeId = $this->badgeIdFromLookup($context, $config['type'], $config['year'], 5);
                    if ($badgeId) {
                        $badges[$badgeId] = true;
                    }
                }
            }
        }

        // Adds all claim based badges for user.
        $entryCount = $entries->count();
        if ($entryCount >= 50) { $badges[93] = true; }
        elseif ($entryCount >= 20) { $badges[15] = true; }
        elseif ($entryCount >= 8) { $badges[14] = true; }
        elseif ($entryCount >= 2) { $badges[13] = true; }

        // faction badges
        $factions = $context['identities_by_faction'];
        $runnerCounts = ['shaper' => 0, 'criminal' => 0, 'anarch' => 0, 'adam' => 0, 'apex' => 0, 'sunny' => 0];
        $corpCounts = ['nbn' => 0, 'hb' => 0, 'weyland' => 0, 'jinteki' => 0];
        $runnerWinFlags = ['shaper' => false, 'criminal' => false, 'anarch' => false, 'adam' => false, 'apex' => false, 'sunny' => false];
        $corpWinFlags = ['nbn' => false, 'hb' => false, 'weyland' => false, 'jinteki' => false];

        foreach ($entries as $entry) {
            $runnerIdentity = (string) $entry->runner_deck_identity;
            $corpIdentity = (string) $entry->corp_deck_identity;
            $tournamentId = (int) $entry->tournament_id;
            $wonTop = isset($context['tournament_top_set'][$this->setKey($tournamentId)]) && intval($entry->rank_top) === 1;
            $wonNoTop = isset($context['tournament_no_top_set'][$this->setKey($tournamentId)]) && intval($entry->rank) === 1;
            $won = $wonTop || $wonNoTop;

            foreach ($runnerCounts as $key => $count) {
                if (isset($factions[$key][$this->setKey($runnerIdentity)])) {
                    $runnerCounts[$key]++;
                    if ($won) {
                        $runnerWinFlags[$key] = true;
                    }
                }
            }
            foreach ($corpCounts as $key => $count) {
                if (isset($factions[$key][$this->setKey($corpIdentity)])) {
                    $corpCounts[$key]++;
                    if ($won) {
                        $corpWinFlags[$key] = true;
                    }
                }
            }
        }

        // minority report
        if ($runnerCounts['adam'] > 0 || $runnerCounts['apex'] > 0 || $runnerCounts['sunny'] > 0) { $badges[27] = true; }
        // self-modifying personality
        if ($runnerCounts['shaper'] > 0 && $runnerCounts['criminal'] > 0 && $runnerCounts['anarch'] > 0) { $badges[28] = true; }
        // diversified portfolio
        if ($corpCounts['nbn'] > 0 && $corpCounts['hb'] > 0 && $corpCounts['weyland'] > 0 && $corpCounts['jinteki'] > 0) { $badges[29] = true; }
        // mastery badges
        if ($runnerCounts['shaper'] > 4 && $runnerWinFlags['shaper']) { $badges[53] = true; }
        if ($runnerCounts['criminal'] > 4 && $runnerWinFlags['criminal']) { $badges[54] = true; }
        if ($runnerCounts['anarch'] > 4 && $runnerWinFlags['anarch']) { $badges[55] = true; }
        if ($runnerCounts['adam'] > 2 && $runnerWinFlags['adam']) { $badges[73] = true; }
        if ($runnerCounts['apex'] > 2 && $runnerWinFlags['apex']) { $badges[74] = true; }
        if ($runnerCounts['sunny'] > 2 && $runnerWinFlags['sunny']) { $badges[75] = true; }
        if ($corpCounts['nbn'] > 4 && $corpWinFlags['nbn']) { $badges[49] = true; }
        if ($corpCounts['hb'] > 4 && $corpWinFlags['hb']) { $badges[50] = true; }
        if ($corpCounts['weyland'] > 4 && $corpWinFlags['weyland']) { $badges[51] = true; }
        if ($corpCounts['jinteki'] > 4 && $corpWinFlags['jinteki']) { $badges[52] = true; }

        // road to worlds
        if (
            $this->anyEntryMatchesTournamentSet($entries, $context['road_stores_set'], function ($entry) { return intval($entry->rank) > 0; }) &&
            $this->anyEntryMatchesTournamentSet($entries, $context['road_regionals_set'], function ($entry) { return intval($entry->rank) > 0; }) &&
            $this->anyEntryMatchesTournamentSet($entries, $context['road_nationals_set'], function ($entry) { return intval($entry->rank) > 0; })
        ) {
            $badges[34] = true;
        }
        // trapped in time
        if (!empty($context['recurring_ids']) && Entry::where('user', $userId)->whereIn('tournament_id', $context['recurring_ids'])->exists()) {
            $badges[30] = true;
        }
        // charity
        if ($this->anyEntryMatchesTournamentSet($entries, $context['charity_set'], function ($entry) { return intval($entry->rank) > 0; })) {
            $badges[38] = true;
        }
        // champion of sorts
        if ($this->anyEntryMatchesTournamentSet($entries, $context['cos_set'], function ($entry) {
            return intval($entry->rank_top) === 1 || intval($entry->rank) === 1;
        })) {
            $badges[35] = true;
        }

        $countryCount = DB::table('entries')->join('tournaments', 'entries.tournament_id', '=', 'tournaments.id')
            ->where('entries.user', $userId)->where('tournaments.online', 0)->where('tournaments.approved', 1)
            ->whereNull('tournaments.deleted_at')->where('entries.rank', '>', 0)
            ->distinct('tournaments.location_country')->count('tournaments.location_country');
        if ($countryCount >= 3) {
            $badges[36] = true;
        }

        foreach ($context['national_info'] as $nationalInfo) {
            if ($nationalInfo['winner_user'] === $userId) {
                $badges[$nationalInfo['winner_badge_id']] = true;
            } elseif (isset($nationalInfo['participants'][$this->setKey($userId)])) {
                $badges[$nationalInfo['participant_badge_id']] = true;
            }
        }

        // Adds, removes badges related to tournament organizing.
        $createdApprovedTournaments = Tournament::where('creator', $userId)->where('approved', 1)
            ->get(['id', 'import', 'description', 'tournament_type_id', 'concluded']);
        $createdCount = $createdApprovedTournaments->count();
        if ($createdCount >= 50) { $badges[94] = true; } // PLATINUM T.O.
        elseif ($createdCount >= 20) { $badges[18] = true; } // GOLD T.O.
        elseif ($createdCount >= 8) { $badges[17] = true; } // SILVER T.O.
        elseif ($createdCount >= 2) { $badges[16] = true; } // BRONZE T.O.

        if ($createdApprovedTournaments->where('import', 1)->count() >= 3) { $badges[26] = true; } // NRTM preacher
        if ($createdApprovedTournaments->where('import', 4)->count() >= 3) { $badges[111] = true; } // Snek Majesty
        foreach ($createdApprovedTournaments as $tournament) {
            if (strlen((string) $tournament->description) > 600 &&
                preg_match('/[^!]\[([^\]]+)\]\(([^)]+)\)/', $tournament->description) &&
                preg_match('/!\[([^\]]*)\]\(([^)]+)\)/', $tournament->description)) {
                $badges[20] = true; // Fancy T.O.
                break;
            }
        }

        $concludedByType = $createdApprovedTournaments->where('concluded', 1)->groupBy('tournament_type_id')
            ->map(function ($items) { return $items->count() > 0; });
        if (($concludedByType[2] ?? false) && ($concludedByType[3] ?? false) && ($concludedByType[4] ?? false)) {
            $badges[37] = true; // community champion
        }

        $createdTournamentIds = $createdApprovedTournaments->pluck('id')->all();
        if (count($createdTournamentIds)) {
            $communityCount = Entry::whereIn('tournament_id', $createdTournamentIds)->whereIn('type', [3, 4])
                ->where('user', '!=', $userId)->distinct()->count('user');
            if ($communityCount > 29) { $badges[68] = true; } // higher community builder
            elseif ($communityCount > 9) { $badges[48] = true; } // community builder
        }

        // Adds NetrunnerDB related badges to user.
        // These badges are never removed.
        if ($user->published_decks >= 20) { $badges[21] = true; } // Hard-working publisher
        if ($user->private_decks >= 150) { $badges[25] = true; } // Keeper of many secrets
        if ($user->reputation >= 5000) { $badges[39] = true; } // NetrunnerDB Superstar
        elseif ($user->reputation >= 1000) { $badges[31] = true; } // NetrunnerDB VIP
        elseif ($user->reputation >= 500) { $badges[32] = true; } // NetrunnerDB Celeb
        elseif ($user->reputation >= 100) { $badges[33] = true; } // NetrunnerDB Known
        // ABR birthday badge
        if ((!is_null($user->created_at)) && ($user->created_at->format('Y-m-d') <= (date('Y') - 1).date('-m-d'))) {
            $badges[72] = true;
        }

        if (Video::where('user_id', $userId)->where('flag_removed', false)->count() >= 5) { $badges[47] = true; }
        if (VideoTag::where('user_id', $userId)->count() > 4) { $badges[64] = true; }

        return [
            'badges' => $badges,
            'has_featured_tournament' => Tournament::where('creator', $userId)->where('featured', 1)->exists(),
        ];
    }

    public function computeScope(User $user, array $context, string $scope): array
    {
        $full = $this->computeAll($user, $context)['badges'];
        $scopeIds = $this->scopeBadgeIds($context, $scope);
        $scoped = [];
        foreach ($scopeIds as $badgeId) {
            $scoped[$badgeId] = $full[$badgeId] ?? false;
        }
        return $scoped;
    }

    private function scopeBadgeIds(array $context, string $scope): array
    {
        if ($scope === 'claim') {
            return $context['claim_badge_ids'];
        }
        if ($scope === 'to') {
            return self::TO_BADGES;
        }
        if ($scope === 'ndb') {
            return self::NDB_BADGES;
        }
        if ($scope === 'video') {
            return self::VIDEO_BADGES;
        }
        if ($scope === 'community') {
            return self::COMMUNITY_BADGES;
        }
        if ($scope === 'sensie') {
            return self::SENSIE_BADGES;
        }
        return [];
    }

    private function buildIdSet(array $ids): array
    {
        $set = [];
        foreach ($ids as $id) {
            $set[$this->setKey($id)] = true;
        }
        return $set;
    }

    private function setKey($id): string
    {
        return 'k:'.(string) $id;
    }

    private function badgeIdFromLookup(array $context, int $type, $year, int $winlevel): ?int
    {
        $yearKey = is_null($year) ? 'null' : (string) $year;
        if (isset($context['badge_lookup'][$type][$yearKey][$winlevel])) {
            return intval($context['badge_lookup'][$type][$yearKey][$winlevel]);
        }
        if (is_null($year) && isset($context['badge_lookup_any_year'][$type][$winlevel])) {
            return intval($context['badge_lookup_any_year'][$type][$winlevel]);
        }
        return null;
    }

    private function anyEntryMatchesTournamentSet($entries, array $tournamentSet, ?callable $predicate = null): bool
    {
        foreach ($entries as $entry) {
            if (!isset($tournamentSet[$this->setKey($entry->tournament_id)])) {
                continue;
            }
            if (is_null($predicate) || $predicate($entry)) {
                return true;
            }
        }
        return false;
    }
}
