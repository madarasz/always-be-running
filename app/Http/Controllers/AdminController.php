<?php

namespace App\Http\Controllers;

use App\CardCycle;
use App\CardIdentity;
use App\CardPack;
use App\Badge;
use App\Photo;
use App\TournamentType;
use App\User;
use App\Entry;
use App\Video;
use App\Tournament;
use App\VideoTag;
use App\Mwl;
use App\PrizeElement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

use App\Http\Requests;

class AdminController extends Controller
{

    public function lister(Request $request)
    {
        $this->authorize('admin', Tournament::class, $request->user());

        // collecting data for admin page
        $nowdate = date('Y.m.d.');
        $message = session()->has('message') ? session('message') : '';
        $cycles = CardCycle::orderBy('position', 'desc')->get();
        $packsByCycle = CardPack::orderBy('position', 'desc')->get()->groupBy('cycle_code');
        $packs = [];
        foreach ($cycles as $cycle) {
            $packs[] = $packsByCycle->get($cycle->id, collect());
        }
        $count_ids = CardIdentity::count();
        $lastIdentity = CardIdentity::orderBy('id', 'desc')->select('title')->first();
        $last_id = $lastIdentity ? $lastIdentity->title : '';
        $count_cycles = $cycles->count();
        $last_cycle = $count_cycles > 1 ? $cycles[1]->name : '';
        $count_packs = CardPack::count();
        $count_mwls = Mwl::count();
        $lastMwl = Mwl::orderBy('id', 'desc')->select('name')->first();
        $last_mwl = $lastMwl ? $lastMwl->name : '';

        $video_channels = Video::where('videos.flag_removed', false)
            ->whereExists(function ($query) {
                $query->select(DB::raw(1))
                    ->from('tournaments')
                    ->whereColumn('tournaments.id', 'videos.tournament_id')
                    ->where('tournaments.approved', 1)
                    ->whereNull('tournaments.recur_weekly');
            })
            ->select('channel_name', 'type', DB::raw('count(*) as total'))
            ->groupBy('channel_name', 'type')
            ->orderBy('total', 'desc')
            ->get();

        $video_users = Video::where('videos.flag_removed', false)
            ->whereExists(function ($query) {
                $query->select(DB::raw(1))
                    ->from('tournaments')
                    ->whereColumn('tournaments.id', 'videos.tournament_id')
                    ->where('tournaments.approved', 1)
                    ->whereNull('tournaments.recur_weekly');
            })
            ->join('users', 'users.id', '=', 'videos.user_id')
            ->select(
                'users.id',
                DB::raw("COALESCE(NULLIF(users.username_preferred, ''), users.name) as display_name"),
                'users.supporter',
                'users.admin',
                'users.artist_id',
                DB::raw('count(*) as total')
            )
            ->groupBy('users.id', 'users.username_preferred', 'users.name', 'users.supporter', 'users.admin', 'users.artist_id')
            ->orderBy('total', 'desc')
            ->get();
        foreach ($video_users as $videoUser) {
            $videoUser->abr_link_class = $this->userLinkClassFromFields($videoUser->admin, $videoUser->artist_id, $videoUser->supporter);
        }

        $video_users_tagged = VideoTag::where('video_tags.user_id', '>', 0)
            ->join('users', 'users.id', '=', 'video_tags.user_id')
            ->select(
                'users.id',
                DB::raw("COALESCE(NULLIF(users.username_preferred, ''), users.name) as display_name"),
                'users.supporter',
                'users.admin',
                'users.artist_id',
                DB::raw('count(*) as total')
            )
            ->groupBy('users.id', 'users.username_preferred', 'users.name', 'users.supporter', 'users.admin', 'users.artist_id')
            ->orderBy('total', 'desc')
            ->get();
        foreach ($video_users_tagged as $taggedUser) {
            $taggedUser->abr_link_class = $this->userLinkClassFromFields($taggedUser->admin, $taggedUser->artist_id, $taggedUser->supporter);
        }

        $entry_types = $this->addEntryTypeNames(Entry::select('type', DB::raw('count(*) as total'))
            ->whereExists(function ($query) {
                $query->select(DB::raw(1))
                    ->from('tournaments')
                    ->whereColumn('tournaments.id', 'entries.tournament_id')
                    ->where('tournaments.approved', 1)
                    ->whereNull('tournaments.recur_weekly');
            })
            ->groupBy('type')->pluck('total', 'type')->toArray());
        $published_count = Entry::where('runner_deck_type', 1)->count() + Entry::where('corp_deck_type', 1)->count();
        $private_count = Entry::where('runner_deck_type', 2)->count() + Entry::where('corp_deck_type', 2)->count();
        $backlink_count = Entry::where('netrunnerdb_claim_runner', '>', 0)->count() +
            Entry::where('netrunnerdb_claim_corp', '>', 0)->count();
        $no_backlink_count = Entry::where('netrunnerdb_claim_runner', '=', 0)->count() +
            Entry::where('netrunnerdb_claim_corp', '=', 0)->count();
        $unexported_count = Entry::where('runner_deck_id', '>', 0)->whereNull('netrunnerdb_claim_runner')->count() +
            Entry::where('corp_deck_id', '>', 0)->whereNull('netrunnerdb_claim_corp')->count();
        $broken_count = Entry::where('broken_runner', '>', 0)->count() + Entry::where('broken_corp', '>', 0)->count();
        $broken_user_ids = Entry::where(function ($query) {
                $query->where('broken_runner', true)->orWhere('broken_corp', true);
            })
            ->where('user', '>', 0)
            ->distinct()
            ->pluck('user')
            ->all();
        $broken_users = User::whereIn('id', $broken_user_ids)->get();
        $photos = Photo::orderBy('id', 'desc')->limit(16)->get();
        $photo_tournaments = Photo::join('tournaments', 'tournaments.id', '=', 'photos.tournament_id')
            ->where('tournaments.approved', 1)
            ->whereNull('tournaments.recur_weekly')
            ->select('tournaments.id as tournament_id', 'tournaments.title', DB::raw('count(*) as total'))
            ->groupBy('tournaments.id', 'tournaments.title')
            ->orderBy('total', 'desc')
            ->get();
        $photo_users = Photo::join('tournaments', 'tournaments.id', '=', 'photos.tournament_id')
            ->join('users', 'users.id', '=', 'photos.user_id')
            ->where('tournaments.approved', 1)
            ->whereNull('tournaments.recur_weekly')
            ->select(
                'users.id',
                DB::raw("COALESCE(NULLIF(users.username_preferred, ''), users.name) as display_name"),
                'users.supporter',
                'users.admin',
                'users.artist_id',
                DB::raw('count(*) as total')
            )
            ->groupBy('users.id', 'users.username_preferred', 'users.name', 'users.supporter', 'users.admin', 'users.artist_id')
            ->orderBy('total', 'desc')
            ->get();
        foreach ($photo_users as $photoUser) {
            $photoUser->abr_link_class = $this->userLinkClassFromFields($photoUser->admin, $photoUser->artist_id, $photoUser->supporter);
        }

        $missing_videos = Video::where('flag_removed', true)
            ->with(['tournament' => function ($query) {
                $query->select('id', 'date', 'title');
            }])
            ->get();
        $tournament_types = TournamentType::where('order', '<', 7)->orderBy('order')->pluck('type_name', 'id');
        $tournament_types->put(0,'other'); // adding 'other' to prize types
        $art_types = PrizeElement::groupBy('type')
            ->orderBy(\DB::raw('count(type)'), 'DESC')
            ->pluck('type')
            ->all();
        // VIP information
        $vipBadgeIds = [1, 2, 8, 9, 10, 11, 14, 15, 17, 18, 31, 39, 48, 56, 57, 59, 60, 61, 62];
        $vip_ids = DB::table('badge_user')
            ->whereIn('badge_id', $vipBadgeIds)
            ->distinct()
            ->pluck('user_id');
        $vips = User::whereIn('id', $vip_ids)
            ->with('country')
            ->withCount(['claims', 'tournamentsCreated', 'badges'])
            ->get();
        $claimersCount = DB::table('tournaments')
            ->join('entries', 'entries.tournament_id', '=', 'tournaments.id')
            ->whereIn('tournaments.creator', $vip_ids->all())
            ->whereIn('entries.type', [3, 4])
            ->whereColumn('entries.user', '!=', 'tournaments.creator')
            ->select('tournaments.creator as user_id', DB::raw('count(distinct entries.user) as claimers_count'))
            ->groupBy('tournaments.creator')
            ->pluck('claimers_count', 'user_id');
        foreach ($vips as $vip) {
            $vip->claimers_count = intval($claimersCount[$vip->id] ?? 0);
            $vip->abr_link_class = $this->userLinkClassFromFields($vip->admin, $vip->artist_id, $vip->supporter);
        }

        // Know the Meta update calculation
        $ktm_metas = Cache::remember('admin_ktm_metas', 300, function () {
            $rawMetas = file_get_contents('https://alwaysberunning.net/ktm/metas.json');
            if (!is_string($rawMetas) || !json_validate($rawMetas)) {
                return [];
            }

            return json_decode($rawMetas, true);
        });
        $ktm_packs = [];
        $ktm_update = data_get($ktm_metas, '0.lastUpdate', '2000-01-01 12:00:00');
        $ktmMetaByCardpool = collect($ktm_metas)->keyBy('cardpool');
        foreach($packs as $cycle) {
            foreach($cycle as $pack) {
                if ($pack['date_release'] > '2019-12-30') {
                    $update_stamp = data_get($ktmMetaByCardpool->get($pack->name), 'lastUpdate', '2000-01-01 12:00:00');
                    $packStats = Entry::join('tournaments', 'entries.tournament_id', '=', 'tournaments.id')
                        ->where('tournaments.cardpool_id', $pack->id)
                        ->where('entries.updated_at', '>', $update_stamp)
                        ->selectRaw("
                            SUM(CASE WHEN entries.type IN (1, 11, 12, 13, 3, 4) THEN 1 ELSE 0 END) as entries_total,
                            SUM(CASE WHEN entries.type = 3 THEN 1 ELSE 0 END) as decks_total
                        ")
                        ->first();
                    $pack_entries = intval($packStats->entries_total ?? 0);
                    $pack_decks = intval($packStats->decks_total ?? 0);
                    if ($pack_entries) {
                        $ktm_packs[$pack->name] = [$pack_entries, $pack_decks];
                    }
                }
            }
        }

        // determine last pack name, $pack[0] is 'draft'
        if ($count_packs > 1 && $count_cycles > 1) {
            if (count($packs[1])) {
                $last_pack = $packs[1][0]->name;
            } else {
                $last_pack = $packs[2][0]->name;
            }
        } else {
            $last_pack = '';
        }

        $badge_type_count = Badge::count();
        $badge_count = DB::table('badge_user')->count();
        $unseen_badge_count = DB::table('badge_user')->where('seen', 0)->count();
        $badge_refresh_run = $request->query('badge_refresh_run');

        $user = $request->user();
        $page_section = 'admin';
        return view('admin', compact('user', 'message', 'nowdate', 'badge_type_count', 'badge_count', 'unseen_badge_count',
            'count_ids', 'last_id', 'count_packs', 'last_pack', 'count_cycles', 'last_cycle', 'count_mwls', 'last_mwl', 'packs', 'cycles',
            'page_section', 'video_channels', 'video_users', 'entry_types', 'published_count', 'private_count',
            'backlink_count', 'no_backlink_count', 'unexported_count', 'broken_count', 'broken_users', 'missing_videos',
            'ktm_update', 'ktm_packs', 'photos', 'photo_tournaments', 'photo_users', 'video_users_tagged', 'vips',
            'badge_refresh_run',
            'tournament_types', 'art_types'));
    }

    public function approveTournament($id, Request $request)
    {
        return $this->approval($id, 1, 'Tournament approved.', $request);
    }

    public function rejectTournament($id, Request $request)
    {
        return $this->approval($id, 0, 'Tournament rejected.', $request);
    }

    private function approval($id, $outcome, $message, $request)
    {
        $this->authorize('admin', Tournament::class, $request->user());
        $tournament = Tournament::findorFail($id);
        $tournament->approved = $outcome;
        $tournament->save();
        // update badges
        App('App\Http\Controllers\BadgeController')->addTOBadges($tournament->creator);

        return back()->with('message', $message);
    }

    public function restoreTournament($id, Request $request)
    {
        $this->authorize('admin', Tournament::class, $request->user());
        Tournament::withTrashed()->where('id', $id)->restore();
        return back()->with('message', 'Tournament restored');
    }

    public function enablePack($id, Request $request) {
        return $this->changePackUsage($id, 1, 'Card pack enabled.', $request);
    }

    public function disablePack($id, Request $request) {
        return $this->changePackUsage($id, 0, 'Card pack disabled.', $request);
    }

    private function changePackUsage($id, $outcome, $message, $request) {
        $this->authorize('admin', Tournament::class, $request->user());
        $pack = CardPack::findorFail($id);
        $pack->usable = $outcome;
        $pack->save();
        return back()->with('message', $message);
    }

    /**
     * Recalculates "type" field for all entries.
     * @param $request
     * @return mixed
     */
    public function setEntryTypes(Request $request) {
        $this->authorize('admin', Tournament::class, $request->user());
        // user registered for tournament
        Entry::where('user', '>', 0)->whereNull('rank')->update(['type' => 0]);
        // imported entries
        $nrtm_tournaments = Tournament::where('import', 1)->pluck('id')->all();
        $csv_tournaments = Tournament::where('import', 2)->pluck('id')->all();
        $manual_tournaments = Tournament::where('import', 3)->pluck('id')->all();
        Entry::whereIn('tournament_id', $nrtm_tournaments)->where('user', 0)->update(['type' => 11]);
        Entry::whereIn('tournament_id', $csv_tournaments)->where('user', 0)->update(['type' => 12]);
        Entry::whereIn('tournament_id', $manual_tournaments)->where('user', 0)->update(['type' => 13]);
        // claims with decklists
        Entry::where('runner_deck_id', '>', 0)->where('corp_deck_id', '>', 0)->update(['type' => 3]);
        return back()->with('message', 'Entries updated with types');
    }

    /**
     * Flags broken decks
     * @param Request $request
     * @return mixed
     */
    public function detectBrokenDecks(Request $request) {
        $this->authorize('admin', Tournament::class, $request->user());

        $deletedOrRejected = Tournament::withTrashed()->where(function($q) {
            $q->where('approved', '=', 0)->orWhere('deleted_at', '>', 0);
        })->pluck('id')->all();

        // find claims to export
        $claims = Entry::where('user', '>', 0)->where('rank', '>', 0)
            ->where('runner_deck_id', '>', 0)->where('corp_deck_id', '>', 0)
            ->whereNotIn('tournament_id', $deletedOrRejected)->get();

        $count = 0;
        foreach($claims as $claim) {
            // runner
            if (app('App\Http\Controllers\NetrunnerDBController')
                ->isDeckLinkBroken($claim->runner_deck_type == 1, $claim->runner_deck_id)) {
                $claim->broken_runner = true;
                $claim->save();
                $count++;
            } elseif ($claim->broken_runner) {
                $claim->broken_runner = false;
                $claim->save();
                $count--;
            }
            // corp
            if (app('App\Http\Controllers\NetrunnerDBController')
                ->isDeckLinkBroken($claim->corp_deck_type == 1, $claim->corp_deck_id)) {
                $claim->broken_corp = true;
                $claim->save();
                $count++;
            } elseif ($claim->broken_corp) {
                $claim->broken_corp = false;
                $claim->save();
                $count--;
            }
        }

        return back()->with('message', 'Broken decks flagged: '.$count);
    }

    public function adminStats(Request $request) {
        $this->authorize('admin', Tournament::class, $request->user());
        $entries = $this->getWeekNumber(DB::table('entries')->select('created_at as week', DB::raw('count(*) as total'))
            ->where('user', '>', 0)->whereNotNull('created_at')
            ->groupBy(DB::raw('CONCAT(YEAR(created_at), WEEK(created_at, 3))'))->get(), 'week');
        $tournaments = $this->getWeekNumber(DB::table('tournaments')->select('created_at as week', DB::raw('count(*) as total'))
            ->where('approved', 1)->whereNotNull('created_at')
            ->groupBy(DB::raw('CONCAT(YEAR(created_at), WEEK(created_at, 3))'))->get(), 'week');
        $users = $this->getWeekNumber(DB::table('users')->select('created_at as week', DB::raw('count(*) as total'))
            ->whereNotNull('created_at')
            ->groupBy(DB::raw('CONCAT(YEAR(created_at), WEEK(created_at, 3))'))->get(), 'week');
        $countries = Tournament::where('approved', 1)->select('location_country', DB::raw('count(*) as total'))
            ->groupBy('location_country')->orderBy('total', 'desc')->get();
        $result = [
            'totalEntries' => Entry::where('user', '>', 0)->whereNotNull('created_at')->count(),
            'newEntriesByWeek' => $entries,
            'totalTournaments' => Tournament::where('approved', 1)->whereNotNull('created_at')->count(),
            'newTournamentsByWeek' => $tournaments,
            'totalUsers' => User::whereNotNull('created_at')->count(),
            'newUsersByWeek' => $users,
            'countries' => $countries
        ];
        return response()->json($result);
    }

    public function adminStatsPerCountry(Request $request, $country) {
        $fromDate = '2017.10.06'; // constant, stats from this date

        $this->authorize('admin', Tournament::class, $request->user());
        $newTournaments = $this->getWeekNumber(DB::table('tournaments')->select('created_at as week', DB::raw('count(*) as total'))
            ->where('approved', 1)->where('created_at', '>', $fromDate)->where('location_country', $country)
            ->groupBy(DB::raw('CONCAT(YEAR(created_at), WEEK(created_at, 3))'))->get(), 'week');
        $concludedTournaments = $this->getWeekNumber(DB::table('tournaments')->select('concluded_at as week', DB::raw('count(*) as total'))
            ->where('approved', 1)->where('concluded_at', '>', $fromDate)->where('location_country', $country)
            ->groupBy(DB::raw('CONCAT(YEAR(concluded_at), WEEK(concluded_at, 3))'))->get(), 'week');
        $claims = $this->getWeekNumber(DB::table('entries')->select('entries.updated_at as week', DB::raw('count(*) as total'))
            ->leftJoin('tournaments', 'entries.tournament_id', '=', 'tournaments.id')->where('location_country', $country)
            ->whereIn('entries.type', [3, 4])->where('entries.updated_at', '>', $fromDate)
            ->groupBy(DB::raw('CONCAT(YEAR(entries.updated_at), WEEK(entries.updated_at, 3))'))->get(), 'week');
        $importedEntries = $this->getWeekNumber(DB::table('entries')->select('entries.updated_at as week', DB::raw('count(*) as total'))
            ->leftJoin('tournaments', 'entries.tournament_id', '=', 'tournaments.id')->where('location_country', $country)
            ->whereIn('entries.type', [11, 12, 13, 14])->where('entries.updated_at', '>', $fromDate)
            ->groupBy(DB::raw('CONCAT(YEAR(entries.updated_at), WEEK(entries.updated_at, 3))'))->get(), 'week');

        $result = [
            'newTournaments' => $newTournaments,
            'concludedTournaments' => $concludedTournaments,
            'claims' => $claims,
            'importedEntries' => $importedEntries
        ];
        return response()->json($result);
    }

    public function toggleFeatured(Request $request, $id) {
        if ($request->user()->id != 1276) {
            abort(403);
        }
        $tournament = Tournament::findOrFail($id);
        $message = $tournament->featured ? 'Tournament removed from featured.' : 'Tournament added to featured.';
        $tournament->featured = $tournament->featured ? 0 : 1;
        $tournament->save();

        // badge
        if ($tournament->featured) {
            App('App\Http\Controllers\BadgeController')->addFeaturedBadge($tournament->creator);
        }

        return back()->with('message', $message);
    }

    private function getWeekNumber($array, $datefield) {
        $result = [];
        foreach($array as $element) {
            $row = (array)$element;
            $date = new \DateTime($row[$datefield]);
            $row[$datefield] = $date->format('YW');
            array_push($result, $row);
        }
        return $result;
    }

    /**
     * Adds entry type names.
     * @param $array
     * @return array
     */
    private function addEntryTypeNames($array) {
        $result = [];
        $typeNames = [
            '' => 'undefinied',
            0 => 'registered for tournament',
            11 => 'imported by NRTM',
            12 => 'imported by CSV',
            13 => 'imported manually',
            14 => 'imported by Cobr.ai',
            2 => 'registered with decklist',
            3 => 'claim with decklist',
            4 => 'claim without decklist'
        ];
        foreach ($typeNames as $typeCode => $typeName) {
            if (array_key_exists($typeCode, $array)) {
                $result[$typeName] = $array[$typeCode];
            }
        }
        return $result;
    }

    private function userLinkClassFromFields($admin, $artistId, $supporter) {
        if ($admin) {
            return 'admin';
        }
        if (!is_null($artistId)) {
            return 'artist';
        }
        if ($supporter) {
            return 'supporter';
        }
        return '';
    }
}
