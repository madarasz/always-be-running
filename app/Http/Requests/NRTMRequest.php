<?php

namespace App\Http\Requests;

use App\Http\Requests\Request;

class NRTMRequest extends Request
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
            'jsonresults' => 'required_without:conclusion_code',
            'conclusion_code' => 'conc_code:'.Request::get('conclusion_code')
        ];
    }

    public function messages() {
        return [
            'jsonresults.required_without' => 'Either upload the NRTM file or provide the conclusion code.',
            'conclusion_code.conc_code' => 'Conclusion code does not exist.'
        ];
    }

}
