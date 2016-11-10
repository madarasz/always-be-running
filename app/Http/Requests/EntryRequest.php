<?php

namespace App\Http\Requests;

use App\Http\Requests\Request;

class EntryRequest extends Request
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
            'corp_deck' => 'required_without:other_corp_deck',
            'runner_deck' => 'required_without:other_runner_deck',
            'other_corp_deck' => 'integer|min:1',
            'other_runner_deck' => 'integer|min:1',
            'rank_top' => 'tournament_top:'.Request::get('rank').','.Request::get('top_number').'|tournament_not_top:'.Request::get('rank').','.Request::get('top_number')
        ];
    }

    public function messages() {
        return [
            'corp_deck.required_without' => 'Create a corporation deck on NetrunnerDB, so you will be able to choose it here.',
            'runner_deck.required_without' => 'Create a runner deck on NetrunnerDB, so you will be able to choose it here.',
            'tournament_top' => "The \"rank after top cut\" must be \"below top cut\", because you didn't make the it to the top cut.",
            'tournament_not_top' => "The \"rank after top cut\" must be set, because you made the top cut during the swiss rounds.",
            'other_corp_deck.integer' => "The corp deck ID must be a number. You can find it in its URL.",
            'other_runner_deck.integer' => "The runner deck ID is a number. You can find it in its URL."
        ];
    }

}
