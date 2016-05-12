<?php

namespace App\Http\Requests;

use App\Http\Requests\Request;

class TournamentRequest extends Request
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true; //TODO
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'title' => 'required',
            'date' => 'required|date_format:Y.m.d.',
            'location_city' => 'required',
            'players_number' => 'integer|between:1,1000',
            'top_number' => 'integer|between:0,1000',
            'location_country' => 'not_in:0'
        ];
    }

    public function messages() {
        return [
            'date_format' => 'Please enter the date using YYYY.MM.DD. format.',
            'not_in' => 'Please select a country.'
        ];
    }
}
