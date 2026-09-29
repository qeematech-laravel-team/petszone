<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TicketMessage extends Model
{
    protected $fillable = [
        'ticket_id',
        'sender_type',
        'sender_id',
        'message',
        'attachments',
    ];

    protected $casts = [
        'attachments' => 'array',
    ];

    public function ticket()
    {
        return $this->belongsTo(Ticket::class);
    }

    public function sender()
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    public function admin()
    {
        return $this->belongsTo(User::class, 'sender_id')->where('role', 'admin');
    }

    public function getAttachmentsPathAttribute()
    {
        return $this->attachments ? collect($this->attachments)->map(function ($attachment) {
            return asset('storage/'.$attachment);
        }) : null;
    }
}
