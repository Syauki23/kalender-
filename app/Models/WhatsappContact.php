<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WhatsappContact extends Model
{
    use HasFactory;

    protected $fillable = [
        'department_id',
        'name',
        'phone',
    ];

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function events()
    {
        return $this->belongsToMany(Event::class, 'event_whatsapp_contact');
    }
}
