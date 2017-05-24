<?php

namespace App\Http\Requests;

use App\Http\Requests\Request;

class EntryNoDeckRequest extends Request
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
//            'rank_top_nodeck' => 'tournament_top:'.Request::get('rank_nodeck').','.Request::get('top_number').'|tournament_not_top:'.Request::get('rank_nodeck').','.Request::get('top_number')
        ];
    }

    public function messages() {
        return [
            'tournament_top' => "The \"rank after top cut\" must be \"below top cut\", because you didn't make the it to the top cut.",
            'tournament_not_top' => "The \"rank after top cut\" must be set, because you made the top cut during the swiss rounds.",
        ];
    }

}
