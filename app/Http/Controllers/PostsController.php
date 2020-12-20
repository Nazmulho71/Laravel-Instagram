<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Http\Request;
use Intervention\Image\Facades\Image;

class PostsController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $user = auth()->user()->following()->pluck('profiles.user_id');

        $followingPosts = Post::whereIn('user_id', $user)->with('user')->get();
        $myPosts = auth()->user()->posts()->with('user')->get();

        $posts = $followingPosts->merge($myPosts)->sortDesc();

        return view('posts.index', compact('posts'));
    }

    public function create()
    {
        return view('posts.create');
    }

    public function store(Request $request)
    {
        $this->validate($request, [
            'caption' => 'required|max:255',
            'image' => 'required|image',
        ]);

        $filenameWithExtension = $request->file('image')->getClientOriginalName();
        $extension = $request->file('image')->getClientOriginalExtension();
        $filename = pathinfo($filenameWithExtension, PATHINFO_FILENAME);
        $filenameToStore = time() . '_' . uniqid() . '_' . $filename . '.' . $extension;

        $request->file('image')->storeAs('public/uploads/photos/' . auth()->id(), $filenameToStore);
        
        $image = Image::make($request->file('image')->getRealPath())->fit(1200, 1200);
        $savePath = public_path('storage/uploads/thumbs/' . auth()->id(). '/');

        if (!file_exists($savePath)) {
            mkdir($savePath, 666, true);
            $image->save($savePath . $filenameToStore, 100);
        } else {
            $image->save($savePath . $filenameToStore, 100);
        }

        auth()->user()->posts()->create([
            'caption' => $request->caption,
            'image' => $filenameToStore
        ]);

        return redirect()->route('profile.show', auth()->user())->with('info', 'Photo added!');
    }

    public function show(Post $post)
    {
        return view('posts.show', compact('post'));
    }
}
