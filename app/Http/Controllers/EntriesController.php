<?php

namespace App\Http\Controllers;

use App\Http\Requests\EntryRequest;
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
                'tournament_id' => $id
            ]);
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
        return redirect()->back()->with('message', 'You have unregistered from the tournament.');
    }

    public function claim(EntryRequest $request, $id)
    {
        $this->authorize('logged_in', Tournament::class, $request->user());
        $user_id = $request->user()->id;
        $corp_deck = json_decode(stripslashes($request->corp_deck), true);
        $runner_deck = json_decode(stripslashes($request->runner_deck), true);

        // getting registration for tournament or imported entry
        $reg_entry = Entry::where('user', $user_id)->where('tournament_id', $id)->first();
        $import_entry = Entry::where('tournament_id', $id)->whereNull('user')->where(function($q) use ($request) {
                $q->where('rank', $request->rank)->orWhere('rank_top', $request->rank_top);
            })->first();

	// merging with import entry
        if (!is_null($import_entry) &&     // if there is an import entry
            $import_entry->runner_deck_identity == $runner_deck['identity'] &&   // and IDs match
            $import_entry->corp_deck_identity == $corp_deck['identity'] &&
            $import_entry->rank == $request->rank && $import_entry->rank_top == $request->rank_top) // and rank, top_rank match
        {
                Entry::destroy($import_entry->id);    // delete import entry
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
            'runner_deck_type' => $runner_deck['type']
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
        $tournament = Tournament::where('id', $id)->first();
        $tournament->updateConflict();

        return redirect()->back()->with('message', 'You have claimed a spot on the tournament.');
    }

    public function unclaim(Request $request, $id)
    {
        $entry = Entry::where('id', $id)->first();
        $this->authorize('unclaim', $entry, $request->user());
        if (!is_null($entry)) {     // claim is removed, registration for the tournament stays
            $entry->rank = null;
            $entry->rank_top = null;
            $entry->corp_deck_id = null;
            $entry->runner_deck_id = null;
            $entry->corp_deck_title = '';
            $entry->runner_deck_title = '';
            $entry->runner_deck_identity = '';
            $entry->corp_deck_identity = '';
            $entry->save();
        }

        // remove conflict if needed
        $tournament = Tournament::where('id', $entry->tournament_id)->first();
        $tournament->updateConflict();

        return redirect()->back()->with('message', 'You removed your claim from the tournament.');
    }

    /**
     * API endpont for tournament results
     * @param $id Tournament ID
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
        $entries = Entry::where('tournament_id', $id)->get()->all();

        foreach($entries as $entry) {

            if ($entry['user']) {
                $user_name = User::where('id', $entry['user'])->first()['name'];
            } else {
                $user_name = null;
            }

            array_push($result, [
                'user_id' => $entry['user'],
                'user_name' => $user_name,
                'user_import_name' => $entry['import_username'],
                'rank_swiss' => $entry['rank'],
                'rank_top' => $entry['rank_top'] ? $entry['rank_top'] : null,
                'runner_deck_title' => $entry['runner_deck_title'],
                'runner_deck_identity_id' => $entry['runner_deck_identity'],
                'runner_deck_identity_title' => $identities_titles[$entry['runner_deck_identity']],
                'runner_deck_identity_faction' => $identities_factions[$entry['runner_deck_identity']],
                'runner_deck_url' => $this->deckUrl($entry['runner_deck_id'], $entry['runner_deck_type']),
                'corp_deck_title' => $entry['corp_deck_title'],
                'corp_deck_identity_id' => $entry['corp_deck_identity'],
                'corp_deck_identity_title' => $identities_titles[$entry['corp_deck_identity']],
                'corp_deck_identity_faction' => $identities_factions[$entry['corp_deck_identity']],
                'corp_deck_url' => $this->deckUrl($entry['corp_deck_id'], $entry['corp_deck_type'])
            ]);
        }

        return response()->json($result);
    }

    public function deckUrl($deckid, $type) {
        switch ($type) {
            case 1: return "https://netrunnerdb.com/en/decklist/".$deckid;
            case 2: return "https://netrunnerdb.com/en/deck/view/".$deckid;
            default: return "";
        }
    }
}
