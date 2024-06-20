<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Friend;
use App\Events\FriendRequestSent;
use Illuminate\Support\Facades\Auth;

class FriendController extends Controller
{
    // Envoyer une demande d'ami
    public function addFriend(Request $request)
    {
        $user = Auth::user();
        $friend = User::find($request->friend_id);

        if (!$friend) {
            return response()->json(['message' => 'Utilisateur non trouvé.'], 404);
        }

        // Vérifiez si une demande d'ami existe déjà
        $existingFriendRequest = Friend::where(function($query) use ($user, $friend) {
            $query->where('user_id', $user->id)
                  ->where('friend_id', $friend->id);
        })->orWhere(function($query) use ($user, $friend) {
            $query->where('user_id', $friend->id)
                  ->where('friend_id', $user->id);
        })->first();

        if ($existingFriendRequest) {
            return response()->json(['message' => 'Demande d\'ami déjà envoyée.'], 400);
        }

        // Ajouter la demande d'ami
        Friend::create([
            'user_id' => $user->id,
            'friend_id' => $friend->id,
            'status' => 'pending',
        ]);

        // Émettre l'événement de demande d'ami
        event(new FriendRequestSent($user, $friend));

        return response()->json(['message' => 'Demande d\'ami envoyée.'], 200);
    }

   // Répondre à une demande d'ami
   public function respondToFriendRequest(Request $request)
   {
       $user = Auth::user();
       $friend = User::find($request->friend_id);
       $status = $request->status;
   
       if (!$friend) {
           return response()->json(['message' => 'Utilisateur non trouvé.'], 404);
       }
   
       if (!in_array($status, ['accepted', 'declined', 'blocked'])) {
           return response()->json(['message' => 'Statut invalide.'], 400);
       }
   
       $friendRequest = Friend::where('user_id', $friend->id)
                              ->where('friend_id', $user->id)
                              ->where('status', 'pending')
                              ->first();
   
       if (!$friendRequest) {
           return response()->json(['message' => 'Demande d\'ami non trouvée.'], 404);
       }
   
       $friendRequest->update(['status' => $status]);
   
       return response()->json(['message' => 'Demande d\'ami ' . $status . '.'], 200);
   }
   


    // Afficher les amis
    public function listFriends()
    {
        $user = Auth::user();
        $friends = $user->friends()->wherePivot('status', 'accepted')->get();

        return response()->json(['friends' => $friends], 200);
    }

    // Afficher les demandes d'ami en attente
    public function listFriendRequests()
    {
        $user = Auth::user();
        $friendRequests = $user->friendRequests()->wherePivot('status', 'pending')->get();

        return response()->json(['friend_requests' => $friendRequests], 200);
    }
}
