<?php

namespace App\Http\Controllers;

use App\Photo;
use App\Tournament;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use App\Http\Requests;
use Intervention\Image\Facades\Image;
use Intervention\Image\Exception\NotReadableException;
use Mockery\CountValidator\Exception;

class PhotosController extends Controller
{
    /**
     * Saves photo, resizes, creates thumbnail image.
     * @param Request $request
     * @return $this|\Illuminate\Http\RedirectResponse
     */
    // to be deprecated
    public function store(Request $request)
    {
        $tournament = Tournament::withTrashed()->findOrFail($request->get('tournament_id'));
        $this->authorize('logged_in', Tournament::class, $request->user());

        // validating successful upload
        if ($request->hasFile('photo') && $request->file('photo')->isValid()) {

            // adding to the DB
            $created = Photo::create($request->all());

            // if admin uploads it gets approved by default
            if ($request->user()->admin) {
                $created->update(['approved' => 1]);
            }

            // saving photo and thumbnail
            $filename = $created->id.'.'.$request->photo->extension();
            $request->file('photo')->move('photo', $filename);
            File::copy('photo/' . $filename, 'photo/thumb_' . $filename);

            try {
                // resizing image
                $img = Image::make(public_path('photo/') . $filename);
                $img->resize(2560, 2560, function ($constraint) {
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
            } catch (NotReadableException $e) {
                $created->delete();
                return redirect()->route('tournaments.show.slug', [$tournament->id, $tournament->seoTitle()])
                    ->withErrors(['There was a problem uploading your photo.']);
            }

            // saving filename in DB
            Photo::findOrFail($created->id)->update(['filename' => $filename, 'user_id' => $request->user()->id]);

            // redirecting to tournament
            return redirect()->route('tournaments.show.slug', [$tournament->id, $tournament->seoTitle()])
                ->with('message', 'Photo uploaded');
        } else {
            return redirect()->route('tournaments.show.slug', [$tournament->id, $tournament->seoTitle()])
                ->withErrors(['There was a problem uploading your photo.']);
        }
    }

    /**
     * API endpoint for storing photos, returns JSON response.
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * @throws \Exception
     */
    public function storeApi(Request $request)
    {
        $this->authorize('logged_in', Tournament::class, $request->user());

        // validating successful upload
        if ($request->hasFile('photo') && $request->file('photo')->isValid()) {

            // adding to the DB
            $created = Photo::create($request->all());

            // if admin uploads it gets approved by default
            if ($request->user()->admin) {
                $created->update(['approved' => 1]);
            }

            // saving photo and thumbnail
            $filename = $created->id.'.'.$request->photo->extension();
            $request->file('photo')->move('photo', $filename);
            File::copy('photo/' . $filename, 'photo/thumb_' . $filename);

            try {
                // resizing image
                $img = Image::make(public_path('photo/') . $filename);
                $img->resize(2560, 2560, function ($constraint) {
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
            } catch (NotReadableException $e) {
                $created->delete();
                return response()->json('There was a problem uploading your photo, was not readable.', 500);
            }

            // saving filename in DB
            Photo::findOrFail($created->id)->update(['filename' => $filename, 'user_id' => $request->user()->id]);

            // redirecting to tournament
            return response()->json($created);
        } else {
            return response()->json('There was a problem uploading your photo, file missing or not valid.', 500);
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

    // to be deprecated
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

    public function destroyApi(Request $request, $id) {
        $photo = Photo::findOrFail($id);
        $this->authorize('delete', $photo, $request->user());

        File::delete('photo/'.$photo->filename);
        File::delete('photo/thumb_'.$photo->filename);
        $photo->delete();

        // TODO: remove badge

        // redirecting to tournament
        return response()->json('Photo deleted.');
    }

    /**
     * Rotates image 90 degrees clockwise or counter-clockwise.
     * @param Request $request
     * @param $id
     * @param $dir 'cw' or 'ccw' for clockwise or counter-clockwise
     * @return \Illuminate\Http\RedirectResponse
     */
    public function rotate(Request $request, $id, $dir) {
        $photo = Photo::findOrFail($id);
        $this->authorize('delete', $photo, $request->user());

        // new file name: image.jpg > rot1_image.jpg > rot2_image.jpg > rot3_image.jpg > image.jpg
        if (preg_match('/rot(\d)/', $photo->filename, $matches)) {
            $rotnum = intval($matches[1]);
            $filename = substr($photo->filename, 5);
        } else {
            $rotnum = 0;
            $filename = $photo->filename;
        }
        // calculate rotation, new rotation number
        $degrees = 0;
        if ($dir == 'cw') {
            $rotnum++;
            $degrees = -90;
        } elseif ($dir === 'ccw') {
            $rotnum--;
            if ($rotnum < 0) {
                $rotnum = $rotnum + 4;
            }
            $degrees = 90;
        }
        $rotnum = $rotnum % 4;
        // calculate new file name
        if ($rotnum) {
            $newFilename = 'rot'.$rotnum.'_'.$filename;
        } else {
            $newFilename = $filename;
        }

        // rotate image
        $img = Image::make(public_path('photo/') . $photo->filename);
        $img->rotate($degrees);
        $img->save();
        // rotate thumbnail
        $thumb = Image::make(public_path('photo/thumb_') . $photo->filename);
        $thumb->rotate($degrees);
        $thumb->save();

        // rename image
        rename(public_path('photo/').$photo->filename, public_path('photo/').$newFilename);
        // rename thumbnail
        rename(public_path('photo/thumb_').$photo->filename, public_path('photo/thumb_').$newFilename);
        // update DB
        $photo->update(['filename' => $newFilename]);

        // redirecting to tournament
        return redirect()->back()->with('message', 'Photo rotated');
    }

    /**
     * Approves all photos on a tournament.
     * @param $id tournament ID (0 if you want all pending photos to approved for all tournament)
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function approveAll($id, Request $request) {
        $this->authorize('admin', Tournament::class, $request->user());

        if ($id > 0) {
            // approve photos for a tournament
            $tournament = Tournament::findOrFail($id);
            $photos = $tournament->photos();
        } else {
            // approve all pending photos
            $photos = Photo::whereNull('approved');
        }

        $photos->update(['approved' => true]);

        // TODO: add badge

        // redirecting to tournament
        return redirect()->back()->with('message', 'All photos are approved.');
    }

}
