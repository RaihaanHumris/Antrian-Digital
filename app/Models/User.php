<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'peran',
        'nama_loket',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    // Fungsi cek apakah user adalah Admin
    public function apakahAdmin(): bool
    {
        return $this->peran === 'admin';
    }
}