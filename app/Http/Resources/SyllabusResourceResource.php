<?php
// app/Http/Resources/SyllabusResourceResource.php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class SyllabusResourceResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'type' => $this->type,
            'type_text' => ucfirst($this->type),
            'url' => $this->url,
            'file_path' => $this->file_path,
            'file_url' => $this->file_path ? asset('storage/' . $this->file_path) : null,
            'description' => $this->description,
        ];
    }
}