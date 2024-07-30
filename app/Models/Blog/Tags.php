<?php

namespace App\Models\Blog;

use App\Models\Blog;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tags extends Model
{
    use HasFactory;

    protected $fillable = ['blog_id', 'name'];

    public function blog()
    {
        return $this->belongsTo(Blog::class);
    }
}
