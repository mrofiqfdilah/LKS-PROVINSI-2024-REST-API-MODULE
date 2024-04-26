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

class FollowingController extends Controller
{
    public function follow_user(Request $request, $username)
    {
        $user = User::where('username', $username)->first();

        if(!$user)
        {
            return response()->json([
            'message' => 'User not found'
            ], 404);
        }

        if($user->id === auth()->id())
        {
            return response()->json([
            'message' => 'You are not allowed to follow yourself'
            ], 422);
        }

        $sudahfollow = Follow::where('follower_id', auth()->id())
                             ->where('following_id', $user->id)
                             ->first();

        if($sudahfollow)
        {
            return response()->json([
            'message' => 'You are already followed',
            'status' => $sudahfollow->is_accepted ? 'following' : 'requested'
            ], 422);
        }

        $status = $user->is_private ? 'requested' : 'following';

        $follow = new Follow;
        $follow->follower_id = auth()->id();
        $follow->following_id = $user->id;
        $follow->is_accepted = !$user->is_private;
        $follow->save();

        return response()->json([
            'message' => 'Follow success',
            'status' => $status
        ], 200);
    }

    public function unfollow_user(Request $request, $username)
    {
        $user = User::where('username', $username)->first();

        if(!$user)
        {
            return response()->json([
            'message' => 'User not found'
            ], 404);
        }

        $sudahfollow = Follow::where('follower_id', auth()->id())
                             ->where('following_id', $user->id)
                             ->first();

        if(!$sudahfollow)
        {
            return response()->json([
            'message' => 'You are not following the user'
            ], 422);
        }

        $sudahfollow->delete();

        return response()->json([
            'message' => 'Unfollow success',
        ], 204);
    }

    public function user_following(Request $request)
    {
        $user = Auth::user();
    
        $following = $user->following;
    
        $datafollowing = $following->map(function ($as){
            $is_accepted = $as->pivot->is_accepted == 0 ? true : false;
            return [
                'id' => $as->id,
                'full_name' => $as->full_name,
                'username' => $as->username,
                'bio' => $as->bio,
                'is_private' => $as->is_private,
                'created_at' => $as->created_at,
                'is_requested' => $is_accepted
            ];
        });
    
        return response()->json([
            'following' => $datafollowing
        ], 200);
    }
}
