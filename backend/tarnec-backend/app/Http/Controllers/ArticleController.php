<?php

namespace App\Http\Controllers;

use App\Models\Article;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class ArticleController extends Controller
{
    public function index()
    {
        try {
            $articles = Article::all();
            return response()->json([
                'status' => 'success',
                'data' => $articles
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Erreur lors de la récupération des articles',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'title' => 'required|string|max:255',
                'description' => 'required|string'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $article = Article::create($request->all());

            return response()->json([
                'status' => 'success',
                'message' => 'Article créé avec succès',
                'data' => $article
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Erreur lors de la création de l\'article',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function show($id)
    {
        try {
            $article = Article::findOrFail($id);
            return response()->json([
                'status' => 'success',
                'data' => $article
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Article non trouvé',
                'error' => 'L\'article avec l\'ID ' . $id . ' n\'existe pas'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Erreur lors de la récupération de l\'article',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $article = Article::findOrFail($id);

            $validator = Validator::make($request->all(), [
                'title' => 'sometimes|required|string|max:255',
                'description' => 'sometimes|required|string'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            if (empty($request->all())) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Aucune donnée à mettre à jour'
                ], 400);
            }

            $article->update($request->all());

            return response()->json([
                'status' => 'success',
                'message' => 'Article mis à jour avec succès',
                'data' => $article
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Article non trouvé',
                'error' => 'L\'article avec l\'ID ' . $id . ' n\'existe pas'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Erreur lors de la mise à jour de l\'article',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $article = Article::findOrFail($id);
            
            // Vérifier si l'article a des prêts associés
            if ($article->lendings()->exists()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Impossible de supprimer l\'article',
                    'error' => 'Cet article a des prêts associés'
                ], 409);
            }

            $article->delete();

            return response()->json([
                'status' => 'success',
                'message' => 'Article supprimé avec succès'
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Article non trouvé',
                'error' => 'L\'article avec l\'ID ' . $id . ' n\'existe pas'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Erreur lors de la suppression de l\'article',
                'error' => $e->getMessage()
            ], 500);
        }
    }
} 