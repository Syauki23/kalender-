<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EventReminder extends Model
{
    use HasFactory;

    protected $fillable = [
        'event_id',
        'schedule_time',
        'is_sent',
    ];

    protected $casts = [
        'schedule_time' => 'datetime',
        'is_sent' => 'boolean',
    ];

    public function event()
    {
        return $this->belongsTo(Event::class);
    }
}
