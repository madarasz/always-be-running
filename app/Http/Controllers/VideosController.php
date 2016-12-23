<?php

namespace App\Http\Controllers;

use App\Tournament;
use App\Video;
use App\Http\Requests;
use Illuminate\Http\Request;

class VideosController extends Controller
{
    /**
     * Saves new video.
     * @param Requests\VideoRequest $request
     * @return redirects
     */
    public function store(Requests\VideoRequest $request)
    {
        $tournament = Tournament::withTrashed()->findOrFail($request->get('tournament_id'));
        $this->authorize('logged_in', Tournament::class, $request->user());

        $video = Video::create([
            'tournament_id' => $tournament->id,
            'user_id' => $request->user()->id,
            'video_id' => $request->get('video_id')
        ]);
        $video->populateFromYoutube();

        // redirecting to tournament
        return redirect()->route('tournaments.show.slug', [$tournament->id, $tournament->seoTitle()])
            ->with('message', 'Video added.');
    }

    public function destroy(Request $request, $id)
    {
        $video = Video::where('id', $id)->first();
        $this->authorize('delete', $video, $request->user());

        $tournament = $video->tournament;
        Video::destroy($id);

        // redirecting to tournament
        return redirect()->route('tournaments.show.slug', [$tournament->id, $tournament->seoTitle()])
            ->with('message', 'Video deleted.');
    }
}
