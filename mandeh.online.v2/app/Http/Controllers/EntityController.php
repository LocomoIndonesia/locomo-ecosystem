<?php

namespace App\Http\Controllers;

use App\Models\Entity;
use Illuminate\Http\Request;

class EntityController extends Controller
{
    /**
     * Show the entity profile by slug.
     * This is the landing point for QR scans.
     */
    public function show(string $slug)
    {
        $entity = Entity::where('slug', $slug)
            ->with(['interactions' => function($query) {
                $query->where('status', 'public')->latest();
            }])
            ->firstOrFail();

        // Determine if it's a partner for Generative Rich UI
        $isPartner = $entity->type === 'sponsor' || $entity->businessMeta()->exists();

        return view('entity.show', [
            'entity' => $entity,
            'isPartner' => $isPartner,
        ]);
    }
}
