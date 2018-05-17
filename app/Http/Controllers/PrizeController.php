<?php

namespace App\Http\Controllers;

use App\Prize;
use App\PrizeElement;
use App\Tournament;
use Illuminate\Http\Request;

class PrizeController extends Controller
{

    /**
     * List all prize kits inluding their items and linked photos.
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getPrizeKits(Request $request) {

        $prizes = Prize::with(['tournament_type', 'user', 'elements', 'photos', 'elements.photos', 'elements.user'])
            ->orderBy('year', 'desc')->orderBy('tournament_type_id', 'desc')->orderBy('title', 'desc')->get();

        // hiding unimportant fields
        $prizes = $prizes->makeHidden(['creator']);
        foreach ($prizes as $prize) {
            if ($prize->user) {
                $prize->user->makeHidden(['name', 'username_preferred', 'supporter']);
            }
            if ($prize->elements) {
                $prize->elements->makeHidden(['prize_id', 'sort_order', 'creator', 'quantity']);
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
            'order' => $request->input('order'),
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
            'order' => $request->input('order')
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
        $this->authorize('admin', Tournament::class, $request->user());

        $newItem = PrizeElement::create([
            'prize_id' => $request->input('prize_id'),
            'quantity' => $request->input('quantity'),
            'title' => $request->input('title'),
            'type' => $request->input('type'),
            'creator' => $request->user()->id
        ]);

        return response()->json($newItem);
    }

    public function editPrizeItem($id, Request $request) {
        $item = PrizeElement::findOrFail($id);
        $this->authorize('admin', Tournament::class, $request->user());

        $item->update([
            'quantity' => $request->input('quantity'),
            'title' => $request->input('title'),
            'type' => $request->input('type')
        ]);

        return response()->json($item);
    }

    public function deletePrizeItem($id, Request $request) {
        // auth, error checking
        $this->authorize('admin', Tournament::class, $request->user());
        $item = PrizeElement::findOrFail($id);
        $item->delete();

        return response()->json('Prize item deleted.');
    }

}
