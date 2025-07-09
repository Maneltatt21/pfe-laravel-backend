<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class VehicleResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'registration_number' => $this->registration_number,
            'model' => $this->model,
            'year' => $this->year,
            'status' => $this->status,
            'archived_at' => $this->archived_at?->format('Y-m-d H:i:s'),
            'created_at' => $this->created_at->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at->format('Y-m-d H:i:s'),
            'assigned_user' => new UserResource($this->whenLoaded('assignedUser')),
            'documents' => VehicleDocumentResource::collection($this->whenLoaded('documents')),
            'maintenances' => MaintenanceResource::collection($this->whenLoaded('maintenances')),
            'exchanges' => VehicleExchangeResource::collection($this->whenLoaded('exchanges')),
        ];
    }
}
