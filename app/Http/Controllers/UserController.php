<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function getEmail($id)
    {
        try {
            $user = User::find($id);
            
            if (!$user) {
                return response()->json(['message' => 'User not found'], 404);
            }
            return response()->json(['email' => $user->email]);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Email not fount'], 500);
        }
    }

    public function getAllEmails(): JsonResponse
    {
        try {
            $emails = User::pluck('email');
            return response()->json($emails);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Unable to retrieve email addresses.'], 500);
        }
    }
    
}
