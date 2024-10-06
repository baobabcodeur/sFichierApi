<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Message;
use App\Models\Group;

use Illuminate\Support\Facades\Mail;
use App\Mail\FileUploadedNotification;


class MessageController extends Controller
{
    //
    public function sendMessage(Request $request, $groupId)
    {
        $request->validate([
            'content' => 'required|string',
        ]);

        $group = Group::findOrFail($groupId);
        $message = Message::create([
            'content' => $request->content,
            'group_id' => $group->id,
            'user_id' => auth()->id(),
        ]);

        return response()->json(['message' => 'Message sent successfully.', 'message' => $message], 201);
    }

    public function listMessages($groupId)
{
    $group = Group::findOrFail($groupId);
    $messages = $group->messages()->with('user:id,name')->get(); // Chargez les utilisateurs avec uniquement id et name

    return response()->json($messages);
}

public function uploadFile(Request $request, $groupId, $messageId = null)
{
    // Validation du fichier
    $request->validate([
        'file' => 'required|file|mimes:jpg,png,pdf,docx,txt,xlsx|max:10240',
    ]);

    // Récupérer le groupe
    $group = Group::findOrFail($groupId);

    // Récupérer le fichier
    $file = $request->file('file');
    $originalName = $file->getClientOriginalName(); // Obtenir le nom d'origine du fichier

    // Enregistrer le fichier dans le dossier storage/files tout en gardant son nom d'origine
    // Note : Vous pouvez aussi choisir de remplacer les espaces et autres caractères spéciaux par des underscores
    $safeName = preg_replace('/[^a-zA-Z0-9-_\.]/', '_', $originalName); // Assurez-vous que le nom est sûr
    $path = $file->storeAs('storage/files', $safeName, 'public'); // Utiliser storeAs pour garder le nom d'origine

    // Enregistrer le fichier avec l'ID de l'utilisateur
    $fileRecord = $group->files()->create([
        'group_id' => $groupId,
        'path' => $path, // Stocke le chemin dans la base de données
        'message_id' => $messageId,
        'user_id' => auth()->id(), // Enregistrer l'ID de l'utilisateur
    ]);

    // Récupérer tous les membres du groupe, sauf celui qui a uploadé le fichier
    $members = $group->users()
        ->where('users.id', '!=', auth()->id())
        ->get();

    // Envoyer un e-mail à chaque membre du groupe
    foreach ($members as $member) {
        Mail::to($member->email)->send(new FileUploadedNotification($group, $fileRecord));
    }

    return response()->json(['message' => 'File uploaded successfully.', 'file' => $fileRecord], 201);
}




public function listFiles($groupId)
{
    // Trouver le groupe par son ID
    $group = Group::findOrFail($groupId);

    // Récupérer les fichiers du groupe avec leurs détails et le nom de l'utilisateur
    $files = $group->files()->with('user:id,name')->get(['id', 'path', 'created_at', 'user_id']); // Vous pouvez aussi ajouter 'message_id' si nécessaire

    // Retourner la liste des fichiers sous forme de JSON
    return response()->json(['files' => $files]);
}





}
