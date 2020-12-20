<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Intervention\Image\Facades\Image;

class ProfilesController extends Controller
{
    public function show(User $user)
    {
        $follows = (auth()->user()) ? auth()->user()->following->contains($user->id) : false;

        $postsCount = Cache::remember(
            'count.post' . $user->id,
            now()->addSeconds(60),
            function () use ($user) {
                return $user->posts->count();
            });

        $followersCount = Cache::remember(
            'count.follower' . $user->id,
            now()->addSeconds(60),
            function () use ($user) {
                return $user->profile->followers->count();
            });

        $followingCount = Cache::remember(
            'count.following' . $user->id,
            now()->addSeconds(60),
            function () use ($user) {
                return $user->following->count();
            });
        
        return view('profile.index', [
            'user' => $user,
            'follows' => $follows,
            'postsCount' => $postsCount,
            'followersCount' => $followersCount,
            'followingCount' => $followingCount
        ]);
    }

    public function edit(User $user)
    {
        $this->authorize('update', $user->profile);

        if ($user->id !== auth()->id()) {
            abort(404);
        }

        return view('profile.edit', compact('user'));
    }

    public function update(Request $request, User $user)
    {
        $this->authorize('update', $user->profile);

        if ($user->id !== auth()->id()) {
            return redirect()->route('welcome');
        }

        $this->validate($request, [
            'title' => 'max:255',
            'description' => 'max:1200',
            'url' => 'url',
            'image' => 'image'
        ]);

        if ($request->file('image')) {
            $filenameWithExtension = $request->file('image')->getClientOriginalName();
            $extension = $request->file('image')->getClientOriginalExtension();
            $filename = pathinfo($filenameWithExtension, PATHINFO_FILENAME);
            $filenameToStore = time() . '_' . uniqid() . '_' . $filename . '.' . $extension;

            $request->file('image')->storeAs('public/profile/photos/' . auth()->id(), $filenameToStore);
            
            $image = Image::make($request->file('image')->getRealPath())->fit(1000, 1000);
            $savePath = public_path('storage/profile/thumbs/' . auth()->id(). '/');

            if (!file_exists($savePath)) {
                mkdir($savePath, 666, true);
                $image->save($savePath . $filenameToStore, 100);
            } else {
                $image->save($savePath . $filenameToStore, 100);
            }
            
            auth()->user()->profile->update([
                'title' => $request->title,
                'description' => $request->description,
                'url' => $request->url,
                'image' => $filenameToStore
            ]);
        } else {
            auth()->user()->profile->update([
                'title' => $request->title,
                'description' => $request->description,
                'url' => $request->url
            ]);
        }

        return redirect()->route('profile.show', $user)->with('info', 'Profile successfully updated!');
    }
}
