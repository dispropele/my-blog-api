<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class NotificationResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $basedData = [
            'id' => $this->id,
            'type' => class_basename($this->type),
            'read_at' => $this->read_at,
            'created_at' => $this->created_at,
        ];

        $specificData = [];
        switch ($this->type){
            case 'App\Notifications\NewPostNotification':
                $specificData = [
                    'post_id' => $this->data['post_id'] ?? null,
                    'post_title' => $this->data['post_title'] ?? 'Untitled Post',
                    'author_id' => $this->data['author_id'] ?? null,
                    'author_name' => $this->data['author_name'] ?? 'Unknown Author',
                    'message' => $this->data['message'] ?? '',
                ];
                break;
            default:
                $specificData = [
                    'data' => $this->data
                ];
                break;
        }

        return array_merge($basedData, $specificData);
    }
}
