<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use Notifiable;

    /**
     * Atribut yang dapat diisi massal.
     * Sistem kini hanya menggunakan satu peran yaitu Administrator (Admin).
     */
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

    /**
     * Helper untuk memeriksa status admin.
     * Seluruh akun yang terdaftar pada sistem diperlakukan sebagai Administrator.
     */
    public function apakahAdmin(): bool
    {
        return $this->peran === 'admin' || empty($this->peran);
    }
}
