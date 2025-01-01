<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Spatie\Permission\Models\Role as SpatieRole;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Spatie\Permission\Models\Permission;

class Role extends SpatieRole
{
    use HasFactory;

    protected $fillable = [
        'name',
    ];

     /**
     * Mutator pour capitaliser la première lettre du nom du rôle
     */
    public function setNameAttribute($value)
    {
        // Capitalise la première lettre du nom du rôle et met le reste en minuscules
        $this->attributes['name'] = ucfirst(strtolower($value));
    }

    // Relation standard avec les permissions
    public function permissions(): BelongsToMany
    {
        return parent::permissions(); // Hérite de la relation définie dans SpatieRole
    }

    // Relation supplémentaire pour les permissions spécifiques à un modèle (si nécessaire)
    public function modelPermissions(): BelongsToMany
    {
        return $this->belongsToMany(
            Permission::class,
            'role_has_model_permissions',
            'role_id',
            'permission_id'
        )->withPivot('model_type', 'model_id');
    }
}
