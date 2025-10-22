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

            $filename = $created->id.'.'.$request->photo->extension();
            $fullPath = public_path('photo/') . $filename;
            $thumbPath = public_path('photo/thumb_') . $filename;

            try {
                // Process image directly from upload before moving to final location
                $img = Image::make($request->file('photo')->getRealPath());
                
                // Resize main image
                $img->resize(1920, 1920, function ($constraint) {
                    $constraint->aspectRatio();
                    $constraint->upsize();
                });
                $img->save($fullPath, 90); // Explicitly set quality to 90%
                
                // Create and process thumbnail
                $thumb = Image::make($request->file('photo')->getRealPath());
                $dim = min($thumb->height(), $thumb->width());
                $thumb->resizeCanvas($dim, $dim, 'center');
                $thumb->resize(400, 400, function ($constraint) {
                    $constraint->aspectRatio();
                    $constraint->upsize();
                });
                $thumb->save($thumbPath, 85); // Slightly lower quality for thumbnails
                
                // Verify files were created successfully
                if (!file_exists($fullPath) || !file_exists($thumbPath)) {
                    throw new \Exception('Failed to save processed images to disk');
                }
                
            } catch (NotReadableException $e) {
                \Log::error('Photo upload failed - not readable: ' . $e->getMessage(), [
                    'photo_id' => $created->id,
                    'tournament_id' => $tournament->id,
                    'user_id' => $request->user()->id
                ]);
                $created->delete();
                return redirect()->route('tournaments.show.slug', [$tournament->id, $tournament->seoTitle()])
                    ->withErrors(['The uploaded file could not be read as an image. Please ensure it is a valid image file.']);
            } catch (\Exception $e) {
                \Log::error('Photo upload failed: ' . $e->getMessage(), [
                    'photo_id' => $created->id,
                    'tournament_id' => $tournament->id,
                    'user_id' => $request->user()->id
                ]);
                // Clean up any partially created files
                if (file_exists($fullPath)) {
                    @unlink($fullPath);
                }
                if (file_exists($thumbPath)) {
                    @unlink($thumbPath);
                }
                $created->delete();
                return redirect()->route('tournaments.show.slug', [$tournament->id, $tournament->seoTitle()])
                    ->withErrors(['There was a problem processing your photo. Please try again or contact support if the issue persists.']);
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

            $filename = $created->id.'.'.$request->photo->extension();
            $fullPath = public_path('photo/') . $filename;
            $thumbPath = public_path('photo/thumb_') . $filename;

            try {
                // Process image directly from upload before moving to final location
                $img = Image::make($request->file('photo')->getRealPath());
                
                // Resize main image
                $img->resize(2560, 2560, function ($constraint) {
                    $constraint->aspectRatio();
                    $constraint->upsize();
                });
                $img->save($fullPath, 90); // Explicitly set quality to 90%
                
                // Create and process thumbnail
                $thumb = Image::make($request->file('photo')->getRealPath());
                $dim = min($thumb->height(), $thumb->width());
                $thumb->resizeCanvas($dim, $dim, 'center');
                $thumb->resize(200, 200, function ($constraint) {
                    $constraint->aspectRatio();
                    $constraint->upsize();
                });
                $thumb->save($thumbPath, 85); // Slightly lower quality for thumbnails
                
                // Verify files were created successfully
                if (!file_exists($fullPath) || !file_exists($thumbPath)) {
                    throw new \Exception('Failed to save processed images to disk');
                }
                
            } catch (NotReadableException $e) {
                \Log::error('Photo upload API failed - not readable: ' . $e->getMessage(), [
                    'photo_id' => $created->id,
                    'user_id' => $request->user()->id
                ]);
                $created->delete();
                return response()->json('The uploaded file could not be read as an image. Please ensure it is a valid image file.', 500);
            } catch (\Exception $e) {
                \Log::error('Photo upload API failed: ' . $e->getMessage(), [
                    'photo_id' => $created->id,
                    'user_id' => $request->user()->id
                ]);
                // Clean up any partially created files
                if (file_exists($fullPath)) {
                    @unlink($fullPath);
                }
                if (file_exists($thumbPath)) {
                    @unlink($thumbPath);
                }
                $created->delete();
                return response()->json('There was a problem processing your photo. Please try again or contact support if the issue persists.', 500);
            }

            // saving filename in DB
            Photo::findOrFail($created->id)->update(['filename' => $filename, 'user_id' => $request->user()->id]);
            $created = Photo::findOrFail($created->id);

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

    public function update($id, Request $request) {
        $photo = Photo::findOrFail($id);
        $this->authorize('delete', $photo, $request->user());

        $photo->update($request->all());

        return response()->json($photo);
    }

}
