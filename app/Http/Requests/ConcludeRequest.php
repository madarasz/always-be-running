<?php

namespace App\Http\Requests;

use App\Http\Requests\Request;

class ConcludeRequest extends Request
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
            'players_number' => 'integer|between:1,1000',
            'top_number' => 'integer|between:0,16|players_top:'.Request::get('players_number').','.Request::get('top_number')
        ];
    }

    public function messages() {
        return [
            'players_top' => 'Players in top cut should be less than the total number of players.'
        ];
    }

}
