<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Antrian extends Model
{
    protected $table = 'antrians';

    protected $fillable = [
        'nomor_urut',
        'kode_antrian',
        'nama_pasien',
        'jenis_poli',
        'status',
        'tanggal_antrian',
        'diinput_oleh',
        'dilayani_oleh',
        'waktu_dipanggil',
    ];

    protected $casts = [
        'tanggal_antrian' => 'date',
        'waktu_dipanggil' => 'datetime',
    ];

    public function petugasPelayan(): BelongsTo
    {
        return $this->belongsTo(User::class, 'dilayani_oleh');
    }

    public function petugasPenginput(): BelongsTo
    {
        return $this->belongsTo(User::class, 'diinput_oleh');
    }
}