<?php

namespace App\Http\Controllers;

use App\Models\hotel;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class HotelController extends Controller
{
    /**
     * Display a listing of the resource.
     */
   // seul les admines connecter peuvent afficher les hotels
    public function index()
    {
       try{
        $user = auth()->user();
        // Vérifiez si l'utilisateur est authentifié
        if (!$user) {
            return response()->json([
                'status_code' => 403,
                'message' => 'Non autorisé, veuillez vous connecter.',
            ], 403);
         }else{
            $hotels = Hotel::all();
        return response()->json([
            'hotels' => $hotels,
            'status_code' => 200,
            'message' => 'Hôtels récupérés avec succès',
        ]);
         } 
       }catch(Exception $e){
            return response()->json([
               'status_code' => 500,
               'message' => 'Erreur lors de la récupération des hôtels.',
            ], 500);
        }
       }
    

    public function store(Request $request)
    {
        // Vérification de l'authentification
        try {
            $user = auth()->user();
            if (!$user) {
                return response()->json([
                    'status_code' => 403,
                    'message' => 'Non autorisé, veuillez vous connecter.',
                ], 403);
            }

            // Validation des données
            $validatedData = $request->validate([
                'name' => ['required', 'string', 'max:255'],
                'address' => ['required', 'string', 'max:255'],
                'email' => ['required', 'string', 'email', 'max:255', 'unique:hotels'],
                'phone' => ['required', 'numeric', 'digits:9','unique:hotels'], // Validation pour 9 chiffres
                'price' => ['required', 'numeric', 'gt:0'], // Vérifie que le prix est supérieur à 0
                'devise' => ['required', 'in:USD,EUR,CFA,XOF'], // Validation de la devise
                'image_url' => ['nullable', 'image', 'max:2048'], // Validation de l'image
            ]);

            // Gestion de l'image
            $imageName = null;
            if ($request->hasFile('image_url')) {
                $imageName = Str::random(5) . "." . $request->image_url->getClientOriginalExtension();
                //move deplace le fichier télécharger vers app/public/storage
                $request->image_url->move(public_path('/storage'), $imageName);
                $validatedData['image_url'] = $imageName; 
            }

            // Ajoutez l'admin_id à validatedData
            $validatedData['admin_id'] = $user->id; // Assurez-vous que l'utilisateur est authentifié

            // Création d'un nouvel hôtel
            $hotel = Hotel::create($validatedData);

            return response()->json([
                'hotel' => $hotel,
                'status_code' => 201,
                'message' => 'Hôtel créé avec succès',
            ]);
        } catch (Exception $e) {
            return response()->json([
                'status_code' => 500,
                'message' => 'Erreur lors de la création de l\'hôtel : ' . $e->getMessage(),
            ], 500);
        }
    }
    //afficher un hotel par l'id
    public function show($id){
        // Fetch the hotel by id'
        try{
            $user=auth()->user();
            if (!$user) {
                return response()->json(['status' => 'error', 'message' => 'Non autorisé, veuillez vous connecter.'], 403);
            } else {
                $hotel = Hotel::find($id);
                if($hotel){
                    return response()->json([
                        'hotel' => $hotel,
                        'status_code' => 200,
                        'message' => 'Hotel found successfully',
                    ]);
                } else {    
                    return response()->json([
                       'status_code' => 404,
                       'message' => 'Hotel not found',
                    ]);
                }
            }
        }catch(Exception $e){
            return response()->json([
                'status_code' => 500,
                'message' => 'Erreur lors de la récupération de l\'hôtel.',
            ], 500);
        }
    }
    //modifier un hotel
    public function update(Request $request, $id)
    {
        // Fetch the hotel by id
        try {
            $user = auth()->user();
            if (!$user) {
                return response()->json(['status' => 'error', 'message' => 'Non autorisé, veuillez vous connecter.'], 403);
            } else {
                $hotel = Hotel::find($id);
                if ($hotel) {
                    // Validation des données
                    $validatedData = $request->validate([
                        'name' => ['required', 'string', 'max:255'],
                        'address' => ['required', 'string', 'max:255'],
                        'email' => ['required', 'string', 'email', 'max:255', 'unique:hotels,email,' . $hotel->id],
                        'phone' => ['required', 'numeric', 'digits:9'],
                        'price' => ['required', 'numeric', 'gt:0'],
                        'devise' => ['required', 'in:USD,EUR,CFA,XOF'],
                        'image_url' => ['nullable', 'image', 'max:2048'],
                    ]);

                    // Mettre à jour l'hôtel avec les données validées
                    $hotel->update($validatedData);
                    // dd($hotel);
                    return response()->json(['success' => true, 'message' => 'Hôtel mis à jour avec succès']);
                } else {
                    return response()->json(['success' => false, 'message' => 'Hôtel non trouvé']);
                }
            }
        } catch (ValidationException $e) {
            return response()->json([
                'status_code' => 422,
                'message' => 'Erreur de validation',
                'errors' => $e->validator->errors(),
            ], 422);
        } catch (Exception $e) {
            return response()->json([
                'status_code' => 500,
                'message' => 'Erreur lors de la mise à jour de l\'hôtel : ' . $e->getMessage(),
            ], 500);
        }
    }
    //supprimer un hotel
    public function destroy($id){
        // Fetch the hotel by id
       try{
        $user = auth()->user();
        if (!$user) {
            return response()->json(['status' => 'error', 'message' => 'Non autorisé, veuillez vous connecter.'], 403);
        } else {
            $hotel = Hotel::find($id);
            if($hotel){
                // Delete the hotel
                $hotel->delete();
                return response()->json(array('success' => true, 'message' => 'Hôtel supprimé avec succès'));
            } else{
                return response()->json(array('success' => false, 'message' => 'Hotel not found'));
            }
        }}catch(Exception $e){
            return response()->json([
               'status_code' => 500,
               'message' => 'Erreur lors de la suppression de l\'hôtel.',
            ], 500);
        }
    }
    //recherche d'un hotel par son nom
    public function search($name){
        // Fetch hotels by name
       try{
        $user = auth()->user();
        if (!$user) {
            return response()->json(['status' => 'error', 'message' => 'Non autorisé, veuillez vous connecter.'], 403);
        } else {
            $hotels = Hotel::where('name', 'LIKE', '%'.$name.'%')->paginate(5);
        if($hotels->count()){
            return response()->json([
                'hotels' => $hotels,
               'status_code' => 200,
               'message' => 'Hotels retrieved successfully',
            ]);
        } else{
            return response()->json([
               'status_code' => 404,
               'message' => 'No hotels found with the given name',
            ]);
        }
        }
       }catch(Exception $e){
            return response()->json([
               'status_code' => 500,
               'message' => 'Erreur lors de la recherche de l\'hôtel.',
            ], 500);
        }
    }
}