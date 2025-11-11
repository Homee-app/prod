<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use FFMpeg\FFMpeg;
use FFMpeg\Coordinate\TimeCode;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class Image extends Model
{
    use SoftDeletes;

    protected $table;

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->table = config('tables.images');
    }

    protected $fillable = ['path', 'type', 'thumbnail_path'];

    public function taggable()
    {
        return $this->morphTo();
    }

    public function setPathAttribute($value)
    {
        $this->attributes['path'] = $value;

        $extension = strtolower(pathinfo($value, PATHINFO_EXTENSION));

        $imageExtensions = ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'webp', 'svg'];
        $videoExtensions = ['mp4', 'mov', 'avi', 'mkv', 'flv', 'wmv'];

        if (in_array($extension, $imageExtensions)) {
            $this->attributes['type'] = 0; // image
        } elseif (in_array($extension, $videoExtensions)) {
            $this->attributes['type'] = 1; // video
        }

        if (in_array($extension, $imageExtensions)) {
        } elseif (in_array($extension, $videoExtensions)) {
            $this->attributes['type'] = 1; // video

            try {
                $videoPath = Storage::disk('public')->path($value);
                $thumbnailFilename = pathinfo($value, PATHINFO_FILENAME) . '.jpg';
                $thumbnailPath = Storage::disk('public')
                    ->path(pathinfo($value, PATHINFO_DIRNAME) . '/thumbnail-' . $thumbnailFilename);

                $ffmpeg = FFMpeg::create([
                    'ffmpeg.binaries'  => '/usr/bin/ffmpeg',
                    'ffprobe.binaries' => '/usr/bin/ffprobe',
                ]);

                $video = $ffmpeg->open($videoPath);
                $frame = $video->frame(TimeCode::fromSeconds(1));
                $frame->save($thumbnailPath);

                $this->attributes['thumbnail_path'] = str_replace(Storage::disk('public')->path(''), '', $thumbnailPath);
            } catch (\Exception $e) {
                Log::error("FFmpeg failed: " . $e->getMessage());
                $this->attributes['thumbnail_path'] = null;
            }
        }else{
            $this->attributes['type'] = 3; // chat
            $this->attributes['thumbnail_path'] = null;
        }
    }
}
