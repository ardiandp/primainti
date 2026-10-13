<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

/**
 * Pengaturan situs non-secret. Jangan simpan secret/API key di tabel ini.
 */
#[Fillable(['key', 'value'])]
class SiteSetting extends Model
{
}
