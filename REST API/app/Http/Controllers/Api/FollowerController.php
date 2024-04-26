<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\{Validator};
use App\Models\User;
use App\Models\Posts;
use App\Models\Post_attachments;
use App\Models\Follow;

class FollowerController extends Controller
{
    public function accept_follow(Request $request, $username)
    {
        $user = User::where('username',$username)->first();

        if(!$user)
        {
            return response()->json([
            'message' => 'User not found'
            ], 404);
        }

        $sudahfollow = Follow::where('follower_id', $user->id)
        ->where('following_id', auth()->id())
        ->first();

        if(!$sudahfollow)
        {
            return response()->json([
            'message' => 'The user is not following you'
            ], 422);
        }

        if($sudahfollow->is_accepted)
        {
            return response()->json([
            'message' => 'Follow request is already accepted'
            ], 422);
        }

        $sudahfollow->is_accepted = true;
        $sudahfollow->save();

        return response()->json([
        'message' => 'Follow request accepted'
        ], 200);
    }

    public function user_follower(Request $request, $username)
    {
        $user =  User::where('username', $username)->first();
    
        $follower = $user->follower;
    
        $datafollower = $follower->map(function ($ass){
            $is_accepted = $ass->pivot->is_accepted == 0 ? true : false;
            return [
                'id' => $ass->id,
                'full_name' => $ass->full_name,
                'username' => $ass->username,
                'bio' => $ass->bio,
                'is_private' => $ass->is_private,
                'created_at' => $ass->created_at,
                'is_accepted' => $is_accepted,
            ];
        });
    
        return response()->json([
        'follower' => $datafollower
        ], 200);
    }
}
