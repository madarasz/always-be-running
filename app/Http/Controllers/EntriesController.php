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
        $entry = Entry::where('user', $user_id)->where('tournament_id', $id)->first();
        $conflict = Entry::where('tournament_id', $id)->where('user', '!=', $user_id)->where(function($query) use($request) {
            $query->where('rank', $request->rank)->orWhere('rank_top', $request->rank_top);
        })->first();
        $corp_deck = json_decode(stripslashes($request->corp_deck), true);
        $runner_deck = json_decode(stripslashes($request->runner_deck), true);
        if (is_null($entry)) {
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
        } else {
            $entry->update([
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
        // create conflict
        if (!is_null($conflict)) {
            $tournament = Tournament::where('id', $id)->first();
            $tournament->update(['conflict' => 1]);
        }
        return redirect()->back()->with('message', 'You have claimed a spot on the tournament.');
    }

    public function unclaim(Request $request, $id)
    {
        $entry = Entry::where('id', $id)->first();
        $this->authorize('unclaim', $entry, $request->user());
        if (!is_null($entry)) {
            $entry->rank = null;
            $entry->rank_top = null;
            $entry->corp_deck_id = 0;
            $entry->runner_deck_id = 0;
            $entry->corp_deck_title = '';
            $entry->runner_deck_title = '';
            $entry->save();
        }
        // remove conflict
        $tournament = Tournament::where('id', $entry->tournament_id)->first();
        if ($tournament->conflict)
        {
            $conflict_rank = Entry::where('tournament_id', $entry->tournament_id)->groupBy('rank')->havingRaw("COUNT(id) > 1")->get();
            $conflict_rank_top = Entry::where('tournament_id', $entry->tournament_id)->groupBy('rank_top')->havingRaw("COUNT(id) > 1")->get();
            if (count($conflict_rank) + count($conflict_rank_top) == 0) {
                $tournament->update(['conflict' => 0]);
            }
        }
        return redirect()->back()->with('message', 'You removed your claim from the tournament.');
    }
}
