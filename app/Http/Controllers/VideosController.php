<?php

namespace App\Http\Controllers;

use App\Tournament;
use App\Video;
use App\Http\Requests;
use App\VideoTag;
use Illuminate\Http\Request;
use Alaouy\Youtube\Facades\Youtube;

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

        // check if not live twitch
        if (preg_match('/twitch.tv\/(?!videos)/', $request->video_id)) {
            return redirect()->back()->withErrors(['Adding LIVE Twitch feed is not possible. URL should be like: https://www.twitch.tv/videos/...']);
        }
        // source autodetect
        if (preg_match('/youtube.com/', $request->video_id)) {
            $request->type = 1;
        }
        if (preg_match('/twitch.tv/', $request->video_id)) {
            $request->type = 2;
        }

        // load metadata
        switch (intval($request->type)) {
            case 1:
                $data = $this->youtubeLookup($request->video_id);
                break;
            case 2:
                $data = $this->twitchLookup($request->video_id);
                break;
        }

        $message = '';
        $errors = [];

        if ($data) {
            $exists = Video::where(['video_id' => $data['video_id'], 'tournament_id' => $tournament->id, 'flag_removed' => false])
                ->count();

            if ($exists < 1) {
                $data['tournament_id'] = $tournament->id;
                $data['user_id'] = $request->user()->id;
                $video = Video::create($data);
                $message = 'Video added.';
            } else {
                $errors = ['This video has already been added to this tournament!'];
            }
        } else {
            $errors = ['Error loading video data. Probably invalid ID or URL.'];
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
        return redirect()->back()->with('message', 'Video deleted.');
    }

    /**
     * Tags user in video.
     * @param Request $request
     * @param $id int Video ID
     * @return \Illuminate\Http\RedirectResponse
     */
    public function storeTag(Request $request, $id) {
        $this->authorize('logged_in', Tournament::class, $request->user());
        Video::findOrFail($id);

        // if already created
        if (strlen($request->import_player_name) == 0 &&
            VideoTag::where('video_id', $id)->where('user_id', $request->user_id)->first()) {
                return redirect()->back()->with('message', 'User was already tagged in video.');
        }

        if ($request->side == "") {
            $side = null;
        } else {
            $side = intval($request->side) == 1;
        }
        if (strlen($request->import_player_name)) {
            $request->user_id = null;
        }

        VideoTag::create([
            'video_id' => $id,
            'user_id' => $request->user_id,
            'tagged_by_user_id' => $request->user()->id,
            'is_runner' => $side,
            'import_player_name' => $request->import_player_name
        ]);

        // add badges
        if ($request->user_id) {
            App('App\Http\Controllers\BadgeController')->addSensieActor($request->user_id);
        }

        // redirecting to tournament
        return redirect()->back()->with('message', 'Video tag added.');
    }

    /**
     * Untags users from video
     * @param Request $request
     * @param $id int VideoTag ID
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroyTag(Request $request, $id) {
        $videotag = VideoTag::findOrFail($id);
        $user_id = $videotag->user_id;
        $this->authorize('delete', $videotag, $request->user());

        VideoTag::destroy($videotag->id);

        // remove badges
        if ($request->user_id) {
            App('App\Http\Controllers\BadgeController')->addSensieActor($user_id);
        }

        // redirecting to tournament
        return redirect()->back()->with('message', 'Video tag deleted.');
    }

    public function lister() {
        $all = Tournament::where('approved', 1)->has('videos')->
            with(['videos', 'videos.videoTags', 'videos.videoTags.user', 'cardpool', 'tournament_type', 'tournament_format'])->
            select(['id', 'title', 'date', 'location_country', 'players_number', 'charity',
                'tournament_type_id', 'tournament_format_id', 'cardpool_id'])->
            orderBy('date', 'desc')->get();

        return response()->json($all);
    }

    public function page() {
        $page_section = 'videos';
        return view('videos', compact('page_section'));
    }

    public function twitchLookup($input)
    {

        if (is_numeric($input)) {
            // input is ID
            $video_id = $input;
        } else {
            // input is URL
            if (preg_match('/(?:twitch.tv\/videos\/)(([0-9])+)/', $input, $matches)) {
                $video_id = $matches[1];
            } else {
                return false;
            }
        }

        // Create a stream
        $opts = array(
            'http' => array(
                'method' => "GET",
                'header' => "Accept: application/vnd.twitchtv.v5+json\r\n" .
                    "Client-ID:" . env('TWITCH_CLIENT_ID') . "\r\n"
            )
        );
        $context = stream_context_create($opts);

        // Open the file using the HTTP headers set above
        try {
            $data = json_decode(file_get_contents('https://api.twitch.tv/kraken/videos/' . $video_id, false, $context), true);
        } catch (\Exception $e) {
            return false;
        }

        if ($data) {
            return [
                'video_id' => $video_id,
                'video_title' => $data['title'],
                'thumbnail_url' => $data['thumbnails']['medium'][0]['url'],
                'channel_name' => $data['channel']['display_name'],
                'length' => $this->secsToLength($data['length']),
                'type' => 2
            ];
        } else {
            return false;
        }
    }

    public function youtubeLookup($input)
    {
        if (strlen($input) == 11) {
            // video ID
            $video_id = $input;
        } else {
            // video URL
            try {
                $video_id = Youtube::parseVidFromURL($input);
            } catch (\Exception $e) {
                return false;
            }
        }
        $data = Youtube::getVideoInfo($video_id);

        if ($data) {
            return [
                'video_id' => $video_id,
                'video_title' => $data->snippet->title,
                'thumbnail_url' => $data->snippet->thumbnails->default->url,
                'channel_name' => $data->snippet->channelTitle,
                'length' => $this->YTDurationToLength($data->contentDetails->duration),
                'type' => 1
            ];
        } else {
            return false;
        }
    }

    /**
     * Goes through all videos. Flags missing videos, updates length if found.
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function scanForRemovedVideos(Request $request) {
        $this->authorize('admin', Tournament::class, $request->user());
        $count = 0;
        $videos = Video::get();
        foreach ($videos as $video) {
            if ($video->type == 1) {
                // youtube
                $details = $this->youtubeLookup($video->video_id);
            } else {
                // twitch
                $details = $this->twitchLookup($video->video_id);
            }

            if ($details == false) {
                // flag as missing/deleted

                $video->update(['flag_removed' => true]);
                $count++;
            } else {
                // mark as not missing, update length

                $video->update([
                    'flag_removed' => false,
                    'length' => array_key_exists('length', $details) ? $details['length'] : null,
                    'video_title' => $details['title'],
                    'thumbnail_url' => $details['thumbnail_url'],
                    'channel_name' => $details['lechannel_namegth']
                ]);
            }
        }
        return back()->with('message', 'Missing videos flagged: '.$count);
    }

    private function secsToLength($seconds)
    {
        $hours = floor($seconds / 3600);
        $mins = floor($seconds / 60 % 60);
        $secs = floor($seconds % 60);

        return $this->formatLength($hours, $mins, $secs);
    }

    private function YTDurationToLength($duration)
    {
        if (preg_match('/PT(\d+H)?(\d+M)?(\d+S)?/', $duration, $matches)) {
            $hours = 0; $mins = 0; $secs = 0;
            // handle zero values, weird youtube stuff
            if (count($matches) > 1) {
                $hours = intval($matches[1]);
                if (count($matches) > 2) {
                    $mins = intval($matches[2]);
                    if (count($matches) > 3) {
                        $secs = intval($matches[3]);
                    }
                }
            }

            return $this->formatLength($hours, $mins, $secs);
        } else {
            return null;
        }
    }

    private function formatLength($hours, $mins, $secs) {
        if ($hours) {
            return sprintf('%02d:%02d:%02d', $hours, $mins, $secs);
        } else {
            return sprintf('%02d:%02d', $mins, $secs);
        }
    }
}
