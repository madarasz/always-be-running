<?php

namespace App\Http\Controllers;

use App\CardCycle;
use App\CardIdentity;
use App\CardPack;
use App\Badge;
use App\User;
use App\Entry;
use App\Video;
use App\Tournament;
use Illuminate\Http\Request;
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
        $packs = [];
        foreach ($cycles as $cycle) {
            array_push($packs, CardPack::where('cycle_code', $cycle->id)->orderBy('position', 'desc')->get());
        }
        $count_ids = CardIdentity::count();
        $last_id = $count_ids > 0 ? CardIdentity::orderBy('id', 'desc')->first()->title : '';
        $count_cycles = count($cycles);
        $last_cycle = $count_cycles > 1 ? $cycles[1]->name : '';
        $count_packs = CardPack::count();
        $approved_tournaments = Tournament::where('approved', 1)->whereNull('recur_weekly')->pluck('id')->all(); // + non-recurring
        $video_channels = Video::whereIn('tournament_id', $approved_tournaments)
            ->select('channel_name', DB::raw('count(*) as total'))
            ->groupBy('channel_name')->orderBy('total', 'desc')->pluck('total', 'channel_name');
        $video_users = Video::whereIn('tournament_id', $approved_tournaments)
            ->select('user_id', DB::raw('count(*) as total'))
            ->groupBy('user_id')->orderBy('total', 'desc')->pluck('total', 'user_id');
        $entry_types = $this->addEntryTypeNames(Entry::select('type', DB::raw('count(*) as total'))
            ->whereIn('tournament_id', $approved_tournaments)->groupBy('type')->pluck('total', 'type')->toArray());
        $published_count = Entry::where('runner_deck_type', 1)->count() + Entry::where('corp_deck_type', 1)->count();
        $private_count = Entry::where('runner_deck_type', 2)->count() + Entry::where('corp_deck_type', 2)->count();
        $backlink_count = Entry::where('netrunnerdb_claim_runner', '>', 0)->count() +
            Entry::where('netrunnerdb_claim_corp', '>', 0)->count();
        $no_backlink_count = Entry::where('netrunnerdb_claim_runner', '=', 0)->count() +
            Entry::where('netrunnerdb_claim_corp', '=', 0)->count();
        $unexported_count = Entry::where('runner_deck_id', '>', 0)->whereNull('netrunnerdb_claim_runner')->count() +
            Entry::where('corp_deck_id', '>', 0)->whereNull('netrunnerdb_claim_corp')->count();
        $broken_count = Entry::where('broken_runner', '>', 0)->count() + Entry::where('broken_corp', '>', 0)->count();
        $broken_user_ids = Entry::where('broken_runner', true)->orWhere('broken_corp', true)->pluck('user')->all();
        $broken_users = User::whereIn('id', $broken_user_ids)->get();

        // Know the Meta update calculation
        $ktm_update = preg_replace('/\./i', '-', file_get_contents('http://www.knowthemeta.com/LastUpdate/'));
        $ktm_packs = [];
        foreach($packs as $cycle) {
            foreach($cycle as $pack) {
                $pack_tournaments = Tournament::where('cardpool_id', $pack->toArray()['id'])->pluck('id')->all();
                if (count($pack_tournaments)) {
                    $pack_entries = Entry::whereIn('type', [1, 11, 12, 13, 3, 4])->where('updated_at', '>', $ktm_update)
                        ->whereIn('tournament_id', $pack_tournaments)->count();
                    $pack_decks = Entry::where('type', 3)->where('updated_at', '>', $ktm_update)
                        ->whereIn('tournament_id', $pack_tournaments)->count();
                    if ($pack_entries) {
                        $ktm_packs[$pack->toArray()['name']] = [$pack_entries, $pack_decks];
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

        $page_section = 'admin';
        return view('admin', compact('user', 'message', 'nowdate', 'badge_type_count', 'badge_count', 'unseen_badge_count',
            'count_ids', 'last_id', 'count_packs', 'last_pack', 'count_cycles', 'last_cycle', 'packs', 'cycles',
            'page_section', 'video_channels', 'video_users', 'entry_types', 'published_count', 'private_count',
            'backlink_count', 'no_backlink_count', 'unexported_count', 'broken_count', 'broken_users',
            'ktm_update', 'ktm_packs'));
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
            ->where('user', '>', 0)->whereNotNull('created_at')->groupBy(DB::raw('WEEK(created_at, 3)'))->get(), 'week');
        $tournaments = $this->getWeekNumber(DB::table('tournaments')->select('created_at as week', DB::raw('count(*) as total'))
            ->where('approved', 1)->whereNotNull('created_at')->groupBy(DB::raw('WEEK(created_at, 3)'))->get(), 'week');
        $users = $this->getWeekNumber(DB::table('users')->select('created_at as week', DB::raw('count(*) as total'))
            ->whereNotNull('created_at')->groupBy(DB::raw('WEEK(created_at, 3)'))->get(), 'week');
        $countries = Tournament::where('approved', 1)->select('location_country', DB::raw('count(*) as total'))
            ->groupBy('location_country')->get();
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
        $typeNames = ['' => 'undefinied', 0 => 'registered for tournament', 11 => 'imported by NRTM', 12 => 'imported by CSV',
            13 => 'imported manually', 2 => 'registered with decklist', 3 => 'claim with decklist', 4 => 'claim without decklist'];
        foreach ($typeNames as $typeCode => $typeName) {
            if (array_key_exists($typeCode, $array)) {
                $result[$typeName] = $array[$typeCode];
            }
        }
        return $result;
    }
}
