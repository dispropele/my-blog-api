<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

class Post extends Model
{
    use HasSlug;

    protected $fillable = [
      'title',
        'user_id',
        'category_id',
        'slug',
        'body',
        'image'
    ];

    public function comments() : HasMany{
        return $this->hasMany(Comment::class);
    }

    public function category() : BelongsTo{
        return $this->belongsTo(Category::class);
    }

    public function user() : BelongsTo{
        return $this->belongsTo(User::class);
    }


    //Получить опции для генерации слага
    public function getSlugOptions() : SlugOptions{
        return SlugOptions::create()
            ->generateSlugsFrom('title')
            ->saveSlugsTo('slug');
    }

}
