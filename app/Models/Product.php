<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;
    protected $table = "product_master";
    protected $fillable = [
        'name', 'description', 'link_text', 'link_url', 'status', 'image'
    ];
}
