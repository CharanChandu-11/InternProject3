<?php
// app/Http/Resources/UserProfileResource.php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class UserProfileResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'date_of_birth' => $this->date_of_birth?->toDateString(),
            'date_of_birth_formatted' => $this->date_of_birth?->format('F j, Y'),
            'age' => $this->age,
            'gender' => $this->gender,
            'gender_text' => $this->gender_text,
            'blood_group' => $this->blood_group,
            'blood_group_text' => $this->blood_group ? $this->blood_group . ' positive' : null,
            'religion' => $this->religion,
            'nationality' => $this->nationality,
            'permanent_address' => $this->permanent_address,
            'current_address' => $this->current_address,
            'emergency_contact' => $this->emergency_contact,
            'emergency_contact_name' => $this->emergency_contact_name,
            'medical_conditions' => $this->medical_conditions,
            'medical_conditions_list' => $this->medical_conditions ? explode(',', $this->medical_conditions) : [],
            'qualification' => $this->qualification,
            'experience_years' => $this->experience_years,
            'bio' => $this->bio,
            'social_links' => $this->social_links,
            'social_links_formatted' => $this->formatSocialLinks(),
            'created_at' => $this->created_at?->toDateTimeString(),
            'updated_at' => $this->updated_at?->toDateTimeString(),
        ];
    }

    private function formatSocialLinks()
    {
        if (!$this->social_links) {
            return null;
        }

        $links = is_array($this->social_links) ? $this->social_links : json_decode($this->social_links, true);
        
        return collect($links)->map(function($url, $platform) {
            return [
                'platform' => $platform,
                'url' => $url,
                'icon' => $this->getSocialIcon($platform)
            ];
        })->values();
    }

    private function getSocialIcon($platform)
    {
        return match($platform) {
            'facebook' => 'fab fa-facebook',
            'twitter' => 'fab fa-twitter',
            'instagram' => 'fab fa-instagram',
            'linkedin' => 'fab fa-linkedin',
            'youtube' => 'fab fa-youtube',
            'github' => 'fab fa-github',
            default => 'fas fa-link'
        };
    }
}