<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreMoviesRequest;
use App\Http\Requests\UpdateMovieRequest;
use App\Models\Movie;
use Illuminate\Http\Request;

class MovieController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Movie::query(); // persiapan pengecekan query parameter

        if ($request->has('title')) {
            $sortDirection = $request->title === 'asc' ? 'asc' : 'desc';
            $query->orderBy('title', $sortDirection);
        }

        if ($request->has('rating')) {
            $sortDirection = $request->rating === 'asc' ? 'asc' : 'desc';
            $query->orderBy('rating', $sortDirection);
        }

        if ($request->has('release_date')) {
            $sortDirection = $request->release_date === 'asc' ? 'asc' : 'desc';
            $query->orderBy('release_date', $sortDirection);
        }

        $movies = $query->with('media')->paginate(10);

        return response()->json($movies);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreMoviesRequest $request)
    {
        // 1. Cek role
        if(auth()->user()->role  != "admin") {
            return response()->json([
                'message' => 'Forbiden',
            ], 403);
        }

        // 2. Create Movie
        $movie = Movie::create([
            'title' => $request->title,
            'description' => $request->description,
            'release_date' => $request->release_date,
            'rating' => $request->rating,
        ]);


        // 3. Create Media & Store to disk
        for ($i=1; $i <= 3; $i++) {
            if ($request->hasFile('media'.$i)) {
                $mediaFilename = $request->file('media'.$i)->hashName();
                $movie->media()->create([
                    'path' => $mediaFilename,
                    'is_thumbnail' => $i == 1, // true jika image1
                ]);
                $request->file('media'.$i)->storeAs('public/media', $mediaFilename);
            }
        }

        // 3. return response
        return response()->json([
            'message' => 'Movie created successfully',
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $movie = Movie::with('media')->find($id);
        return response()->json($movie);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateMovieRequest $request, string $id)
    {
        // 1. Cek role
        if(auth()->user()->role  != "admin") {
            return response()->json([
                'message' => 'Forbiden',
            ], 403);
        }

        // 2. Update Movie
        $movie = Movie::with('media')->find($id);
        $movie->update([
            'title' => $request->title,
            'description' => $request->description,
            'release_date' => $request->release_date,
            'rating' => $request->rating,
        ]);
        // 3. Check updated media
        for ($i=1; $i <= 3; $i++) {
            if ($request->hasFile('media'.$i)) {
                unlink(storage_path('app/public/media/' . $movie->media[$i-1]->path)); // delete old file

                $mediaFilename = $request->file('media'.$i)->hashName(); // generate new filename
                // update path media pada database
                $movie->media[$i-1]->update([
                    'path' => $mediaFilename,
                ]);

                $request->file('media'.$i)->storeAs('public/media', $mediaFilename);
            }
        }

        // 3. return response
        return response()->json([
            'message' => 'Movie updated successfully',
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        // 1. Cek role
        if(auth()->user()->role  != "admin") {
            return response()->json([
                'message' => 'Forbiden',
            ], 403);
        }

        // 2. Delete Movie & Media
        $movie = Movie::with('media')->find($id);
        foreach ($movie->media as $media) {
            unlink(storage_path('app/public/media/' . $media->path)); // delete all media file
        }
        $movie->delete();

        // 3. return response
        return response()->json([
            'message' => 'Movie deleted successfully',
        ], 200);
    }
}
