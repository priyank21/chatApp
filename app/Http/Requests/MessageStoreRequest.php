<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class MessageStoreRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'sender_id' => 'required',
            'receiver_id' => 'required',
            'message' => 'required',
            'image' => 'required',
        ];
    }

    public function messages()
    {
        return [
            'sender_id.required' => 'Sender Id is required!',
            'receiver_id.required' => 'Receiver Id is required!',
            'message.required' => 'Password is required!',
            'image.required' => 'image is required!',
        ];
    }
    
}
