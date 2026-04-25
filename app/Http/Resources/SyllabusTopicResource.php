<?php
// app/Http/Resources/SyllabusTopicResource.php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class SyllabusTopicResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description,
            'week_number' => $this->week_number,
            'session_count' => $this->session_count,
            'learning_objectives' => $this->learning_objectives,
            'teaching_methods' => $this->teaching_methods,
            'assessment_methods' => $this->assessment_methods,
            'sort_order' => $this->sort_order,
            'resources' => SyllabusResourceResource::collection($this->whenLoaded('resources')),
        ];
    }
}