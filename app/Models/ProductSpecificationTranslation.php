<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['product_specification_id', 'locale', 'label', 'value'])]
class ProductSpecificationTranslation extends Model
{
    public function specification(): BelongsTo
    {
        return $this->belongsTo(ProductSpecification::class, 'product_specification_id');
    }
}
