<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Kreait\Firebase;
use Kreait\Firebase\Factory;
use Kreait\Firebase\ServiceAccount;
use Kreait\Firebase\Database;
use App\User;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use App\Http\Controllers\Response;
use Illuminate\Support\Facades\Config;

class ChatController extends Controller
{
    public function UserList() {
        
        return User::all();
    }
    public function sendMessage(Request $request){
        
        $validator = Validator([
            'sender_id' => 'required',
            'receiver_id' => 'required',
            'message' => 'message',
        ]);
        
        if($validator->fails()){
            print_r($request->all());
        }else{
            $chat_id = ($request['sender_id'] < $request['receiver_id']) ? $request['sender_id'].'_'.$request['receiver_id'] : $request['receiver_id'].'_'.$request['sender_id'];
            $timeparts = explode(" ",microtime());
            $currenttime = bcadd(($timeparts[0]*1000),bcmul($timeparts[1],1000));
            $messages = 'messages/'.$chat_id;
            if($request['image']){
                $base64_image = $request['image'];

                if (preg_match('/^data:image\/(\w+);base64,/', $base64_image)) {
                    $data = substr($base64_image, strpos($base64_image, ',') + 1);
                    $data = base64_decode($data);
                    $file_name = 'image_' . time() . '.jpg';
                    Storage::disk('local')->put($file_name, $data);
                    $firebase_data_array = [
                        'chat_id' =>  $chat_id,
                        'message'  =>  '',
                        'media_url' => $file_name,
                        'msg_type' =>'img',
                        'sender_id' => $request['sender_id'],
                        'timestampe' => $currenttime
                    ];
                }else{
                    return Response()->json([
                        'message' => 'Improper image format'
                    ], 400);
                }
            }else{
                $firebase_data_array = [
                    'chat_id' =>  $chat_id,
                    'message'  =>  $request['message'],
                    'media_url' => '',
                    'msg_type' =>'msg',
                    'sender_id' => $request['sender_id'],
                    'timestampe' => $currenttime
                ];
            }
        $factory = (new Factory)->withServiceAccount(__DIR__.Config::get('constants.Chats.firbaseUrl'));
        $database = $factory->createDatabase();
        $createPost =   $database
                            ->getReference($messages)
                            ->push($firebase_data_array);
        if($createPost){
            return Response()->json([
                'message' => 'sent'
            ], 201);
        }else{
            return Response()->json([
                'message' => 'Internal server error'
            ], 500);
        }
        }

    }
    public function getMessage(Request $request) {
        $validator = Validator([
            'sender_id' => 'required',
            'receiver_id' => 'required',
        ]);
        
        if($validator->fails()){
            print_r($request->all());
        }else{
            $chat_id = ($request['sender_id'] < $request['receiver_id']) ? $request['sender_id'].'_'.$request['receiver_id'] : $request['receiver_id'].'_'.$request['sender_id'];
            $collection_name = 'messages/'.$chat_id;
            $factory = (new Factory)->withServiceAccount(__DIR__.Config::get('constants.Chats.firbaseUrl'));
            $database = $factory->createDatabase();
            $chatdata =   $database->getReference($collection_name)->getvalue();
            return response()->json(['chatData'=>$chatdata],201);
        }
        
    }
}
