<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Artist;
use App\Tournament;
use App\Http\Requests;

class ArtistController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function getArtists()
    {
        $artists = Artist::get();
        return response()->json($artists);
    }

    public function getArtistDetails($id) {
        $artist = Artist::where('id', $id)->with(['items', 'user', 'items.photos'])->first();
        return response()->json($artist);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function createArtist(Request $request)
    {
        $this->authorize('admin', Tournament::class, $request->user());
        $newArtist = Artist::create([
            'name' => $request->input('name'),
            'user_id' => $request->input('user_id'),
            'description' => $request->input('description'),
            'url' => $request->input('url'),
            'creator_id' => $request->user()->id
        ]);

        return response()->json($newArtist);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function editArtist(Request $request, $id)
    {
        $artist = Artist::findOrFail($id);
        $this->authorize('admin', Tournament::class, $request->user());

        $artist->update($request->all());

        return response()->json($artist);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function deleteArtist(Request $request, $id)
    {
        // auth, error checking
        $this->authorize('admin', Tournament::class, $request->user());
        // TODO
        // if (Artist::where('id', $id)->count() > 0) {
        //     abort(403, 'Artist is in use.');
        // }

        $artist = Artist::findOrFail($id);
        $artist->delete();

        return response()->json('Artist removed.');
    }
}
