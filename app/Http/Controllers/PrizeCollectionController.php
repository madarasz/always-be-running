<?php

namespace App\Http\Controllers;

use App\PrizeCollection;
use App\User;
use Illuminate\Http\Request;

use App\Http\Requests;

class PrizeCollectionController extends Controller
{

    /**
     * Get prize collection for a single user.
     * @param Request $request
     * @param $userId
     * @return \Illuminate\Http\JsonResponse
     */
    public function get(Request $request, $userId)
    {
        $currentUserId = $request->user() ? $request->user()->id : 0;
        $user = User::findOrFail($userId);

        $collection = PrizeCollection::where('user_id', $userId)->get();

        // hide unwanted fields
        if ($currentUserId != $userId) {
            if (!$user->prize_owning_public) {
                $collection = $collection->makeHidden(['owning']);
            }
            if (!$user->prize_trading_public) {
                $collection = $collection->makeHidden(['trading']);
            }
            if (!$user->prize_wanting_public) {
                $collection = $collection->makeHidden(['wanting']);
            }
        }

        return response()->json($collection);
    }


    public function update(Request $request, $id)
    {
        if (is_null($request->user()) || $request->user()->id != $id) {
            abort(403);
        }
        $user = User::findOrFail($id);

        foreach ($request->all() as $key => $item) {
            $collectionItem = PrizeCollection::where('user_id', $id)->where('prize_element_id', $key)->first();

            if ($item['owning'] + $item['trading'] + $item['wanting'] > 0) {

                if (is_null($collectionItem)) {
                    // create
                    PrizeCollection::create([
                        'prize_element_id' => $key,
                        'user_id' => $id,
                        'owning' => $item['owning'],
                        'trading' => $item['trading'],
                        'wanting' => $item['wanting']
                    ]);
                } else {
                    // update
                    $collectionItem->update([
                        'owning' => $item['owning'],
                        'trading' => $item['trading'],
                        'wanting' => $item['wanting']
                    ]);
                }

            } elseif (!is_null($collectionItem)) {
                // delete
                $collectionItem->delete();
            }
        }

        $collection = PrizeCollection::where('user_id', $id)->get();
        return response()->json($collection);
    }

}
