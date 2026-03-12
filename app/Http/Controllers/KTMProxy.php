<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;

/**
 * Hack!
 * Proxying KnowTheMeta data, since knowthemeta.com only serves HTTP data, no HTTPS.
 * Currently this is a technical limitation of github.io.
 * Class KTMProxy
 * @package App\Http\Controllers
 */
class KTMProxy extends Controller
{
    public function getCardpoolNames() {
        $rawData = file_get_contents('http://www.knowthemeta.com/JSON/Cardpoolnames');
        if (!is_string($rawData) || !json_validate($rawData)) {
            return response()->json(null);
        }

        $data = json_decode($rawData, true);
        return response()->json($data);
    }

    public function getCardpoolStat($side, $pack) {
        $rawData = file_get_contents('http://www.knowthemeta.com/JSON/Tournament/'.$side.'/'.rawurlencode($pack));
        if (!is_string($rawData) || !json_validate($rawData)) {
            return response()->json(null);
        }

        $data = json_decode($rawData, true);
        return response()->json($data);
    }
}
