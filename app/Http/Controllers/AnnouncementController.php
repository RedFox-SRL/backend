<?php

namespace App\Http\Controllers;

use App\Models\Announcement;
use App\Models\GlobalAnnouncement;
use App\Models\Management;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\ApiCode;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;

class AnnouncementController extends Controller
{
    public function store(Request $request)
    {
        $user = Auth::user();

        if ($user->role !== 'teacher') {
            return $this->respondUnAuthorizedRequest(ApiCode::UNAUTHORIZED);
        }

        $validatedData = $request->validate([
            'management_id' => 'required|exists:management,id',
            'announcement' => 'nullable|string|max:2000',
            'files' => 'required_without:announcement|array|min:1',
            'files.*' => 'file|max:10240', // 10MB max
            'links' => 'nullable|json',
            'youtube_videos' => 'nullable|json',
            'is_global' => 'boolean',
        ]);

        $management = Management::findOrFail($validatedData['management_id']);

        DB::beginTransaction();

        try {
            if ($validatedData['is_global'] ?? false) {
                $announcement = GlobalAnnouncement::create([
                    'user_id' => $user->id,
                    'content' => $validatedData['announcement'] ?? '',
                    'semester' => $management->semester,
                    'year' => $management->start_date->year,
                ]);
            } else {
                $announcement = Announcement::create([
                    'management_id' => $validatedData['management_id'],
                    'user_id' => $user->id,
                    'content' => $validatedData['announcement'] ?? '',
                ]);
            }

            $this->processFiles($request, $announcement);
            $this->processLinks($validatedData, $announcement);
            $this->processYoutubeVideos($validatedData, $announcement);

            DB::commit();

            $announcement->load('files', 'links', 'youtubeVideos', 'user');
            return response()->json(['message' => 'Anuncio creado con éxito', 'announcement' => $this->formatAnnouncement($announcement)], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Error al crear el anuncio', 'error' => $e->getMessage()], 500);
        }
    }

    public function index(Request $request, $managementId)
    {
        $management = Management::findOrFail($managementId);

        $regularAnnouncements = Announcement::where('management_id', $managementId)
            ->with(['user', 'files', 'links', 'youtubeVideos'])
            ->get();

        $globalAnnouncements = GlobalAnnouncement::where('semester', $management->semester)
            ->whereYear('created_at', $management->start_date->year)
            ->with(['user', 'files', 'links', 'youtubeVideos'])
            ->get();

        $allAnnouncements = $regularAnnouncements->concat($globalAnnouncements)
            ->sortByDesc('created_at')
            ->values();

        $page = max(1, intval($request->input('page', 1)));
        $perPage = 10;

        $paginatedAnnouncements = new \Illuminate\Pagination\LengthAwarePaginator(
            $allAnnouncements->forPage($page, $perPage),
            $allAnnouncements->count(),
            $perPage,
            $page,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        $formattedAnnouncements = $paginatedAnnouncements->through(function ($announcement) {
            return $this->formatAnnouncement($announcement);
        });

        return response()->json($formattedAnnouncements);
    }

    private function processFiles($request, $announcement)
    {
        if ($request->hasFile('files')) {
            foreach ($request->file('files') as $file) {
                $path = $file->store('announcement_files');
                $announcement->files()->create([
                    'name' => $file->getClientOriginalName(),
                    'path' => $path,
                    'mime_type' => $file->getMimeType(),
                    'size' => $file->getSize(),
                ]);
            }
        }
    }

    private function processLinks($validatedData, $announcement)
    {
        $links = json_decode($validatedData['links'] ?? '[]', true);
        if (is_array($links)) {
            foreach ($links as $link) {
                if (isset($link['url'])) {
                    $announcement->links()->create([
                        'url' => $link['url'],
                        'title' => $link['title'] ?? null,
                    ]);
                }
            }
        }
    }

    private function processYoutubeVideos($validatedData, $announcement)
    {
        $youtubeVideos = json_decode($validatedData['youtube_videos'] ?? '[]', true);
        if (is_array($youtubeVideos)) {
            foreach ($youtubeVideos as $video) {
                if (isset($video['video_id'])) {
                    $announcement->youtubeVideos()->create([
                        'video_id' => $video['video_id'],
                        'title' => $video['title'] ?? null,
                    ]);
                }
            }
        }
    }

    private function formatAnnouncement($announcement)
    {
        return [
            'id' => $announcement->id,
            'management_id' => $announcement instanceof Announcement ? $announcement->management_id : null,
            'user_id' => $announcement->user_id,
            'content' => $announcement->content,
            'created_at' => $announcement->created_at,
            'updated_at' => $announcement->updated_at,
            'is_global' => $announcement instanceof GlobalAnnouncement,
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

    private function formatYoutubeVideos($videos)
    {
        return $videos->map(function ($video) {
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
