<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Profile\UpdateRequest;
use App\Http\Resources\UserResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProfileController extends Controller
{
    /**
     * Get the authenticated user's profile.
     */
    public function show(Request $request): JsonResponse
    {
        $user = $request->user();
        $user->load('roles', 'permissions');

        return response()->json([
            'success' => true,
            'data' => [
                'user' => new UserResource($user),
            ],
        ]);
    }

    /**
     * Update the authenticated user's profile.
     */
    public function update(UpdateRequest $request): JsonResponse
    {

        $user = $request->user();
      
        $data = $request->validated();

        // Handle image upload
        if ($request->hasFile('image')) {
            // Get the current image path (without the asset URL)
            $currentImagePath = $user->getRawOriginal('image');

            // Delete old image if exists
            if ($currentImagePath && Storage::disk('public')->exists($currentImagePath)) {
                Storage::disk('public')->delete($currentImagePath);
            }

            // Store new image
            $data['image'] = $request->file('image')->store('users', 'public');
        }

        // Update email verification status if email changed
        if (isset($data['email']) && $user->email !== $data['email']) {
            $data['email_verified_at'] = null;
        }

        // Update phone verification status if phone changed
        if (isset($data['phone']) && $user->phone !== $data['phone']) {
            $data['phone_verified_at'] = null;
        }

        $user->fill($data);
        $user->save();

        // Reload with relationships
        $user->load('roles', 'permissions');

        return response()->json([
            'success' => true,
            'message' => __('Profile updated successfully.'),
            'data' => [
                'user' => new UserResource($user),
            ],
        ]);
    }
}
