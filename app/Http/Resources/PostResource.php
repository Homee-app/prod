<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\UserResource;

class PostResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  Request  $request
     * @return array<string, mixed>
     */
    
    public function toArray(Request $request): array
    {
        
        $roles = [
            1 => 'Admin',
            2 => 'Child',
            3 => 'Parent',
            4 => 'Business',
        ];
        // Relationships should be loaded in the controller, not here.
        return [
            'id'          => $this->id,
            //'user'        => new UserResource($this->whenLoaded('user')),
            'user'        => $this->whenLoaded('user', function () use ($roles) {
                return [
                    'id'            => $this->user->id,
                    'first_name'    => $this->user->first_name,
                    'last_name'     => $this->user->last_name,
                    'email'         => $this->user->email,
                    'dob'           => $this->user->dob,
                    'profile_photo' => $this->user->profile_photo ? asset($this->user->profile_photo) : null,
                    'phone_no'      => $this->user->phone_no,
                    'role'          => $roles[$this->user->role] ?? 'Unknown',
                ];
            }),
            'caption'     => $this->caption,
            'thumbnail'   => $this->thumbnail,
            'attachments' => $this->whenLoaded('attachments', function () {
                return $this->attachments->map(function ($attachment) {
                    return [
                        'id'             => $attachment->id,
                        'post_id'        => $attachment->post_id,
                        'file_path'      => $attachment->file_path,
                        'file_type'      => $attachment->file_type,
                        'file_extension' => $attachment->file_extension,
                    ];
                });
            }),
            'tags'        => $this->whenLoaded('tags', function () {
                return $this->tags->map(function ($tag) {
                    return [
                        'id'   => $tag->id,
                        'name' => $tag->name,
                    ];
                });
            }),
            'likes_count'    => $this->likes_count,
            'comments_count' => $this->whenLoaded('comments', function () {
                return $this->comments->withoutReported()->whereNull('parent_id')->count();
            }, $this->comments()->withoutReported()->whereNull('parent_id')->count()),

            // Add is_liked flag: true if the authenticated user has liked the post
            'is_liked'    => $request->user() 
                                ? $this->likes->contains('user_id', $request->user()->id)
                                : false,
            'created_at'  => $this->created_at->toDateTimeString(),
        ];
    }
}
