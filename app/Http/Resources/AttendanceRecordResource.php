<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class AttendanceRecordResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id'         => $this->id,
            'date'       => $this->date->toDateString(),
            'is_present' => (bool) $this->is_present,
            'notes'      => $this->notes,
            
            // Proof document information
            'proof_document' => $this->when($this->proof_document_path, [
                'name' => $this->proof_document_name,
                'type' => $this->proof_document_type,
                'size' => $this->proof_document_size,
                'url'  => $this->proof_document_url,
            ]),
            
            // hanya ambil field yang relevan dari member
            'member' => [
                'id'         => $this->member->id,
                'student_id' => $this->member->student_id,
                'phone'      => $this->member->phone,
                'name'       => optional($this->member->user)->name,   // ambil dari user
                'email'      => optional($this->member->user)->email,
            ],

            // recorder = user yang mencatat
            'recorder' => [
                'id'    => $this->recorder->id,
                'name'  => $this->recorder->name,
                'email' => $this->recorder->email,
            ],
        ];
    }
}
