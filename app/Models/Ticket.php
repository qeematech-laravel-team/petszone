<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Ticket extends Model
{
    protected $fillable = ['user_id', 'vendor_id', 'subject', 'description', 'status', 'ticket_from', 'attachments'];

    protected $casts = [
        'attachments' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function vendor()
    {
        return $this->belongsTo(Vendor::class);
    }

    public function messages()
    {
        return $this->hasMany(TicketMessage::class);
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeResolved($query)
    {
        return $query->where('status', 'resolved');
    }

    public function scopeClosed($query)
    {
        return $query->where('status', 'closed');
    }

    public function scopeUser($query)
    {
        return $query->where('ticket_from', 'user');
    }

    public function scopeVendor($query)
    {
        return $query->where('ticket_from', 'vendor');
    }

    public function scopeOpen($query)
    {
        return $query->where('status', 'pending');
    }

    public function getAttachmentsPathAttribute()
    {
        return $this->attachments ? collect($this->attachments)->map(function ($attachment) {
            return asset('storage/'.$attachment);
        }) : null;
    }
}
