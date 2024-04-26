<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\{Validator};
use App\Models\User;
use App\Models\Posts;
use App\Models\Post_attachments;

class PostController extends Controller
{
    public function create_post(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'caption' => 'required',
            'attachments.*' => 'required|file|mimes:jpg,jpeg,png,webp'
        ]);
    
        if($validator->fails()){
            return response()->json([
                'message' => 'Invalid field',
                'errors' => $validator->errors()
            ], 422);
        }
    
        $user = Auth::user();
    
        $post = new Posts;
        $post->caption = $request->caption;
        $post->user_id = $user->id;
        $post->save();
    
       
        foreach($request->attachments as $attachment){
           $file =  $attachment->storeAs('posts' , $attachment->getClientOriginalName(), 'public');

            $attach = new Post_attachments;
            $attach->storage_path = $file;
            $attach->post_id = $post->id;
            $attach->save();
        }

        return response()->json([
            'message' => 'Create post success'
        ], 201);
    }
    
    public function delete_post(Request $request, $id)
    {
        $post = Posts::find($id);

        if(!$post)
        {
            return response()->json([
            'message' => 'Post not found'
            ], 404);
        }

        if($post->user_id !== auth()->id())
        {
            return response()->json([
            'message' => 'Forbidden access'
            ], 403);
        }

        $post->delete();

        return response()->json([
        'message' => 'Delete post success'
        ], 204);

    }

    public function get_allpost(Request $request)
    {
        $validator = Validator::make($request->all(), [
        'page' => 'integer|min:0',
        'size' => 'integer|min:1'
        ]);

        if($validator->fails()){
            return response()->json([
            'message' => 'Invalid field',
            'errors' => $validator->errors()
            ], 422);
        }

        $page = $request->input('page', 0);
        $size = $request->input('size', 10);

        $post = Posts::with('user','post_attachments')->paginate($size);

        $semua = $post->map(function ($post){
            return [
                'id' => $post->id,
                'caption' => $post->caption,
                'created_at' => $post->created_at,
                'deleted_at' => $post->deleted_at,
            'user' => [
                'id' => $post->user->id,
                'full_name' => $post->user->full_name,
                'username' => $post->user->username,
                'bio' => $post->user->bio,
                'is_private' => $post->user->is_private,
                'created_at' => $post->user->created_at,
            ],
            'attachments' => $post->post_attachments->map(function ($attach){
                return [
                    'id' => $attach->id,
                    'storage_path' => $attach->storage_path
                ];
            })
        ];
        });

        return response()->json([
        'page' => $page,
        'size' => $size,
        'posts' => $semua
        ], 200);
    }
}
