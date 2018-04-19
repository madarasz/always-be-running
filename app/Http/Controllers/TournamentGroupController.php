<?php

namespace App\Http\Controllers;

use App\Tournament;
use App\TournamentGroup;
use Illuminate\Http\Request;

class TournamentGroupController extends Controller
{

    /**
     * Lists all Tournament Groups
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getTournamentGroups(Request $request) {

        if (is_null($request->input('user'))) {
            $groups = TournamentGroup::get()->makeHidden(['tournaments']);
        } else {
            $groups = TournamentGroup::get()->makeHidden(['tournaments']);
        }

        return response()->json($groups);
    }

    /**
     * Get details of a single Tournament Group.
     * @param Request $request
     * @param $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function getTournamentGroupDetails(Request $request, $id) {
        $group = TournamentGroup::where('id', $id)
            ->with(['tournaments' => function($query){
                $query->select(['tournaments.id', 'tournaments.title', 'date', 'tournaments.tournament_type_id',
                    'tournaments.tournament_format_id'])->orderBy('date', 'desc');
            }, 'tournaments.tournament_type', 'tournaments.tournament_format'])->first();

        return response()->json($group);
    }

    /**
     * Create new Tournament Group
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function createTournamentGroup(Request $request) {

        $this->authorize('logged_in', TournamentGroup::class, $request->user());

        $newGrouo = TournamentGroup::create([
            'title' => $request->input('title'),
            'description' => $request->input('description'),
            'location' => $request->input('location'),
            'creator' => $request->user()->id
        ]);

        return response()->json($newGrouo);
    }

    /**
     * Deletes Tournament Group.
     * @param Request $request
     * @param $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function deleteTournamentGroup(Request $request, $id) {

        $group = TournamentGroup::findOrFail($id);

        $this->authorize('own', $group, $request->user());

        $group->delete();

        return response()->json('Group deleted.');
    }

    /**
     * Updates Tournament Group.
     * @param Request $request
     * @param $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function editTournamentGroup(Request $request, $id) {

        $group = TournamentGroup::findOrFail($id);

        $this->authorize('own', $group, $request->user());

        $group->update([
            'title' => $request->input('title'),
            'description' => $request->input('description'),
            'location' => $request->input('location')
        ]);

        return response()->json('Group edited.');

    }

    /**
     * Links tournament to group.
     * @param Request $request
     * @param $groupId
     * @param $tournamentId
     * @return \Illuminate\Http\JsonResponse
     */
    public function linkTournamentToGroup(Request $request, $groupId, $tournamentId) {

        $group = TournamentGroup::findOrFail($groupId);
        $tournament = Tournament::findOrFail($tournamentId);
        $this->authorize('own', $group, $request->user());

        $group->tournaments()->save($tournament);

        return response()->json('Tournament linked.');
    }

    /**
     * Unlinks tournament from group.
     * @param Request $request
     * @param $groupId
     * @param $tournamentId
     * @return \Illuminate\Http\JsonResponse
     */
    public function unlinkTournamentToGroup(Request $request, $groupId, $tournamentId) {

        $group = TournamentGroup::findOrFail($groupId);
        $tournament = Tournament::findOrFail($tournamentId);
        $this->authorize('own', $group, $request->user());

        $group->tournaments()->detach($tournamentId);

        return response()->json('Tournament linked.');
    }
}
