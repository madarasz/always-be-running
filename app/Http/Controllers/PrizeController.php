<?php

namespace App\Http\Controllers;

use App\Prize;
use Illuminate\Http\Request;

class PrizeController extends Controller
{

    public function getPrizeKits(Request $request) {

        $prizes = Prize::with(['tournament_type', 'user', 'elements', 'photos', 'elements.photos', 'elements.user'])
            ->orderBy('order', 'desc')->get();

        // hiding unimportant fields
        $prizes = $prizes->makeHidden(['tournament_type_id', 'creator']);
        foreach ($prizes as $prize) {
            if ($prize->user) {
                $prize->user->makeHidden(['name', 'username_preferred', 'supporter']);
            }
            if ($prize->elements) {
                $prize->elements->makeHidden(['prize_id', 'sort_order', 'creator']);
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

}
