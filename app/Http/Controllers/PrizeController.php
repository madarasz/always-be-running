<?php

namespace App\Http\Controllers;

use App\Prize;
use App\PrizeElement;
use App\Tournament;
use App\Photo;
use Illuminate\Http\Request;

class PrizeController extends Controller
{

    /**
     * List all prize kits inluding their items and linked photos.
     * Use verbose=1 GET parameter for additional details.
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getPrizeKits(Request $request) {

        $prizes = Prize::with(['tournament_type', 'user', 'elements', 'photos', 'elements.photos', 'elements.user'])
            ->orderBy('year', 'desc')->orderBy('tournament_type_id', 'desc')->orderBy('title', 'desc')->get();

        // hiding unimportant fields
        $prizes = $prizes->makeHidden(['creator', 'order', 'deleted_at']);
        if ($request->input('verbose') != 1) {
            $prizes = $prizes->makeHidden(['created_at', 'updated_at', 'tournamentCount',
                'tournament_type_id', 'user']);
        }

        foreach ($prizes as $prize) {
            if ($prize->user) {
                $prize->user->makeHidden(['name', 'username_preferred', 'supporter']);
            }
            if ($prize->elements) {
                $prize->elements->makeHidden(['prize_id', 'sort_order', 'creator']);
                if ($request->input('verbose') != 1) {
                    $prize->elements->makeHidden(['created_at', 'updated_at', 'user', 'quantity']);
                }
                foreach ($prize->elements as $element) {
                    if ($element->photos) {
                        $element->photos->makeHidden(['tournament_id', 'user_id', 'title', 'approved', 'created_at',
                            'updated_at', 'prize_id', 'prize_element_id', 'filename']);
                    }
                    if ($element->user) {
                        $element->user->makeHidden(['name', 'username_preferred', 'supporter']);
                    }
                }
            }
            if ($prize->photos) {
                $prize->photos
                    ->makeHidden(['tournament_id', 'user_id', 'title', 'approved', 'created_at', 'updated_at',
                        'prize_id', 'prize_element_id', 'filename']);
            }
        }


        return response()->json($prizes);
    }

    /**
     * Creates prize kit.
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function createPrizeKit(Request $request) {
        $this->authorize('admin', Tournament::class, $request->user());

        $newPrize = Prize::create([
            'year' => $request->input('year'),
            'title' => $request->input('title'),
            'tournament_type_id' => $request->input('tournament_type_id'),
            'description' => $request->input('description'),
            'ffg_url' => $request->input('ffg_url'),
            'creator' => $request->user()->id
        ]);

        return response()->json($newPrize);
    }

    /**
     * Update prize kit.
     * @param $id prize kit ID
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function editPrizeKit($id, Request $request) {
        $prize = Prize::findOrFail($id);
        $this->authorize('admin', Tournament::class, $request->user());

        $prize->update([
            'year' => $request->input('year'),
            'title' => $request->input('title'),
            'tournament_type_id' => $request->input('tournament_type_id'),
            'description' => $request->input('description'),
            'ffg_url' => $request->input('ffg_url'),
        ]);

        return response()->json($prize);
    }

    /**
     * Delete prize kit.
     * @param $id prize kit ID
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function deletePrizeKit($id, Request $request) {
        // auth, error checking
        $this->authorize('admin', Tournament::class, $request->user());
        if (Tournament::where('prize_id', $id)->count() > 0) {
            abort(403, 'Prize kit is in use.');
        }

        $prize = Prize::findOrFail($id);
        $prize->delete();

        return response()->json('Prize kit deleted.');
    }

    /**
     * Creates prize item.
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function createPrizeItem(Request $request) {
        $this->authPrizeItem($request);

        $newItem = PrizeElement::create(array_merge($request->all(), [
            'creator' => $request->user()->id
        ]));

        return response()->json($newItem);
    }

    public function editPrizeItem($id, Request $request) {
        $item = PrizeElement::findOrFail($id);
        $this->authPrizeItem($request);

        $item->update($request->all());

        return response()->json($item);
    }

    public function deletePrizeItem($id, Request $request) {
        // auth, error checking
        $item = PrizeElement::findOrFail($id);
        $request->request->add(['artist_id' => $item->artist_id]); // for auth
        $this->authPrizeItem($request);
        
        // delete related photos
        $photos = Photo::where('prize_element_id', $id)->get();
        foreach($photos as $photo) {
            app('App\Http\Controllers\PhotosController')->destroyApi($request, $photo->id);
        }

        // delete item
        $item->delete();

        return response()->json('Prize item deleted.');
    }

    private function authPrizeItem(Request $request) {
        if (!$request->user()->admin && 
            !($request->has('artist_id') && $request->input('artist_id') == $request->user()->artist_id)) {
            abort(403);
        }
    }

}
