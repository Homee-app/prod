<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes; 

class Verification extends Model
{
    use SoftDeletes;
    protected $table;

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->table = config('tables.verifications');
    }

    /**
     * Check if the given value is verified for a specific device and type.
     *
     * @param  string  $value      The email or phone number.
     * @param  mixed   $deviceId   The device identifier.
     * @param  int     $type       The type (e.g., 1 for email, 2 for phone).
     * @return bool
     */
    public static function isVerified($value, $deviceId, $type)
    {
        $record = self::where('value', $value)
            ->where('device_id', $deviceId)
            ->where('type', $type)
            ->where('status', 1)
            ->orderBy('created_at', 'desc')
            ->first();

        return $record !== null;
    }
}
