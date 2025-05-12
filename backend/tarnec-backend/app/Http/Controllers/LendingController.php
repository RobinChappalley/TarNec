<?php

namespace App\Http\Controllers;

use App\Models\Lending;
use App\Models\Article;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class LendingController extends Controller
{
    public function index()
    {
        try {
            $lendings = Lending::with(['student', 'article'])->get();
            return response()->json([
                'status' => 'success',
                'data' => $lendings
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Erreur lors de la récupération des prêts',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'article_id' => 'required|exists:articles,id',
                'start_date' => 'required|date|after_or_equal:today',
                'end_date' => 'required|date|after:start_date'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Vérifier si l'article est disponible
            $article = Article::findOrFail($request->article_id);
            $activeLending = Lending::where('article_id', $request->article_id)
                ->where('status', 'active')
                ->first();

            if ($activeLending) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Article non disponible',
                    'error' => 'Cet article est déjà emprunté'
                ], 409);
            }

            // Créer le prêt
            $lending = Lending::create([
                'student_id' => Auth::id(),
                'article_id' => $request->article_id,
                'start_date' => $request->start_date,
                'end_date' => $request->end_date,
                'status' => 'pending',
                'lend_date' => null,
                'return_date' => null
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Demande de prêt créée avec succès',
                'data' => $lending
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Erreur lors de la création du prêt',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function show($id)
    {
        try {
            $lending = Lending::with(['student', 'article'])->findOrFail($id);
            return response()->json([
                'status' => 'success',
                'data' => $lending
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Prêt non trouvé',
                'error' => $e->getMessage()
            ], 404);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $lending = Lending::findOrFail($id);

            $validator = Validator::make($request->all(), [
                'status' => 'required|in:pending,active,returned,cancelled'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Mise à jour du statut
            $lending->status = $request->status;

            // Mise à jour des dates selon le statut
            if ($request->status === 'active') {
                $lending->lend_date = Carbon::now();
            } elseif ($request->status === 'returned') {
                $lending->return_date = Carbon::now();
            }

            $lending->save();

            return response()->json([
                'status' => 'success',
                'message' => 'Statut du prêt mis à jour avec succès',
                'data' => $lending
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Erreur lors de la mise à jour du prêt',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $lending = Lending::findOrFail($id);

            if ($lending->status === 'active') {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Impossible de supprimer le prêt',
                    'error' => 'Le prêt est actuellement actif'
                ], 409);
            }

            $lending->delete();

            return response()->json([
                'status' => 'success',
                'message' => 'Prêt supprimé avec succès'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Erreur lors de la suppression du prêt',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function myLendings()
    {
        try {
            $lendings = Lending::with('article')
                ->where('student_id', Auth::id())
                ->get();

            return response()->json([
                'status' => 'success',
                'data' => $lendings
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Erreur lors de la récupération de vos prêts',
                'error' => $e->getMessage()
            ], 500);
        }
    }
} 