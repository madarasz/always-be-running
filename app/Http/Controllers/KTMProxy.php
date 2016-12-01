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
        $data = json_decode(file_get_contents('http://www.knowthemeta.com/JSON/Cardpoolnames'));
        return response()->json($data);
    }

    public function getCardpoolStat($side, $pack) {
        $data = json_decode(file_get_contents('http://www.knowthemeta.com/JSON/Tournament/'.$side.'/'.$pack));
        return response()->json($data);
    }
}
