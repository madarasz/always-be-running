<?php

namespace App\Http\Requests;

use App\Http\Requests\Request;
use App\TournamentType;

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
        $player_rules = is_null(Request::get('concluded')) ? '' : '|required'; // if concluded, players number is required

        $rules = [
            'date' => 'date_format:Y.m.d.',
            'players_number' => 'integer|between:1,1000'.$player_rules,
            'top_number' => 'integer|between:0,1000|players_top:'.Request::get('players_number').','.Request::get('top_number'),
            'link_facebook' => ['regex:/https:\/\/.*facebook\.com\/((groups)|(events))/'],
            'end_date' => 'date_later:'.Request::get('date').','.Request::get('end_date').
                '|date_later_max_week:'.Request::get('date').','.Request::get('end_date')
        ];

        if (Request::get('tournament_type_id') != 7) // non-online tournament requires location
        {
            $rules = array_merge($rules, [
                'location_city' => 'required',
                'location_country' => 'required'
            ]);
        }

        return $rules;
    }

    public function messages() {
        return [
            'date_format' => 'Please enter the date using YYYY.MM.DD. format.',
            'players_top' => 'Players in top cut should be less than the total number of players.',
            'link_facebook.regex' => 'Facebook event/group should be a valid URL of an event or group.',
            'date_later' => 'End date should be later than (start) date.',
            'date_later_max_week' => 'Event should not be longer than a week.'
        ];
    }

    public function sanitize_data($user_id = null)
    {
        $input = array_map('trim', $this->all());

        // if tournament title missing, construct it
        if (!strlen($input['title'])) {
            if (strlen($input['location_store'])) {
                $input['title'] = $input['location_store'];
            } else {
                $input['title'] = $input['location_city'];
            }
            $input['title'] = $input['title'].' - '.
                ucwords(TournamentType::findOrFail($input['tournament_type_id'])->type_name);
        }

        // concluded tournaments
        if (array_key_exists('concluded', $input))
        {
            $input['concluded'] = 1;
            if ($input['top_number'] == 0) {
                $input['top_number'] = null;
            }
        } else {
            $input['concluded'] = 0;
            $input['players_number'] = null;
            $input['top_number'] = null;
        }

        // mandatory decklist
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

        // online tournament has no location
        if ($input['tournament_type_id'] == 7)
        {
            $input['location_country'] = '';
            $input['location_us_state'] = '';
            $input['location_city'] = '';
            $input['location_store'] = '';
            $input['location_address'] = '';
            $input['location_lat'] = null;
            $input['location_long'] = null;
        }

        // non-tournament has no conclusion
        if ($input['tournament_type_id'] == 8)
        {
            $input['concluded'] = 0;
            $input['players_number'] = null;
            $input['top_number'] = null;
            if ($input['end_date_selector'] != 'recurring') {
                $input['recur_weekly'] = null;
            } else {    // recurring event
                $input['date'] = null;
                $input['cardpool_id'] = 'unknown';
            }
        } else {
            $input['recur_weekly'] = null;
        }

        // end date not necessary
        if ($input['end_date_selector'] != 'multiple') {
            $input['end_date'] = null;
        }

        $this->replace($input);
    }
}
