<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Event extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'date',
        'start_time',
        'end_time',
        'location',
        'color',
        'department_id',
        'created_by',
    ];

    protected $casts = [
        'date' => 'date',
    ];

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function whatsappContacts()
    {
        return $this->belongsToMany(WhatsappContact::class, 'event_whatsapp_contact', 'event_id', 'whatsapp_contact_id');
    }

    // Relasi ke reminders
    public function reminders()
    {
        return $this->hasMany(EventReminder::class);
    }
}
