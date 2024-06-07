<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $table = 'product_data';
    protected $fillable = [
        'code',
        'name',
        'description',
        'stock',
        'price',
        'discontinued',
    ];
    public $timestamps = false;
}
