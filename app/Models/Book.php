<?php
// app/Models/Book.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Book extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'isbn',
        'author',
        'publisher',
        'publication_year',
        'category',
        'quantity',
        'available_quantity',
        'shelf_location',
        'description'
    ];

    protected $casts = [
        'publication_year' => 'integer'
    ];

    // Relationships
    public function issues()
    {
        return $this->hasMany(BookIssue::class);
    }

    // Scopes
    public function scopeAvailable($query)
    {
        return $query->where('available_quantity', '>', 0);
    }

    // Methods
    public function isAvailable()
    {
        return $this->available_quantity > 0;
    }

    public function decreaseQuantity()
    {
        if ($this->available_quantity > 0) {
            $this->decrement('available_quantity');
            return true;
        }
        return false;
    }

    public function increaseQuantity()
    {
        if ($this->available_quantity < $this->quantity) {
            $this->increment('available_quantity');
            return true;
        }
        return false;
    }
}