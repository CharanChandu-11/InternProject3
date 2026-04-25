<?php
// app/Exports/BooksExport.php

namespace App\Exports;

use App\Models\Book;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class BooksExport implements FromCollection, WithHeadings, WithMapping
{
    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        return Book::all();
    }

    /**
    * @return array
    */
    public function headings(): array
    {
        return [
            'ID',
            'Title',
            'ISBN',
            'Author',
            'Publisher',
            'Publication Year',
            'Category',
            'Quantity',
            'Available Quantity',
            'Shelf Location',
            'Description',
            'Created At',
            'Updated At',
        ];
    }

    /**
    * @param mixed $book
    * @return array
    */
    public function map($book): array
    {
        return [
            $book->id,
            $book->title,
            $book->isbn,
            $book->author,
            $book->publisher,
            $book->publication_year,
            $book->category,
            $book->quantity,
            $book->available_quantity,
            $book->shelf_location,
            $book->description,
            $book->created_at,
            $book->updated_at,
        ];
    }
}