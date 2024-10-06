<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Group;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use App\Mail\GroupNotificationMail;
use App\Models\User;

class GroupController extends Controller
{
    public function create(Request $request)
    {
        $request->validate([
            'name' => 'required|string',
            'description' => 'nullable|string',
        ]);

        $group = Group::create([
            'name' => $request->name,
            'description' => $request->description,
        ]);

        $group->users()->attach(Auth::id());

        return response()->json(['message' => 'Group created successfully.', 'group' => $group], 201);
    }

    public function list()
    {
        $groups = Group::all();
        return response()->json(['groups' => $groups]);
    }

    public function addMember(Request $request, $groupId)
    {
        // Valider la requête
        $request->validate([
            'user_id' => 'required|exists:users,id',
        ]);
    
        // Trouver le groupe
        $group = Group::findOrFail($groupId);
    
        // Attacher le nouvel utilisateur au groupe
        $group->users()->attach($request->user_id);
    
        // Préparer et envoyer un email à tous les anciens membres
        $members = $group->users()->where('users.id', '!=', $request->user_id)->get(); // Obtenir tous les membres sauf le nouveau
    
        $subject = "Nouveau Membre Ajouté";
        $messageContent = "Un nouveau membre a été ajouté au groupe : " . $group->name;
    
        // Envoyer un email à chaque ancien membre
        foreach ($members as $member) {
            Mail::to($member->email)->send(new GroupNotificationMail($subject, $messageContent));
        }
    
        // Notifier l'utilisateur nouvellement ajouté
        $newMember = User::findOrFail($request->user_id);
        $notificationMessage = "Vous avez été ajouté au groupe : " . $group->name;
        
        // Envoyer une notification à l'utilisateur nouvellement ajouté
        // Exemple d'envoi d'email ou de notification
        Mail::to($newMember->email)->send(new GroupNotificationMail($subject, $notificationMessage));
        
        // Si vous utilisez des notifications Laravel
        // $newMember->notify(new GroupAddedNotification($group));
    
        return response()->json(['message' => 'Membre ajouté et notifications envoyées.']);
    }
    

    public function listMembers($groupId)
    {
        $group = Group::findOrFail($groupId);
        $members = $group->users;

        return response()->json($members);
    }

    public function getNonMembers($groupId)
    {
        // Récupérer le groupe
        $group = Group::findOrFail($groupId);

        // Récupérer tous les utilisateurs qui ne sont pas dans le groupe
        $nonMembers = User::whereDoesntHave('groups', function ($query) use ($groupId) {
            $query->where('group_id', $groupId);
        })->get();

        return response()->json($nonMembers);
    }

    public function getUserGroups()
    {
        // Récupérer l'utilisateur authentifié
        $user = Auth::user();

        // Récupérer les groupes auxquels l'utilisateur appartient
        $groups = $user->groups;

        return response()->json(['groups' => $groups], 200);
    }
}
