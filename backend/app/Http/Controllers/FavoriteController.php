<?php


namespace App\Http\Controllers;

use App\Models\UserFavorite;
use App\Models\Restaurant;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\Auth;

class FavoriteController extends Controller
{
    public function toggle(Request $request)
    {
        $request->validate([
            'restaurant_id' => 'required|exists:restaurants,id'
        ]);

        $favorite = UserFavorite::where('user_id', Auth::id())
            ->where('restaurant_id', $request->restaurant_id)
            ->first();

        if ($favorite) {
            $favorite->delete();
            return response()->json([
                'is_favorite' => false,
                'message' => 'Restaurante removido de favoritos'
            ]);
        } else {
            UserFavorite::create([
                'user_id' => Auth::id(),
                'restaurant_id' => $request->restaurant_id
            ]);

            return response()->json([
                'is_favorite' => true,
                'message' => 'Restaurante agregado a favoritos'
            ]);
        }
    }

    public function getUserFavorites()
    {
        $favorites = UserFavorite::where('user_id', Auth::id())
            ->with([
                'restaurant' => function ($query) {
                    $query->select(
                        'id',
                        'name',
                        'slug',
                        'logo',
                        'category',
                        'rating',
                        'delivery_fee',
                        'delivery_time_min',
                        'delivery_time_max',
                        'minimum_order',
                        'is_active'
                    );
                }
            ])
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($favorites);
    }

    public function checkFavorite($restaurantId)
    {
        $isFavorite = UserFavorite::where('user_id', Auth::id())
            ->where('restaurant_id', $restaurantId)
            ->exists();

        return response()->json(['is_favorite' => $isFavorite]);
    }

    public function getFavoriteStats()
    {
        $stats = [
            'total_favorites' => UserFavorite::where('user_id', Auth::id())->count(),
            'categories' => UserFavorite::where('user_id', Auth::id())
                ->join('restaurants', 'user_favorites.restaurant_id', '=', 'restaurants.id')
                ->select('restaurants.category')
                ->groupBy('restaurants.category')
                ->pluck('category'),
            'recent_favorites' => UserFavorite::where('user_id', Auth::id())
                ->with('restaurant:id,name,logo')
                ->orderBy('created_at', 'desc')
                ->limit(5)
                ->get()
        ];

        return response()->json($stats);
    }
}
