<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Lending extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_id',
        'article_id',
        'start_date',
        'end_date',
        'status',
        'lend_date',
        'return_date'
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'lend_date' => 'date',
        'return_date' => 'date'
    ];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function article()
    {
        return $this->belongsTo(Article::class);
    }
}