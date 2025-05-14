<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Game;
use App\Models\Score;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class GameController extends Controller
{
    public function getGames(Request $request)
    {
        $page = $request->query('page', 0);
        $size = $request->query('size', 10);
        $sortBy = $request->query('sortBy', 'title');
        $sortDir = $request->query('sortDir', 'asc');

        $validator = Validator::make($request->all(), [
            'page' => 'integer|min:0',
            'size' => 'integer|min:1',
            'sortBy' => 'string|in:title,popular,uploaddate',
            'sortDir' => 'string|in:asc,desc',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'invalid',
                'message' => 'Request body is not valid',
                'errors' => $validator->errors(),
            ], 422);
        }

        $query = Game::query()->with(['user', 'latestGameVersion'])->whereNull('deleted_at')->has('latestGameVersion');
        $totalCount = $query->count();

        switch ($sortBy) {
            case 'uploaddate':
                $query->orderBy('created_at', $sortDir);
                break;
            case 'title':
            default:
                $query->orderBy('title', $sortDir);
                break;
        }

        $games =  $query->paginate($size, ['*'], 'page', $page + 1);
        $games = $query->get();

        $transformedGame = $games->map(function ($game) {
            $latestVersion = $game->latestGameVersion;

            if ($latestVersion) {
                $thumbnails = Storage::allFiles($latestVersion->storage_path);
                $imageExt = ['jpg', 'jpeg', 'png', 'gif', 'bmp'];
                $thumbnailsPaths = array_filter($thumbnails, function ($path) use ($imageExt) {
                    $ext = pathinfo($path, PATHINFO_EXTENSION);
                    return in_array(strtolower($ext), $imageExt);
                });

                $thumbailsString = implode(',', $thumbnailsPaths);
                $game->thumbnail = $thumbailsString;
                $game->uploadedTimestamp = $latestVersion->created_at;
                $game->scoreCount = Score::where('game_version_id', $latestVersion->id)->count();
            } else {
                $game->thumbnail = null;
                $game->uploadedTimestamp = null;
                $game->scoreCount = 0;
            }

            $game->author = $game->user ? $game->user->username : 'Tidak Diketahui';
            return $game;
        });

        if ($sortBy == 'popular') {
            $transformedGame = $transformedGame->sortByDesc('scoreCount');

            if ($sortDir == 'asc') {
                $transformedGame = $transformedGame->sortBy('scoreCount');
            }

            $start = $page * $size;
            $transformedGame = $transformedGame->slice($start, $size)->values();
        }

        return response()->json([
            'page' => $page,
            'size' => $size,
            'totalElements' => $totalCount,
            'content' =>  $transformedGame,
        ], 200);
    }
}
