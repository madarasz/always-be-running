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
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        if (Request::get('tournament_type_id') == 6)
        {
            return [
                'title' => 'required',
                'date' => 'required|date_format:Y.m.d.',
                'players_number' => 'integer|between:1,1000',
                'top_number' => 'integer|between:0,1000',
            ];
        } else {
            return [
                'title' => 'required',
                'date' => 'required|date_format:Y.m.d.',
                'location_city' => 'required',
                'location_country' => 'required',
                'players_number' => 'integer|between:1,1000',
                'top_number' => 'integer|between:0,1000',
            ];
        }
    }

    public function messages() {
        return [
            'date_format' => 'Please enter the date using YYYY.MM.DD. format.',
        ];
    }

    public function sanitize_data($user_id = null)
    {
        $input = array_map('trim', $this->all());
        if (array_key_exists('concluded', $input))
        {
            $input['concluded'] = 1;
        } else {
            $input['concluded'] = 0;
            $input['players_number'] = null;
            $input['top_number'] = null;
        }

        if (array_key_exists('decklist', $input))
        {
            $input['decklist'] = 1;
        } else {
            $input['decklist'] = 0;
        }

        if (!is_null($user_id))
        {
            $input['creator'] = $user_id;
        }

        if ($input['tournament_type_id'] == 6)
        {
            $input['location_country'] = '';
            $input['location_us_state'] = '';
            $input['location_city'] = '';
            $input['location_store'] = '';
            $input['location_address'] = '';
        }

        $this->replace($input);
    }
}
