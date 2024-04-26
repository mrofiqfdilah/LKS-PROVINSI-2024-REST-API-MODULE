<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class post_attachments extends Model
{
    use HasFactory;
    protected $table = 'post_attachments';
    protected $guarded = ['id'];

    public function posts()
    {
        return $this->belongsTo(Posts::class);
    }

    public $timestamps = false;
}
