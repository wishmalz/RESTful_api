<?php

namespace App;

use App\Transformers\ProductTransformer;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use SoftDeletes;

    protected $dates = ['deleted_at'];

    public const AVAILABLE_PRODUCT = 'available';
    public const UNAVAILABLE_PRODUCT = 'unavailable';
    public $transformer = ProductTransformer::class;

    protected $fillable = [
        'name',
        'description',
        'quantity',
        'status',
        'image',
        'seller_id',
    ];

    protected $hidden = ['pivot'];

    public function isAvailable()
    {
        return $this->status === self::AVAILABLE_PRODUCT;
    }

    public function seller()
    {
        return $this->belongsTo(Seller::class);
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }

    public function categories()
    {
        return $this->belongsToMany(Category::class);
    }
}
