<?php

namespace App\Http\Controllers;

use App\Models\Review;
use App\Models\Order;
use App\Models\Restaurant;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ReviewController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'restaurant_id' => 'required|exists:restaurants,id',
            'order_id' => 'nullable|exists:orders,id',
            'rating' => 'required|integer|between:1,5',
            'comment' => 'nullable|string|max:1000',
            'is_anonymous' => 'boolean',
            'food_rating' => 'required|integer|between:1,5',
            'service_rating' => 'required|integer|between:1,5',
            'delivery_rating' => 'required|integer|between:1,5',
            'images' => 'nullable|array|max:5',
            'images.*' => 'image|mimes:jpeg,png,jpg|max:2048'
        ]);

        // Verificar que el usuario puede hacer review
        if ($request->order_id) {
            $order = Order::where('id', $request->order_id)
                ->where('user_id', Auth::id())
                ->where('status', 'delivered')
                ->whereDoesntHave('review')
                ->first();

            if (!$order) {
                return response()->json(['error' => 'No puedes revisar esta orden'], 403);
            }
        }

        // Procesar imágenes si las hay
        $imagePaths = [];
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
                $path = $image->store('reviews', 'public');
                $imagePaths[] = $path;
            }
        }

        $review = Review::create([
            'user_id' => Auth::id(),
            'restaurant_id' => $request->restaurant_id,
            'order_id' => $request->order_id,
            'rating' => $request->rating,
            'food_rating' => $request->food_rating,
            'service_rating' => $request->service_rating,
            'delivery_rating' => $request->delivery_rating,
            'comment' => $request->comment,
            'is_anonymous' => $request->is_anonymous ?? false,
            'images' => $imagePaths
        ]);

        // Actualizar ratings del restaurante
        $this->updateRestaurantRating($request->restaurant_id);

        return response()->json($review->load('user'), 201);
    }

    public function getRestaurantReviews($restaurantId)
    {
        $reviews = Review::where('restaurant_id', $restaurantId)
            ->with(['user:id,name,avatar', 'order:id'])
            ->withCount(['helpfulVotes', 'notHelpfulVotes'])  // ✅ ESTOS NOMBRES ESTÁN CORRECTOS
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        // Estadísticas de reviews
        $stats = [
            'total_reviews' => Review::where('restaurant_id', $restaurantId)->count(),
            'average_rating' => Review::where('restaurant_id', $restaurantId)->avg('rating'),
            'average_food_rating' => Review::where('restaurant_id', $restaurantId)->avg('food_rating'),
            'average_service_rating' => Review::where('restaurant_id', $restaurantId)->avg('service_rating'),
            'average_delivery_rating' => Review::where('restaurant_id', $restaurantId)->avg('delivery_rating'),
            'rating_distribution' => Review::where('restaurant_id', $restaurantId)
                ->select('rating', DB::raw('count(*) as count'))
                ->groupBy('rating')
                ->orderBy('rating', 'desc')
                ->get()
        ];

        return response()->json([
            'reviews' => $reviews,
            'stats' => $stats
        ]);
    }

    public function markHelpful(Request $request, $reviewId)
    {
        $request->validate([
            'helpful' => 'required|boolean'
        ]);

        $review = Review::findOrFail($reviewId);

        // Verificar si ya votó
        $existingVote = DB::table('review_votes')
            ->where('review_id', $reviewId)
            ->where('user_id', Auth::id())
            ->first();

        if ($existingVote) {
            DB::table('review_votes')
                ->where('review_id', $reviewId)
                ->where('user_id', Auth::id())
                ->update(['is_helpful' => $request->helpful]);
        } else {
            DB::table('review_votes')->insert([
                'review_id' => $reviewId,
                'user_id' => Auth::id(),
                'is_helpful' => $request->helpful,
                'created_at' => now()
            ]);
        }

        return response()->json(['success' => true]);
    }

    public function respond(Request $request, $reviewId)
    {
        $request->validate([
            'response' => 'required|string|max:500'
        ]);

        $review = Review::findOrFail($reviewId);

        // Verificar que el usuario es el dueño del restaurante
        $restaurant = Restaurant::where('id', $review->restaurant_id)
            ->where('owner_id', Auth::id())
            ->first();

        if (!$restaurant) {
            return response()->json(['error' => 'No autorizado'], 403);
        }

        $review->update([
            'response' => $request->response,
            'responded_at' => now()
        ]);

        return response()->json($review);
    }

    private function updateRestaurantRating($restaurantId)
    {
        $restaurant = Restaurant::find($restaurantId);
        $avgRating = Review::where('restaurant_id', $restaurantId)->avg('rating');
        $totalReviews = Review::where('restaurant_id', $restaurantId)->count();

        $restaurant->update([
            'rating' => round($avgRating, 1),
            'total_reviews' => $totalReviews
        ]);
    }
}
