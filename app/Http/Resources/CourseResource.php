<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class CourseResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
       
        $authUser = $request->user();
        return [
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description,
            'category' => [
                'id' => $this->category_id,
                'name' => optional($this->category)->name,
            ],
            'what_you_learn' => $this->what_you_learn,
            'course_fee_type' => $this->course_fee_type,
            'course_fee' => $this->course_fee,
            'preview_video' => $this->preview_video,
            'preview_image' => $this->preview_image,
            'status' => $this->status,
            'likes_count' => $this->getLikesCountAttribute(),
            'comments_count' => $this->getCommentsCountAttribute(),
            'created_at' => $this->created_at->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at->format('Y-m-d H:i:s'),
            'created_by' => [
                'id' => $this->user_id,                
                'first_name' => optional($this->creator)->first_name,
                'first_name' => optional($this->creator)->last_name,
                'email' => optional($this->creator)->email,
                'profile_photo' => $this->profile_photo ? $this->profile_photo : null,
            ],
            'tags'        => $this->whenLoaded('tags', function () {
                return $this->tags->map(function ($tag) {
                    return [
                        'id'   => $tag->id,
                        'name' => $tag->name,
                    ];
                });
            }),            
            'course_content' => $this->whenLoaded('courseContents', function () {
                return $this->courseContents->sort(function ($a, $b) {
                    if ($a->order == $b->order) {
                        // If order is the same, sort by id descending
                        return $b->id <=> $a->id;
                    }
                    // Otherwise, sort by order ascending
                    return $a->order <=> $b->order;
                })->values()->map(function ($content) {
                    return [
                        'id' => $content->id,
                        'order' => $content->order,
                        'title' => $content->title,
                        'content' => $content->content,
                        'content_type' => $content->content_type,
                        'image' => $content->img ? $content->img : null,
                        'video' => $content->video ? $content->video : null,
                        'quizzes' => $content->quizzes->map(fn($quiz) => [
                            'id' => $quiz->id,
                            'question' => $quiz->question,
                            'type' => $quiz->type,
                            'options' => json_decode($quiz->options, true),
                            'answer' => $quiz->answer,
                        ]),
                    ];
                });
            }),
            // Add is_liked flag: true if the authenticated user has liked the Course
            'is_liked'    => $request->user() 
                                ? $this->likes->contains('user_id', $request->user()->id)
                                : false,
            
        ];
    }
}
