<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Carbon\Carbon;

class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $preamps = "tenantId=" . $this->id;
        $authUser = auth()?->user();

        $data = [
            'id' => $this->id,
            'name' => $this->first_name,
            'email' => $this->email,
            'age' => $this->when(isset($this->age), $this->age),
            'preferred_stay_length' => $this->stay_length ?? null,
            'distance' => $this->when(isset($this->distance), round($this->distance, 2)),
            'profile_photo' => $this->profile_photo ? asset($this->profile_photo) : null,
            'lifestyle_match_percent' => $this->when(isset($this->lifestyle_match_percent), function () {
                return round($this->lifestyle_match_percent);
            }),
            'availability_date' => $this->when(isset($this->availability_date), function () {
                return Carbon::parse($this->availability_date)->format('j F Y');
            }),
            'gender' => $this->gender_label ?? null,
            'role' => $this->role ?? null,
            'is_verified' => $this->userIdentity && $this->userIdentity->verification_status === 'approved',
            'partner' => [
                'name' => $this->partner_name,
                'age' => $this->partner_age,
                'profile_photo' => $this->partner_profile_photo ? asset($this->partner_profile_photo) : null,
                'gender' => $this->partner_gender_label ?? null,
            ],
        ];
        $data['is_saved'] = isset($this?->is_saves) && $this->is_saves == 1 ? true : (isset($this?->is_saved) && $this->is_saved == 1 ? true : false);
        $data['is_blocked'] = $authUser ? $authUser->hasBlocked($this->id) : false;
        $data['share_url'] = app(\App\Services\DeepLinkService::class)->createDeepLink('tenant-details', $preamps) ?? [];
        $data['boost'] = [
            'count' => $this->boost_count ?? 0,
            'status' => $this->is_boost_applied,
            'date' => $this->is_boost_applied ? $this->boost_expired_time : null,
        ];
        $data['allowed_chat'] = $authUser?->can_chat ?? false;

        return $data;
    }
}
