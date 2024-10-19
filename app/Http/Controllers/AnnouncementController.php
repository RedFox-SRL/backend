<?php

namespace App\Http\Controllers;

use App\Models\Announcement;
use App\Models\AnnouncementFile;
use App\Models\AnnouncementLink;
use App\Models\AnnouncementYoutubeVideo;
use App\Models\Management;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AnnouncementController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'management_id' => 'required|exists:management,id',
            'announcement' => 'nullable|string|max:2000',
            'files' => 'nullable|array',
            'files.*' => 'file|max:10240', // 10MB max
            'links' => 'nullable|array',
            'links.*.url' => 'required|url',
            'youtube_videos' => 'nullable|array',
            'youtube_videos.*.video_id' => 'required|string',
        ]);

        $user = Auth::user();
        $management = Management::findOrFail($request->management_id);

//        // Verificar si el usuario tiene permiso para crear anuncios en este management
//        if ($user->teacher && $user->teacher->id === $management->teacher_id) {
//            // El usuario es el profesor de este management
//        } elseif ($user->student && $user->student->managements->contains($management->id)) {
//            // El usuario es un estudiante en este management
//        } else {
//            return response()->json(['message' => 'No tienes permiso para crear anuncios en este management'], 403);
//        }

        DB::beginTransaction();

        try {
            $announcement = Announcement::create([
                'management_id' => $request->management_id,
                'user_id' => $user->id,
                'content' => $request->announcement ?? '',
            ]);

            // Procesar archivos
            if ($request->hasFile('files')) {
                foreach ($request->file('files') as $file) {
                    $path = $file->store('announcement_files');
                    AnnouncementFile::create([
                        'announcement_id' => $announcement->id,
                        'name' => $file->getClientOriginalName(),
                        'path' => $path,
                        'mime_type' => $file->getMimeType(),
                        'size' => $file->getSize(),
                    ]);
                }
            }

            // Procesar enlaces
            if ($request->has('links')) {
                foreach ($request->links as $link) {
                    AnnouncementLink::create([
                        'announcement_id' => $announcement->id,
                        'url' => $link['url'],
                        'title' => $link['title'] ?? null,
                        'description' => $link['description'] ?? null,
                        'image' => $link['image'] ?? null,
                    ]);
                }
            }

            // Procesar videos de YouTube
            if ($request->has('youtube_videos')) {
                foreach ($request->youtube_videos as $video) {
                    AnnouncementYoutubeVideo::create([
                        'announcement_id' => $announcement->id,
                        'video_id' => $video['video_id'],
                        'title' => $video['title'] ?? null,
                        'description' => $video['description'] ?? null,
                        'thumbnail' => $video['thumbnail'] ?? null,
                    ]);
                }
            }

            DB::commit();

            return response()->json(['message' => 'Anuncio creado con éxito', 'announcement' => $announcement], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Error al crear el anuncio', 'error' => $e->getMessage()], 500);
        }
    }

    public function index(Request $request, $managementId)
    {
        $management = Management::findOrFail($managementId);
        $user = Auth::user();

        // Verificar si el usuario tiene permiso para ver los anuncios de este management
        if ($user->teacher && $user->teacher->id === $management->teacher_id) {
            // El usuario es el profesor de este management
        } elseif ($user->student && $user->student->managements->contains($management->id)) {
            // El usuario es un estudiante en este management
        } else {
            return response()->json(['message' => 'No tienes permiso para ver los anuncios de este management'], 403);
        }

        $announcements = Announcement::where('management_id', $managementId)
            ->with(['user', 'files', 'links', 'youtubeVideos'])
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return response()->json($announcements);
    }

    // Otros métodos como update, delete, etc., pueden ser implementados aquí
}
