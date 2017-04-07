<?php

namespace App\Http\Controllers;

use App\Photo;
use App\Tournament;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use App\Http\Requests;
use Intervention\Image\Facades\Image;

class PhotosController extends Controller
{
    /**
     * Saves photo, resizes, creates thumbnail image.
     * @param Request $request
     * @return $this|\Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        $tournament = Tournament::withTrashed()->findOrFail($request->get('tournament_id'));
        $this->authorize('logged_in', Tournament::class, $request->user());

        // validating successful upload
        if ($request->hasFile('photo') && $request->file('photo')->isValid()) {

            // adding to the DB
            $created = Photo::create($request->all());

            // saving photo and thumbnail
            $filename = $created->id.'.'.$request->photo->extension();
            $request->file('photo')->move('photo', $filename);
            File::copy('photo/' . $filename, 'photo/thumb_' . $filename);

            // resizing image
            $img = Image::make(public_path('photo/') . $filename);
            $img->resize(1280, 1280, function ($constraint) {
                $constraint->aspectRatio();
                $constraint->upsize();
            });
            $img->save();

            // trimming to square
            $thumb = Image::make(public_path('photo/thumb_') . $filename);
            $dim = min($thumb->height(), $thumb->width());
            $thumb->resizeCanvas($dim, $dim, 'center');
            // resize
            $thumb->resize(200, 200, function ($constraint) {
                $constraint->aspectRatio();
                $constraint->upsize();
            });
            $thumb->save();

            // saving filename in DB
            Photo::findOrFail($created->id)->update(['filename' => $filename, 'user_id' => $request->user()->id]);

            // redirecting to tournament
            return redirect()->route('tournaments.show.slug', [$tournament->id, $tournament->seoTitle()])
                ->with('message', 'Photo uploaded');
        } else {
            return redirect()->route('tournaments.show.slug', [$tournament->id, $tournament->seoTitle()])
                ->withErrors(['There was a problem uploading your video.']);
        }
    }

    /**
     * Approve photo
     * @param Request $request
     * @param $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function approve (Request $request, $id) {
        $photo = Photo::findOrFail($id);
        $this->authorize('admin', Tournament::class, $request->user());

        $photo->update(['approved' => true]);

        // TODO: add badge

        // redirecting to tournament
        return redirect()->back()->with('message', 'Photo approved');
    }

    public function destroy(Request $request, $id) {
        $photo = Photo::findOrFail($id);
        $this->authorize('delete', $photo, $request->user());

        File::delete('photo/'.$photo->filename);
        File::delete('photo/thumb_'.$photo->filename);
        $photo->delete();

        // TODO: remove badge

        // redirecting to tournament
        return redirect()->back()->with('message', 'Photo deleted');
    }

}
