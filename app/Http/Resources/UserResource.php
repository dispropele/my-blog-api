<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'=> $this->id,
            'login' => $this->login,
            'avatar_url' => $this->avatar ? Storage::url($this->avatar) : null,
            'email' => $this->email,
            'created_at' => $this->created_at,
        ];
    }
}
