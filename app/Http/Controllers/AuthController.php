<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use App\Mail\InvitationMail;
use App\Models\Invitation;
use App\Models\User;
use App\Models\Group;
use Illuminate\Support\Facades\Validator;
use App\Models\Otp;
use App\Mail\OtpMail;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    //

    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string',
            'email' => 'required|string|email|unique:users',
            'password' => 'required|string|min:6',
            'token' => 'nullable|string',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        $otpCode = mt_rand(100000, 999999);
        $expiresAt = now()->addMinutes(10);

        Otp::create([
            'user_id' => $user->id,
            'otp_code' => $otpCode,
            'expires_at' => $expiresAt,
        ]);

       


 // Envoyer l'OTP par email
 Mail::to($user->email)->send(new OtpMail($request->name, $request->email, $otpCode));

  // Vérifier si un token d'invitation est fourni
  if ($request->has('token')) {
    $invitation = Invitation::where('token', $request->token)->first();

    if ($invitation) {
        // Ajouter l'utilisateur au groupe associé
        $group = Group::find($invitation->group_id);
        $group->users()->attach($user->id);

        // Supprimer l'invitation (elle a été utilisée)
        $invitation->delete();
    }
}


 return response()->json(['message' => 'User registered successfully. Please check your email for OTP verification.']);
}

public function verifyOtp(Request $request)
{
 $request->validate([
     'email' => 'required|string|email',
     'otp_code' => 'required|string',
 ]);

 $otp = Otp::where('otp_code', $request->otp_code)
     ->whereHas('user', function ($query) use ($request) {
         $query->where('email', $request->email);
     })
     ->first();

 if (!$otp || $otp->expires_at < now()) {
     return response()->json(['message' => 'Invalid or expired OTP.'], 400);
 }

 // Activer l'utilisateur ou toute autre logique
 $otp->delete(); // Supprimer l'OTP après vérification

 return response()->json(['message' => 'OTP verified successfully.']);
}

public function login(Request $request)
{
    // Validation des entrées
    $validator = Validator::make($request->all(), [
        'email' => 'required|email',
        'password' => 'required|string',
    ]);

    if ($validator->fails()) {
        return response()->json([
            'message' => 'Validation failed',
            'errors' => $validator->errors(),
        ], 422);
    }

    // Authentifier l'utilisateur
    if (Auth::attempt(['email' => $request->email, 'password' => $request->password])) {
        $user = Auth::user();
        // Générer un token pour l'utilisateur (JWT ou autre)
        $token = $user->createToken('auth_token')->plainTextToken; // Si vous utilisez Sanctum

        return response()->json([
            'token' => $token,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
            ],
        ], 200);
    } else {
        return response()->json(['message' => 'Invalid credentials'], 401);
    }
}

public function invite(Request $request)
{
 $request->validate([
     'email' => 'required|string|email',
     'group_id' => 'required|exists:groups,id',
 ]);

  // Générer un jeton unique pour l'invitation
  $token = Str::random(32);

  // Stocker l'invitation dans la base de données
  Invitation::create([
      'email' => $request->email,
      'group_id' => $request->group_id,
      'token' => $token,
  ]);

  // Envoyer l'invitation par e-mail
//   $inviteUrl = route('register') . '?token=' . $token; // Lien vers la page d'inscription avec le jeton


$inviteUrl = "http://192.168.1.160:3000/registration" . '?token=' . $token;


  Mail::to($request->email)->send(new InvitationMail($inviteUrl));


 return response()->json(['message' => 'Invitation sent successfully.']);
}

public function logout(Request $request)
{
    // Révoquer le jeton de l'utilisateur authentifié
    $request->user()->currentAccessToken()->delete();

    return response()->json(['message' => 'Déconnexion réussie.'], 200);
}

}
