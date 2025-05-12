<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class StudentPasswordResetController extends Controller
{
    public function forgotPassword(Request $request)
    {
        try {
            $request->validate([
                'email' => 'required|email|exists:students,email'
            ]);

            $status = Password::broker('students')->sendResetLink(
                $request->only('email')
            );

            if ($status === Password::RESET_LINK_SENT) {
                return response()->json([
                    'message' => 'Lien de réinitialisation envoyé par email',
                    'status' => 'success'
                ]);
            }

            Log::error('Erreur lors de l\'envoi du lien de réinitialisation', [
                'email' => $request->email,
                'status' => $status
            ]);

            return response()->json([
                'message' => 'Impossible d\'envoyer le lien de réinitialisation',
                'status' => 'error',
                'details' => $status
            ], 400);

        } catch (\Exception $e) {
            Log::error('Exception lors de la réinitialisation du mot de passe', [
                'error' => $e->getMessage(),
                'email' => $request->email ?? null
            ]);

            return response()->json([
                'message' => 'Une erreur est survenue lors de l\'envoi du lien de réinitialisation',
                'status' => 'error',
                'details' => $e->getMessage()
            ], 500);
        }
    }

    public function resetPassword(Request $request)
    {
        try {
            $request->validate([
                'token' => 'required',
                'email' => 'required|email',
                'password' => 'required|min:8|confirmed',
            ]);

            $status = Password::broker('students')->reset(
                $request->only('email', 'password', 'password_confirmation', 'token'),
                function (Student $student, string $password) {
                    $student->forceFill([
                        'password' => Hash::make($password),
                        'remember_token' => Str::random(60),
                    ])->save();
                }
            );

            if ($status === Password::PASSWORD_RESET) {
                return response()->json([
                    'message' => 'Mot de passe réinitialisé avec succès',
                    'status' => 'success'
                ]);
            }

            Log::error('Erreur lors de la réinitialisation du mot de passe', [
                'email' => $request->email,
                'status' => $status
            ]);

            return response()->json([
                'message' => 'Impossible de réinitialiser le mot de passe',
                'status' => 'error',
                'details' => $status
            ], 400);

        } catch (\Exception $e) {
            Log::error('Exception lors de la réinitialisation du mot de passe', [
                'error' => $e->getMessage(),
                'email' => $request->email ?? null
            ]);

            return response()->json([
                'message' => 'Une erreur est survenue lors de la réinitialisation du mot de passe',
                'status' => 'error',
                'details' => $e->getMessage()
            ], 500);
        }
    }
} 