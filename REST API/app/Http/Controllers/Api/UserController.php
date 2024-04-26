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

class UserController extends Controller
{
    public function get_user(Request $request)
    {
        $user = Auth::user();

        $tidakdifollow = User::whereNotIn('id', function($query) use ($user){
        $query->select('following_id')
                ->from('follow')
                ->where('follower_id', $user->id);
        })
        ->where('id','!=',$user->id)
        ->get();


        return response()->json([
        'users' => $tidakdifollow
        ], 200);
    }


    public function detail_user(Request $request, $username)
    {
        $user = User::where('username', $username)->first();
    
        if (!$user) {
            return response()->json([
                'message' => 'User not found'
            ], 404);
        }
    
        $follow_status = 'not-following';
        if (Auth::check()) {
            $follow = Follow::where('follower_id', Auth::id())
                ->where('following_id', $user->id)
                ->first();
    
            if ($follow) {
                $follow_status = $follow->is_accepted ? 'following' : 'requested';
            }
        }
    
        $posts = [];
        $posts = $user->posts()->when(
            !$user->is_private || $follow_status === 'following',
            fn($query) => $query->with('post_attachments')->get()->map(fn($post) => [
                'id' => $post->id,
                'caption' => $post->caption,
                'created_at' => $post->created_at,
                'attachments' => $post->post_attachments->map(fn($attachment) => [
                    'id' => $attachment->id,
                    'storage_path' => $attachment->storage_path
                ])
            ])
        );

    
        $posts_count = $user->posts()->count();
        $followers_count = $user->follower()->count();
        $following_count = $user->following()->count();
    
        $response = [
            'id' => $user->id,
            'full_name' => $user->full_name,
            'username' => $user->username,
            'bio' => $user->bio,
            'is_private' => $user->is_private,
            'created_at' => $user->created_at,
            'is_your_account' => Auth::id() === $user->id,
            'following_status' => $follow_status,
            'posts_count' => $posts_count,
            'followers_count' => $followers_count,
            'following_count' => $following_count,
            'posts' => $posts
        ];
    
        return response()->json($response, 200);
    }
}
