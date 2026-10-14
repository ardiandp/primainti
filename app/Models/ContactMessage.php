<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'name',
    'email',
    'company',
    'phone',
    'subject',
    'message',
    'ip_address',
    'is_read',
    'status',
    'internal_note',
])]
class ContactMessage extends Model
{
    public const STATUSES = ['new', 'in_progress', 'resolved', 'spam'];

    protected function casts(): array
    {
        return [
            'is_read' => 'boolean',
        ];
    }
}
