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
use App\ApiCode;

class AnnouncementController extends Controller
{
    public function store(Request $request)
    {
        $user = Auth::user();

        if ($user->role !== 'teacher') {
            return $this->respondUnAuthorizedRequest(ApiCode::UNAUTHORIZED);
        }

        $request->validate([
            'management_id' => 'required|exists:management,id',
            'announcement' => 'required|string|max:2000',
            'files' => 'nullable|array',
            'files.*' => 'file|max:10240', // 10MB max
            'links' => 'nullable|json',
            'youtube_videos' => 'nullable|json',
            'is_global' => 'boolean',
        ]);

        $management = Management::findOrFail($request->management_id);

        DB::beginTransaction();

        try {
            $announcementData = [
                'user_id' => $user->id,
                'content' => $request->announcement,
                'is_global' => $request->is_global ?? false,
            ];

            if ($request->is_global) {
                $announcements = $this->createGlobalAnnouncements($announcementData, $management);
            } else {
                $announcementData['management_id'] = $request->management_id;
                $announcements = [Announcement::create($announcementData)];
            }

            foreach ($announcements as $announcement) {
                $this->processFiles($request, $announcement);
                $this->processLinks($request, $announcement);
                $this->processYoutubeVideos($request, $announcement);
            }

            DB::commit();

            $announcement = $announcements[0];
            $announcement->load('files', 'links', 'youtubeVideos');
            return response()->json(['message' => 'Anuncio creado con éxito', 'announcement' => $announcement], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Error al crear el anuncio', 'error' => $e->getMessage()], 500);
        }
    }

    private function createGlobalAnnouncements($announcementData, $management)
    {
        $announcements = [];
        $relatedManagements = Management::where('semester', $management->semester)
            ->whereYear('start_date', $management->start_date->year)
            ->get();

        foreach ($relatedManagements as $relatedManagement) {
            $announcementData['management_id'] = $relatedManagement->id;
            $announcements[] = Announcement::create($announcementData);
        }

        return $announcements;
    }

    private function processFiles($request, $announcement)
    {
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
    }

    private function processLinks($request, $announcement)
    {
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
    }

    private function processYoutubeVideos($request, $announcement)
    {
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
    }

    public function index(Request $request, $managementId)
    {
        $management = Management::findOrFail($managementId);
        $user = Auth::user();

        $announcements = Announcement::where(function ($query) use ($managementId, $management) {
            $query->where('management_id', $managementId)
                ->orWhere(function ($q) use ($management) {
                    $q->where('is_global', true)
                        ->whereHas('management', function ($subQ) use ($management) {
                            $subQ->where('semester', $management->semester)
                                ->whereYear('start_date', $management->start_date->year);
                        });
                });
        })
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
            'is_global' => $announcement->is_global,
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
