<?php

namespace App\Http\Controllers;

use App\Http\Requests\EntryRequest;
use App\Tournament;
use App\Entry;
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
                $q->where('rank', $request->rank)->where('rank_top', $request->rank_top);
            })->first();

        // merging with import entry
        if (!is_null($import_entry) &&     // if there is an import entry
            $import_entry->runner_deck_id == $request->runner_deck_id &&   // and IDs match
            $import_entry->corp_deck_id == $request->corp_deck_id &&
            $import_entry->rank == $request->rank && $import_entry->rank_top == $request->rank_top) // and rank, top_rank match
        {
                Entry::destroy($import_entry->id);    // delete import entry
        }

        if (is_null($reg_entry)) {   // new claim
            Entry::create([
                'user' => $request->user()->id,
                'approved' => 1,
                'tournament_id' => $id,
                'rank' => $request->rank,
                'rank_top' => $request->rank_top,
                'corp_deck_id' => $corp_deck['id'],
                'corp_deck_title' => $corp_deck['title'],
                'corp_deck_identity' => $corp_deck['identity'],
                'runner_deck_id' => $runner_deck['id'],
                'runner_deck_title' => $runner_deck['title'],
                'runner_deck_identity' => $runner_deck['identity']
            ]);
        } else {    // merging with registration
            $reg_entry->update([
                'rank' => $request->rank,
                'rank_top' => $request->rank_top,
                'corp_deck_id' => $corp_deck['id'],
                'corp_deck_title' => $corp_deck['title'],
                'corp_deck_identity' => $corp_deck['identity'],
                'runner_deck_id' => $runner_deck['id'],
                'runner_deck_title' => $runner_deck['title'],
                'runner_deck_identity' => $runner_deck['identity']
            ]);
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
            $entry->save();
        }

        // remove conflict if needed
        $tournament = Tournament::where('id', $entry->tournament_id)->first();
        $tournament->updateConflict();

        return redirect()->back()->with('message', 'You removed your claim from the tournament.');
    }
}
