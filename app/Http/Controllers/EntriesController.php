<?php

namespace App\Http\Controllers;

use App\Http\Requests\EntryRequest;
use App\Http\Requests\EntryNoDeckRequest;
use App\Tournament;
use App\Entry;
use App\User;
use App\CardIdentity;
use Illuminate\Http\Request;

use App\Http\Requests;

class EntriesController extends Controller
{
    public function register(Request $request, $id)
    {
        $this->authorize('logged_in', Tournament::class, $request->user());
        $user_id = $request->user()->id;
        $entry = Entry::where('user', $user_id)->where('tournament_id', $id)->first();
        if (is_null($entry)) {
            Entry::create([
                'user' => $request->user()->id,
                'approved' => 1,
                'type' => 0,
                'tournament_id' => $id
            ]);
        }

        // add badges for registration on recurring
        if (Tournament::withTrashed()->where('id', $id)->whereNull('date')->first()) {
            App('App\Http\Controllers\BadgeController')->addClaimBadges($user_id);
        }

        return redirect()->back()->with('message', 'You have been registered for the tournament.');
    }

    public function unregister(Request $request, $id)
    {
        $this->authorize('logged_in', Tournament::class, $request->user());
        $user_id = $request->user()->id;
        $entry = Entry::where('user', $user_id)->where('tournament_id', $id)->first();
        if (!is_null($entry)) {
            Entry::destroy($entry->id);
        }

        // remove badges if needed
        if (Tournament::withTrashed()->where('id', $id)->whereNull('date')->first()) {
            App('App\Http\Controllers\BadgeController')->addClaimBadges($user_id);
        }

        return redirect()->back()->with('message', 'You have unregistered from the tournament.');
    }

    public function claim(EntryRequest $request, $id)
    {
        $this->authorize('logged_in', Tournament::class, $request->user());
        $user_id = $request->user()->id;
        $tournament = Tournament::withTrashed()->findOrFail($id);
        $rank = $request->rank_top ? $request->rank_top : $request->rank;

        // claim by own decks / decks by IDs, publish if needed
        try {
            $corp_deck = $this->getDeckInfo($request->corp_deck, $request->other_corp_deck, $request->auto_publish,
                $request->netrunnerdb_link, $rank, $tournament, "corp", $user_id);
            $runner_deck = $this->getDeckInfo($request->runner_deck, $request->other_runner_deck, $request->auto_publish,
                $request->netrunnerdb_link, $rank, $tournament, "runner", $user_id);
        } catch (\Exception $e) {
            return redirect()->back()->withErrors([$e->getMessage()]);
        }

        // getting registration for tournament or imported entry
        $reg_entry = Entry::where('user', $user_id)->where('tournament_id', $id)->first();

        // with top
        if ($tournament->top_number && $request->rank_top) {
            $import_entry = Entry::where('tournament_id', $id)->where('user', 0)->where(function ($q) use ($request) {
                $q->where('rank', $request->rank)->orWhere('rank_top', $request->rank_top);
            })->first();
        } else { // without top
            $import_entry = Entry::where('tournament_id', $id)->where('user', 0)->where('rank', $request->rank)->first();
        }

        // top rank null adjust
        if (!is_null($import_entry) && is_null($import_entry->rank_top)) {
            $import_entry->rank_top = 0;
        }

	    // merging with import entry
        if (!is_null($import_entry) &&     // if there is an import entry
            ($import_entry->runner_deck_identity == $runner_deck['identity'] || strlen($import_entry->runner_deck_identity) < 1) &&   // and IDs match
            ($import_entry->corp_deck_identity == $corp_deck['identity'] || strlen($import_entry->corp_deck_identity) < 1) &&
            $import_entry->rank == $request->rank && (!$tournament->top_number || $import_entry->rank_top == $request->rank_top)) // and rank, top_rank match
        {
            Entry::destroy($import_entry->id);    // delete import entry
            $merge_name = $import_entry->import_username;
        } else {
            $merge_name = null;
        }

        $entry = [
            'rank' => $request->rank,
            'rank_top' => $request->rank_top,
            'corp_deck_id' => $corp_deck['id'],
            'corp_deck_title' => $corp_deck['title'],
            'corp_deck_identity' => $corp_deck['identity'],
            'corp_deck_type' => $corp_deck['type'],
            'runner_deck_id' => $runner_deck['id'],
            'runner_deck_title' => $runner_deck['title'],
            'runner_deck_identity' => $runner_deck['identity'],
            'runner_deck_type' => $runner_deck['type'],
            'import_username' => $merge_name,
            'broken_runner' => 0,
            'broken_corp' => 0,
            'type' => 3
        ];

        // NetrunnerDB claims
        if (array_key_exists('netrunnerdb_claim', $corp_deck)) {
            $entry['netrunnerdb_claim_corp'] = $corp_deck['netrunnerdb_claim'];
        }
        if (array_key_exists('netrunnerdb_claim', $runner_deck)) {
            $entry['netrunnerdb_claim_runner'] = $runner_deck['netrunnerdb_claim'];
        }

        if (is_null($reg_entry)) {   // new claim
            Entry::create([         // additional fields
                'user' => $request->user()->id,
                'approved' => 1,
                'tournament_id' => $id
            ] + $entry);
        } else {    // merging with registration
            $reg_entry->update($entry);
        }

        // add conflict if needed
        $tournament->updateConflict();

        // add badges
        App('App\Http\Controllers\BadgeController')->addClaimBadges($request->user()->id);
        App('App\Http\Controllers\BadgeController')->addCommunityBuilder($tournament->creator);

        return redirect()->back()->with('message', 'You have claimed a spot on the tournament.');
    }

    public function claimWithoutDecks(EntryNoDeckRequest $request, $id)
    {
        $this->authorize('logged_in', Tournament::class, $request->user());
        $user_id = $request->user()->id;
        $tournament = Tournament::withTrashed()->findOrFail($id);

        // getting registration for tournament or imported entry
        $reg_entry = Entry::where('user', $user_id)->where('tournament_id', $id)->first();

        // with top
        if ($tournament->top_number && $request->rank_top_nodeck) {
            $import_entry = Entry::where('tournament_id', $id)->where('user', 0)->where(function ($q) use ($request) {
                $q->where('rank', $request->rank_nodeck)->orWhere('rank_top', $request->rank_top_nodeck);
            })->first();
        } else { // without top
            $import_entry = Entry::where('tournament_id', $id)->where('user', 0)->where('rank', $request->rank_nodeck)->first();
        }

        // top rank null adjust
        if (!is_null($import_entry) && is_null($import_entry->rank_top)) {
            $import_entry->rank_top = 0;
        }

        // merging with import entry
        if (!is_null($import_entry) &&     // if there is an import entry
            ($import_entry->runner_deck_identity == $request['runner_deck_identity'] || strlen($import_entry->runner_deck_identity) < 1) &&   // and IDs match
            ($import_entry->corp_deck_identity == $request['corp_deck_identity'] || strlen($import_entry->corp_deck_identity) < 1) &&
            $import_entry->rank == $request->rank_nodeck && (!$tournament->top_number || $import_entry->rank_top == $request->rank_top_nodeck)) // and rank, top_rank match
        {
            Entry::destroy($import_entry->id);    // delete import entry
            $merge_name = $import_entry->import_username;
        } else {
            $merge_name = null;
        }

        $entry = [
            'rank' => $request->rank_nodeck,
            'rank_top' => $request->rank_top_nodeck,
            'corp_deck_id' => null,
            'corp_deck_title' => $request['corp_deck_title'],
            'corp_deck_identity' => $request['corp_deck_identity'],
            'corp_deck_type' => null,
            'runner_deck_id' => null,
            'runner_deck_title' => $request['runner_deck_title'],
            'runner_deck_identity' => $request['runner_deck_identity'],
            'runner_deck_type' => null,
            'import_username' => $merge_name,
            'broken_runner' => 0,
            'broken_corp' => 0,
            'netrunnerdb_claim_runner' => 0,
            'netrunnerdb_claim_corp' => 0,
            'type' => 4
        ];

        if (is_null($reg_entry)) {   // new claim
            Entry::create([         // additional fields
                    'user' => $request->user()->id,
                    'approved' => 1,
                    'tournament_id' => $id
                ] + $entry);
        } else {    // merging with registration
            $reg_entry->update($entry);
        }

        // add conflict if needed
        $tournament->updateConflict();

        // badge for TO
        App('App\Http\Controllers\BadgeController')->addCommunityBuilder($tournament->creator);

        return redirect()->back()->with('message', 'You have claimed a spot on the tournament.');
    }

    /**
     * Gets deck info. Uses other player's decklist if needed.
     * Publishes deck if needed. Adds claim to NetrunnerDB if needed.
     * @param $request_deck Object deck field value from claim form
     * @param $other_deck int claim with other field value from claim form
     * @param $publish boolean auto-publishing
     * @param $side string corp/runner
     * @throws \Exception error message
     * @return mixed deck object
     */
    private function getDeckInfo($request_deck, $other_deck, $publish, $claimNRDB, $rank, $tournament, $side, $userID) {
        $result = null;

        if ($other_deck) {     // claiming with someone else's deck
            $result = app('App\Http\Controllers\NetrunnerDBController')->getDeckInfo($other_deck);
            if ($result['side'] !== $side) {
                throw new \Exception($side.' deck ID must point to a corp deck');
            }
        } else {    // claiming with own deck
            $result = json_decode(stripslashes($request_deck), true);

            // do publishing if needed
            if (intval($result['type']) == 2 && $publish) {
                try {
                    $response = app('App\Http\Controllers\NetrunnerDBController')->publishDeck($result['id']);
                } catch (\Exception $e) {
                    throw new \Exception('NetrunnerDB publishing error: "'.$result['title'].'" - '.$e->getMessage());
                }

                // store ID of newly published deck
                $result['type'] = 1;
                $result['id'] = intval($response);
            }
        }

        // add claim to NetrunnerDB
        if ($claimNRDB && $result['type'] == 1) {
            try {
                $response = app('App\Http\Controllers\NetrunnerDBController')->addClaimToNRDB(
                    $result['id'], $tournament->title, 'https://alwaysberunning.net'.$tournament->seoUrl(), $rank,
                    $tournament->players_number);
            } catch (\Exception $e) {
                throw new \Exception('NetrunnerDB claim error: "'.$result['title'].'" - '.$e->getMessage());
            }

            // store NetrunnerDB claim ID
            $result['netrunnerdb_claim'] = $response;
        } else {
            $result['netrunnerdb_claim'] = 0; // record that claim should not be added
        }

        return $result;
    }

    public function unclaim(Request $request, $id)
    {
        $entry = Entry::where('id', $id)->first();
        $this->authorize('unclaim', $entry, $request->user());
        if (!is_null($entry)) {     // claim is removed, registration for the tournament stays

            // removing claims from NetrunnerDB
            if ($entry->netrunnerdb_claim_runner) {
                app('App\Http\Controllers\NetrunnerDBController')->deleteClaimFromNRDB(
                    $entry->netrunnerdb_claim_runner, $entry->runner_deck_id);
            }
            if ($entry->netrunnerdb_claim_corp) {
                app('App\Http\Controllers\NetrunnerDBController')->deleteClaimFromNRDB(
                    $entry->netrunnerdb_claim_corp, $entry->corp_deck_id);
            }

            $entry->rank = null;
            $entry->rank_top = null;
            $entry->corp_deck_id = null;
            $entry->runner_deck_id = null;
            $entry->netrunnerdb_claim_runner = null;
            $entry->netrunnerdb_claim_corp = null;
            $entry->corp_deck_title = '';
            $entry->runner_deck_title = '';
            $entry->runner_deck_identity = '';
            $entry->corp_deck_identity = '';
            $entry->runner_deck_type = null;
            $entry->corp_deck_type = null;
            $entry->type = 0;
            $entry->save();
        }

        // remove conflict if needed
        $tournament = Tournament::withTrashed()->findOrFail($entry->tournament_id);
        $tournament->updateConflict();

        // remove badges if needed
        App('App\Http\Controllers\BadgeController')->addClaimBadges($entry->player->id);
        App('App\Http\Controllers\BadgeController')->addCommunityBuilder($tournament->creator);

        return redirect()->back()->with('message', 'You removed your claim from the tournament.');
    }

    /**
     * API endpont for tournament results
     * @param $request Request
     * @return mixed
     */
    public function entriesJSON(Request $request) {
        $id = $request->input('id');
        $tournament = Tournament::where('id', $id)->first();

        // not found
        if (is_null($tournament)) {
            return response()->json(['error' => 'Tournament not found.']);
        }
        // not concluded
        if ($tournament->concluded == 0) {
            return response()->json(['warn' => 'Tournament is not concluded.']);
        }

        $result = [];
        $identities = CardIdentity::get();
        $identities_titles=$identities->pluck('title', 'id')->all();
        $identities_factions=$identities->pluck('faction_code', 'id')->all();
        $entries = Entry::where('tournament_id', $id)->where(function($q) {
            $q->where('runner_deck_identity', '!=', '')->orWhere('corp_deck_identity', '!=', '');
        })->orderBy('rank')->get()->all();

        foreach($entries as $entry) {

            if ($entry['user']) {
                $user_name = User::where('id', $entry['user'])->first()['name'];
            } else {
                $user_name = null;
            }

            $entry_array = [
                'user_id' => $entry['user'],
                'user_name' => $user_name,
                'user_import_name' => $entry['import_username'],
                'rank_swiss' => $entry['rank'],
                'rank_top' => $entry['rank_top'] ? $entry['rank_top'] : null,
                'runner_deck_title' => $entry['runner_deck_title'],
                'runner_deck_identity_id' => $entry['runner_deck_identity'],
                'runner_deck_url' => $this->deckUrl($entry['runner_deck_id'], $entry['runner_deck_type']),
                'corp_deck_title' => $entry['corp_deck_title'],
                'corp_deck_identity_id' => $entry['corp_deck_identity'],
                'corp_deck_url' => $this->deckUrl($entry['corp_deck_id'], $entry['corp_deck_type']),
            ];

            if ($entry['runner_deck_identity']) {
                $entry_array['runner_deck_identity_title'] = $identities_titles[$entry['runner_deck_identity']];
                $entry_array['runner_deck_identity_faction'] = $identities_factions[$entry['runner_deck_identity']];
            }

            if ($entry['corp_deck_identity']) {
                $entry_array['corp_deck_identity_title'] = $identities_titles[$entry['corp_deck_identity']];
                $entry_array['corp_deck_identity_faction'] = $identities_factions[$entry['corp_deck_identity']];
            }

            array_push($result, $entry_array);


        }

        return response()->json($result);
    }

    /**
     * Generates deck URL.
     * @param $deckid int deckID
     * @param $type int 1 = public, 2 = private
     * @return string URL
     */
    public function deckUrl($deckid, $type) {
        switch ($type) {
            case 1: return "https://netrunnerdb.com/en/decklist/".$deckid;
            case 2: return "https://netrunnerdb.com/en/deck/view/".$deckid;
            default: return "";
        }
    }

    /**
     * Deletes anonym entry.
     * @param Request $request
     * @param $id int Entry ID
     * @return redirect
     */
    public function deleteAnonym(Request $request, $id) {
        $entry = Entry::findOrFail($id);
        $tournament = Tournament::withTrashed()->findOrFail($entry->tournament_id);

        // auth check
        $this->authorize('conclude', $tournament, $request->user());

        // delete
        Entry::destroy($id);

        // add conflict if needed
        $tournament->updateConflict();

        // delete imported flag if needed
        if (!Entry::where('tournament_id', $entry->tournament_id)->where('user', 0)->first()) {
            $tournament->update(['import' => 0]);
        }

        return back()->with('message', 'Entry deleted.');
    }

    /**
     * Adds anonym entry
     * @param Request $request
     * @param $id int Tournament ID
     */
    public function addAnonym(Request $request) {
        $tournament = Tournament::withTrashed()->findOrFail($request->tournament_id);

        // auth check
        $this->authorize('conclude', $tournament, $request->user());

        // add anonym entry
        Entry::create($request->all() + ['type' => 13]);

        if (!$tournament->import) {
            $tournament->update(['import' => 3]);
        }
        // add conflict if needed
        $tournament->updateConflict();

        return back()->with('message', 'Entry added.')->with('editmode', 1);
    }
}
