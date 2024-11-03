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
use Illuminate\Support\Facades\Storage;

class AnnouncementController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'management_id' => 'required|exists:management,id',
            'announcement' => 'nullable|string|max:2000',
            'files' => 'nullable|array',
            'files.*' => 'file|max:10240', // 10MB max
            'links' => 'nullable|json',
            'youtube_videos' => 'nullable|json',
        ]);

        $user = Auth::user();
        $management = Management::findOrFail($request->management_id);

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
                $links = json_decode($request->links, true);
                foreach ($links as $link) {
                    AnnouncementLink::create([
                        'announcement_id' => $announcement->id,
                        'url' => $link['url'],
                        'title' => $link['title'] ?? null,
                    ]);
                }
            }

            // Procesar videos de YouTube
            if ($request->has('youtube_videos')) {
                $youtubeVideos = json_decode($request->youtube_videos, true);
                foreach ($youtubeVideos as $video) {
                    AnnouncementYoutubeVideo::create([
                        'announcement_id' => $announcement->id,
                        'video_id' => $video['video_id'],
                        'title' => $video['title'] ?? null,
                    ]);
                }
            }

            DB::commit();

            $announcement->load('files', 'links', 'youtubeVideos');
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

        $announcements = Announcement::where('management_id', $managementId)
            ->with(['user', 'files', 'links', 'youtubeVideos'])
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        $formattedAnnouncements = $announcements->through(function ($announcement) {
            return $this->formatAnnouncement($announcement);
        });

        return response()->json($formattedAnnouncements);
    }

    private function formatAnnouncement($announcement)
    {
        return [
            'id' => $announcement->id,
            'management_id' => $announcement->management_id,
            'user_id' => $announcement->user_id,
            'content' => $announcement->content,
            'created_at' => $announcement->created_at,
            'updated_at' => $announcement->updated_at,
            'user' => $announcement->user,
            'files' => $this->formatFiles($announcement->files),
            'links' => $announcement->links,
            'youtube_videos' => $this->formatYoutubeVideos($announcement->youtubeVideos),
        ];
    }

    private function formatFiles($files)
    {
        return $files->map(function ($file) {
            return [
                'id' => $file->id,
                'name' => $file->name,
                'url' => $this->getFileUrl($file->path),
                'mime_type' => $file->mime_type,
                'size' => $file->size,
            ];
        });
    }

    private function formatYoutubeVideos($youtubeVideos)
    {
        return $youtubeVideos->map(function ($video) {
            return [
                'id' => $video->id,
                'video_id' => $video->video_id,
                'title' => $video->title,
                'embed_url' => "https://www.youtube.com/embed/{$video->video_id}",
                'thumbnail_url' => "https://img.youtube.com/vi/{$video->video_id}/0.jpg",
            ];
        });
    }

    private function getFileUrl($path)
    {
        return Storage::url($path);
    }
}
