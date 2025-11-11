<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Carbon\Carbon;
use App\Traits\Common_trait;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasApiTokens, SoftDeletes, Common_trait;


    protected $table;
    protected $guarded;

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->table = config('tables.users');
    }

    public function role()
    {
        return $this->belongsTo(Roles::class);
    }

    public function userIdentity()
    {
        return $this->hasOne(UserIdentity::class);
    }

    public function scopeActive($query)
    {
        return $query->where('status', 1);
    }

    public function generateTwoFactorCode()
    {
        $this->two_factor_code = rand(100000, 999999);
        $this->two_factor_expires_at = now()->addMinutes(10);
        $this->save();
    }

    public function resetTwoFactorCode()
    {
        $this->two_factor_code = null;
        $this->two_factor_expires_at = null;
        $this->save();
    }

    public function tenantProfile()
    {
        return $this->hasOne(\App\Models\TenantProfile::class, 'user_id');
    }

    public function suburbs()
    {
        return $this->hasMany(QuestionAnswerUser::class, 'user_id')
            ->where('question_id', 20);
    }

    public function rentalBudget()
    {
        return $this->hasOne(QuestionAnswerUser::class, 'user_id')
            ->where('question_id', 23);
    }

    public function questionAnswers()
    {
        return $this->hasMany(QuestionAnswerUser::class, 'user_id');
    }

    //=========================== new relation =========


    public function genderLabel(): Attribute
    {
        return Attribute::make(
            get: fn() => optional($this->genderOption)->label_for_app,
        );
    }

    public function partnerGenderLabel(): Attribute
    {
        return Attribute::make(
            get: fn() => optional($this->partnerGenderOption)->label_for_app,
        );
    }


    public function genderOption()
    {
        return $this->hasOneThrough(
            QuestionsOption::class,
            QuestionAnswerUser::class,
            'user_id',
            'id',
            'id',
            'option_id'
        )->where('question_answers_user.question_id', 4)
            ->where('question_answers_user.for_partner', false);
    }

    public function partnerGenderOption()
    {
        return $this->hasOneThrough(
            QuestionsOption::class,
            QuestionAnswerUser::class,
            'user_id',
            'id',
            'id',
            'option_id'
        )->where('question_answers_user.question_id', 4)
            ->where('question_answers_user.for_partner', true);
    }

    public function stayLengthOption()
    {
        return $this->hasOneThrough(
            QuestionsOption::class,
            QuestionAnswerUser::class,
            'user_id',
            'id',
            'id',
            'option_id'
        )
            ->where('question_answers_user.question_id', 21);
    }

    public function nameAnswer()
    {
        return $this->hasOne(QuestionAnswerUser::class, 'user_id')
            ->where('question_id', 2)
            ->where('for_partner', false);
    }

    public function dobAnswer()
    {
        return $this->hasOne(QuestionAnswerUser::class, 'user_id')
            ->where('question_id', 3)
            ->where('for_partner', false);
    }



    public function getGenderLabelAttribute()
    {
        return optional($this->genderOption)->label_for_app;
    }

    public function getPartnerGenderLabelAttribute()
    {
        return optional($this->partnerGenderOption)->label_for_app;
    }

    public function getGenderAttribute()
    {
        return $this->gender_label;
    }

    public function getPartnerGenderAttribute()
    {
        return $this->partner_gender_label;
    }

    public function getStayLengthAttribute()
    {
        return optional($this->stayLengthOption)->label_for_app;
    }

    public function getNameAttribute()
    {
        return optional($this->nameAnswer)->answer;
    }

    public function getAgeAttribute()
    {
        $dobString = optional($this->dobAnswer)->answer;

        if (!$dobString) return null;

        try {
            $dob = \Carbon\Carbon::createFromFormat('d/m/Y', $dobString);
            return $dob->age;
        } catch (\Exception $e) {
            return null;
        }
    }






    public function availabilityDateAnswer()
    {
        return $this->hasOne(QuestionAnswerUser::class, 'user_id')
            ->where('question_id', 22)
            ->where('for_partner', false);
    }
    public function getAvailabilityDateAttribute()
    {
        $dateString = optional($this->availabilityDateAnswer)->answer;

        if (!$dateString) {
            return null;
        }

        try {
            return \Carbon\Carbon::createFromFormat('d/m/Y', $dateString)->format('j F Y');
        } catch (\Exception $e) {
            return null;
        }
    }

    public function dddddgetAvailabilityDateAttribute()
    {
        $dateString = optional($this->availabilityDateAnswer)->answer;

        if (!$dateString) {
            return null;
        }

        try {
            return \Carbon\Carbon::createFromFormat('Y-m-d', $dateString)->format('j F Y');
        } catch (\Exception $e) {
            return null;
        }
    }
    //==========================
    public function partnerNameAnswer()
    {
        return $this->hasOne(QuestionAnswerUser::class, 'user_id')
            ->where('question_id', 2)
            ->where('for_partner', true);
    }

    public function getPartnerNameAttribute()
    {
        return optional($this->partnerNameAnswer)->answer;
    }

    public function partnerDobAnswer()
    {
        return $this->hasOne(QuestionAnswerUser::class, 'user_id')
            ->where('question_id', 3)
            ->where('for_partner', true);
    }

    public function getPartnerAgeAttribute()
    {
        $dobString = optional($this->partnerDobAnswer)->answer;
        if (!$dobString) return null;

        try {
            $dob = \Carbon\Carbon::createFromFormat('d/m/Y', $dobString);
            return $dob->age;
        } catch (\Exception $e) {
            return null;
        }
    }

    public function likedRooms()
    {
        return $this->belongsToMany(Room::class, config('tables.tenant_likes'), 'tenant_id', 'room_id')
            ->with(['images', 'property'])
            ->withTimestamps();
    }

    public function likedByOwners()
    {
        return $this->belongsToMany(User::class, config('tables.owner_likes'),  'tenant_id', 'owner_id')
            ->withTimestamps();
    }

    public function likedByUsers()
    {
        return $this->belongsToMany(User::class, TenantLike::class,  'user_id', 'tenant_id')
            ->withTimestamps();
    }

    public function propertyOwner()
    {
        return $this->HasOne(PropertyOwner::class, 'user_id');
    }

    public function getIsSavedAttribute()
    {
        $user = auth()?->user();
        if (!$user) {
            return false;
        }
        return $this->checkIsLiked();
    }

    public function checkIsLiked()
    {
        $user = auth()?->user();
        if (!$user) {
            return false;
        }

        $return = TenantLike::query()
            ->where('user_id', $user->id)
            ->where('tenant_id', $this->attributes['id'])
            ->exists();

        if (!$return) {
            if ($user->propertyOwner()?->first('user_id')) {
                $return = DB::table(config('tables.owner_likes'))
                    ->where('owner_id', $user->propertyOwner()->first()->id)
                    ->where('tenant_id', $this->attributes['id'])
                    ->exists();
            } else {
                $return = false;
            }
        }

        return $return;
    }

    public function likedTenants()
    {
        return $this->belongsToMany(User::class, TenantLike::class, 'user_id', 'tenant_id')
            ->with(['images'])
            ->withTimestamps();
    }

    public function likedUsers()
    {
        return $this->belongsToMany(User::class, TenantLike::class,  'user_id', 'tenant_id')
            ->withTimestamps();
    }

    public function blockedUsers()
    {
        return $this->belongsToMany(User::class, config('tables.user_blocks'), 'user_id', 'blocked_user_id')
            ->withTimestamps()
            ->withPivot('created_at');;
    }


    public function blockedByUsers()
    {
        return $this->belongsToMany(User::class, config('tables.user_blocks'), 'blocked_user_id', 'user_id')
            ->withTimestamps();
    }


    public function hasBlocked($userId)
    {
        return $this->blockedUsers()->where('blocked_user_id', $userId)->exists();
    }


    public function isBlockedBy($userId)
    {
        return $this->blockedByUsers()->where('user_id', $userId)->exists();
    }

    public function getImageAttribute()
    {
        return ($this->attributes['profile_photo']
            ? asset($this->attributes['profile_photo'])
            : ($this->attributes['partner_profile_photo']
                ? asset($this->attributes['partner_profile_photo'])
                : null));
    }

    public function suburbsdata()
    {
        return $this->belongsToMany(
            Suburb::class,
            QuestionAnswerUser::class,
            'user_id',
            'option_id'
        )->wherePivot('question_id', 20)->wherePivot('deleted_at', null);
    }

    public function tenent_profile()
    {
        return $this->hasOne(TenantProfile::class);
    }

    protected static function booted()
    {
        static::deleting(function (User $user) {
            DB::transaction(function () use ($user) {
                if ($user->tenantProfile) {
                    $user->tenantProfile->delete();
                }
                $user->questionAnswers()?->delete();
                $user->suburbsdata()?->detach();
                $user->likedRooms()?->detach();
                $user->likedByOwners()?->detach();
                $user->likedByUsers()?->detach();
                $user->likedTenants()?->detach();
                $user->likedUsers()?->detach();
                $user->blockedUsers()?->detach();
                $user->blockedByUsers()?->detach();
                if ($user?->propertyOwner) {
                    $owner = $user?->propertyOwner;
                    $properties = $owner->properties()->pluck('id');
                    foreach ($properties as $propertyId) {
                        $property = \App\Models\Property::find($propertyId);
                        if ($property) {
                            $property?->rooms()?->each->delete();
                            $property?->housemates()?->each->delete();
                            $property->delete();
                        }
                    }
                    $owner?->delete();
                }
                $user->userIdentity()?->delete();
            });
        });
    }

    public function cascadeDelete()
    {
        if ($this->tenantProfile) {
            $this->tenantProfile->delete();
        }
        $this->questionAnswers()?->delete();
        $this->suburbsdata()?->detach();
        $this->likedRooms()?->detach();
        $this->likedByOwners()?->detach();
        $this->likedByUsers()?->detach();
        $this->likedTenants()?->detach();
        $this->likedUsers()?->detach();
        $this->blockedUsers()?->detach();
        $this->blockedByUsers()?->detach();

        if ($this->propertyOwner) {
            $this->propertyOwner->properties()?->each(function ($property) {
                $property?->rooms()?->delete();
                $property?->housemates()?->delete();
                $property->delete();
            });
            $this->propertyOwner->delete();
        }

        $this->userIdentity()?->delete();

        // finally delete the user itself
        $this->delete();
    }


    public function purchases()
    {
        return $this->hasMany(Purchase::class);
    }


    public function boosts()
    {
        return $this->hasOne(Boost::class, 'user_id');
    }


    public function keys()
    {
        return $this->hasOne(GoldenKey::class, 'user_id');
    }

    public function boostUsages()
    {
        return $this->hasMany(UserBoostUsage::class);
    }

    public function keyUsages()
    {
        return $this->hasMany(UserKeyUsage::class);
    }

    public function subscription()
    {
        return $this->hasOne(Subscription::class);
    }

    public function getIsBoostAppliedAttribute()
    {
        $lastUsage = $this->boostUsages()->latest('created_at')->first();
        if (!$lastUsage) {
            return false;
        }

        return Carbon::parse($lastUsage->used_at)->addHours(24)->isFuture();
    }


    public function getBoostExpiredTimeAttribute()
    {
        $lastUsage = $this->boostUsages()->latest('created_at')->first();
        return $lastUsage?->expires_at;
    }

    public function getBoostAppliedTimeAttribute()
    {
        $lastUsage = $this->boostUsages()->latest('created_at')->first();
        return $lastUsage?->used_at;
    }


    public function getBoostCountAttribute()
    {
        return $this->boosts?->boost_count ?? 0;
    }


    public function getIsKeyAppliedAttribute()
    {
        return false;
    }


    public function getKeyAppliedTimeAttribute()
    {
        return '';
    }


    public function getKeyCountAttribute()
    {
        return $this->keys?->key_count ?? 0;
    }

    public function getIsSubscripedAttribute()
    {
        $subscription = $this->subscription()
            ->where('status', 1)
            ->latest('expires_at')
            ->first();

        $isSubscribed = false;

        if ($subscription) {
            $roundedExpiry = Carbon::parse($subscription->expires_at)->ceilHour();
            $isSubscribed = $roundedExpiry->isFuture();
        }


        $this->updateQuietly([
            'is_subscribed' => $isSubscribed ? 1 : 0,
        ]);

        return $isSubscribed;
    }

    public function getSubscriptionExpiredTimeAttribute()
    {
        $lastUsage = $this->subscription()->whereStatus(1)->latest('expires_at')->first();
        return $lastUsage?->expires_at->startOfHour();
    }

    public function getActiveSubscriptionAttribute()
    {
        return $this->subscription()
            ->where('status', '1')
            ->where(function ($q) {
                $q->whereBetween('expires_at',  [now()->subDay(), now()])
                    ->orWhere('expires_at', '>', now());
            })
            ->with(['plan', 'role'])
            ->first();
    }


    public function getCanAddRoomAttribute()
    {
        if ($this->is_subscribed == 1) {
            return true;
        }

        if (!$this->propertyOwner) {
            return true;
        }


        $activeProperties = $this->propertyOwner?->properties()
            ->where('status', 1)
            ->get();

        if ($activeProperties->isEmpty() && $activeProperties->count()) {
            return false;
        }


        foreach ($activeProperties as $property) {
            if ($property->rooms()->where('status', 1)->count() > 0) {
                return false;
            }
        }

        return true;
    }

    public function getCanAddPropertyAttribute()
    {
        if ($this->is_subscribed == 1) {
            return true;
        }

        if (!$this->propertyOwner) {
            return true;
        }

        $activeProperties = $this->propertyOwner?->properties()
            ->where('status', 1)
            ->count();

        return $activeProperties === 0;
    }

    public function getCanChatAttribute()
    {
        if ($this->is_subscribed == 1) {
            return true;
        }

        return $this->chat_count != 0;
    }

    public function roleData()
    {
        return $this->belongsTo(Roles::class, 'role');
    }

    public function getRoleName()
    {
        return $this->roleData?->name ?? 'No role assigned';
    }
    public function getSubscriptionRoleAttribute()
    {
        $roleId = $this->subscription()?->latest('expires_at')?->first()?->user_role;
        if ($roleId) {
            return Roles::find($roleId)->name;
        }
    }

    public function getFirstNameAttribute()
    {
        $fetchName = optional($this->nameAnswer)->answer;

        if ($fetchName) {
            $parts = preg_split('/\s+/', trim($fetchName));
            return $parts[0] ?? null;
        }

        return $this->first_name ?? null;
    }

}
