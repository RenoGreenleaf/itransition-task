<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $table = 'product_data';
    public $timestamps = false;

    public function shouldBeSkipped(): bool
    {
        return ($this->price < 5 and $this->stock < 10) || $this->price > 1000;
    }
}
