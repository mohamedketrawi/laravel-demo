<?php


namespace App\Http\Controllers;


use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class UserController extends Controller
{

    public function getUserPosition(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $numberOfUsers = $request->get('number_of_users', 5);

        $maxNumberPerRange = $numberOfUsers - 1;

        $userRank = User::query()
            ->leftJoin('images', 'users.image_id', '=', 'images.id')
            ->join(DB::raw('(SELECT karma_score, ROW_NUMBER() OVER (ORDER BY karma_score desc) as "position"
                                   FROM users
                                   GROUP BY karma_score
                                   ORDER by karma_score) score_position'),
                function ($join) {
                    $join->on('users.karma_score', '=', 'score_position.karma_score');
                })
            ->where('users.id', $user->id)
            ->select('users.id', 'score_position.karma_score', 'score_position.position', 'images.url')
            ->first();

        $lowerRankUsers = User::query()
            ->leftJoin('images', 'users.image_id', '=', 'images.id')
            ->join(DB::raw('(SELECT karma_score, ROW_NUMBER() OVER (ORDER BY karma_score desc) as "position"
                                   FROM users
                                   GROUP BY karma_score
                                   ORDER by karma_score) score_position'),
                function ($join) {
                    $join->on('users.karma_score', '=', 'score_position.karma_score');
                })
            ->where('score_position.karma_score', '<', $user->karma_score)
            ->select('users.id', 'score_position.karma_score', 'score_position.position', 'images.url')
            ->orderBy('score_position.position')
            ->limit($maxNumberPerRange)
            ->get();

        $higherRankUsers = User::query()
            ->leftJoin('images', 'users.image_id', '=', 'images.id')
            ->join(DB::raw('(SELECT karma_score, ROW_NUMBER() OVER (ORDER BY karma_score desc) as "position"
                                   FROM users
                                   GROUP BY karma_score
                                   ORDER by karma_score) score_position'),
                function ($join) {
                    $join->on('users.karma_score', '=', 'score_position.karma_score');
                })
            ->where('score_position.karma_score', '>', $user->karma_score)
            ->select('users.id', 'score_position.karma_score', 'score_position.position', 'images.url')
            ->orderBy('score_position.position', 'desc')
            ->limit($maxNumberPerRange)
            ->get();

        $result = collect();
        if ($lowerRankUsers->isEmpty()) {
            $result = $result->merge($higherRankUsers);
            $result->push($userRank);
        } else if (empty($higherRankUsers)) {
            $result->push($userRank);
            $result = $result->merge($lowerRankUsers);
        } else if ($lowerRankUsers->count() < intval($maxNumberPerRange / 2)) {
            $result = $result->merge($higherRankUsers->slice(0, $maxNumberPerRange - $lowerRankUsers->count()));
            $result->push($userRank);
            $result = $result->merge($lowerRankUsers);
        } else if ($higherRankUsers->count() < intval($maxNumberPerRange / 2)) {
            $result = $result->merge($lowerRankUsers);
            $result->push($userRank);
            $result = $result->merge($lowerRankUsers->slice(0, $maxNumberPerRange - $higherRankUsers->count()));
        } else {
            $result = $result->merge($higherRankUsers->slice(0, intval($maxNumberPerRange / 2)));
            $result->push($userRank);
            $result = $result->merge($lowerRankUsers->slice(0, intval($maxNumberPerRange / 2)));
        }

        return $result;
    }
}
