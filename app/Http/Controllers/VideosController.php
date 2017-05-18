<?php

namespace App\Http\Controllers;

use App\Policies\VideoTagPolicy;
use App\Tournament;
use App\Video;
use App\Http\Requests;
use App\VideoTag;
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

        // add badges
        App('App\Http\Controllers\BadgeController')->addVideoBadge($request->user()->id);

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

        // remove badges
        App('App\Http\Controllers\BadgeController')->addVideoBadge($request->user()->id);

        // redirecting to tournament
        return redirect()->route('tournaments.show.slug', [$tournament->id, $tournament->seoTitle()])
            ->with('message', 'Video deleted.');
    }

    /**
     * Tags user in video.
     * @param Request $request
     * @param $id int Video ID
     * @return \Illuminate\Http\RedirectResponse
     */
    public function storeTag(Request $request, $id) {
        $this->authorize('logged_in', Tournament::class, $request->user());
        $video = Video::findOrFail($id);

        // if already created
        if (VideoTag::where('video_id', $id)->where('user_id', $request->user_id)->first()) {
            return redirect()->route('tournaments.show.slug', [$video->tournament_id, $video->tournament->seoTitle()])
                ->with('message', 'User was already tagged in video.');
        }

        VideoTag::create([
            'video_id' => $id,
            'user_id' => $request->user_id,
            'tagged_by_user_id' => $request->user()->id
        ]);

        // redirecting to tournament
        return redirect()->back()->with('message', 'User tagged in video.');
    }

    /**
     * Untags users from video
     * @param Request $request
     * @param $id int VideoTag ID
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroyTag(Request $request, $id) {
        $videotag = VideoTag::findOrFail($id);
        $this->authorize('delete', $videotag, $request->user());

        VideoTag::destroy($videotag->id);

        // redirecting to tournament
        return redirect()->back()->with('message', 'User tag deleted.');
    }
}
