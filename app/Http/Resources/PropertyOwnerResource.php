<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PropertyOwnerResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        // return parent::toArray($request);
         $data = [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'age' => $this->when(isset($this->age), $this->age),
            'profile_photo' => $this->profile_photo ? asset($this->profile_photo) : null,
            'gender' => $this->gender_label ?? null,
            'is_verified' => $this->userIdentity && $this->userIdentity->verification_status === 'approved',
            'created_at' => $this->created_at->format('Y-m-d H:i:s'),
            'registed_at' => round($this->created_at->diffInDays(now())) . ' days ago',
        ];

        return $data;
    }
}
