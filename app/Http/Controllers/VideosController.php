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

        $data = Video::youtubeLookup($request->get('video_id'));

        $message = ''; $errors = [];

        if($data) {
            $exists = Video::where(['video_id' => $data['video_id'], 'tournament_id' => $tournament->id])->count();

            if($exists < 1) {
                $data['tournament_id'] = $tournament->id;
                $data['user_id'] = $request->user()->id;
                $video = Video::create($data);
                $message = 'Video added.';
            } else {
                $errors = ['This video has already been added to this tournament!'];
            }
        } else {
            $errors = ['Error loading video data from Youtube. Probably invalid ID or URL.'];
        }

        // redirecting to tournament
        return redirect()->route('tournaments.show.slug', [$tournament->id, $tournament->seoTitle()])
            ->with('message', $message)->withErrors($errors);
    }

    public function destroy(Request $request, $id)
    {
        $video = Video::findOrFail($id);
        $this->authorize('delete', $video, $request->user());

        $tournament = $video->tournament;
        Video::destroy($id);

        // redirecting to tournament
        return redirect()->route('tournaments.show.slug', [$tournament->id, $tournament->seoTitle()])
            ->with('message', 'Video deleted.');
    }
}
