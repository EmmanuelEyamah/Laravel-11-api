<?php

namespace App\Models;

use App\Models\Blog\Comment;
use App\Models\Blog\Tags;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Blog extends Model
{
    use HasFactory;

    protected $fillable = ['author_name', 'title', "message"];

    public function images()
    {
        return $this->hasMany(BlogImages::class);
    }

    public function tags()
    {
        return $this->hasMany(Tags::class);
    }

    public function comments()
    {
        return $this->hasMany(Comment::class);
    }

}
